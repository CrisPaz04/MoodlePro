<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar lista de todas las tareas del usuario
     */
    public function index()
    {
        $tasks = Auth::user()->assignedTasks()
            ->with(['project', 'creator'])
            ->orderBy('due_date', 'asc')
            ->get();
            
        return view('tasks.index', compact('tasks'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request)
    {
        $project = Project::findOrFail($request->project_id);
        
        // Verificar que el usuario sea miembro del proyecto
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            abort(403, 'No tienes acceso a este proyecto');
        }
        
        $members = $project->members;
        
        return view('tasks.create', compact('project', 'members'));
    }

    /**
     * Guardar nueva tarea (CON NOTIFICACIONES)
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date|after:today',
        ]);

        // Verificar acceso al proyecto
        $project = Project::findOrFail($request->project_id);
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            abort(403, 'No tienes acceso a este proyecto');
        }

        // Si se asigna a alguien, verificar que sea miembro del proyecto
        if ($request->assigned_to && !$project->members->contains($request->assigned_to)) {
            return back()->withErrors(['assigned_to' => 'El usuario asignado debe ser miembro del proyecto']);
        }

        $task = Task::create([
            'project_id' => $request->project_id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'assigned_to' => $request->assigned_to,
            'due_date' => $request->due_date,
            'created_by' => Auth::id(),
            'status' => 'todo',
            'order' => Task::where('project_id', $request->project_id)
                          ->where('status', 'todo')
                          ->max('order') + 1
        ]);

        // NOTIFICACIÓN: Al usuario asignado
        if ($task->assigned_to && $task->assigned_to !== Auth::id()) {
            $assignedUser = User::find($task->assigned_to);
            if ($assignedUser) {
                $assignedUser->notifyTaskAssigned($task);
            }
        }

        // NOTIFICACIÓN: A todos los miembros del proyecto sobre la nueva tarea
        $project->members()
            ->where('user_id', '!=', Auth::id()) // Excepto al creador
            ->where('user_id', '!=', $task->assigned_to) // Y al asignado (ya fue notificado)
            ->get()
            ->each(function ($member) use ($task, $project) {
                Notification::create([
                    'user_id' => $member->id,
                    'type' => 'task_created',
                    'title' => 'Nueva tarea en proyecto',
                    'message' => 'Se creó la tarea ":task_title" en el proyecto :project_title',
                    'data' => [
                        'task_title' => $task->title,
                        'project_title' => $project->title,
                        'created_by' => Auth::user()->name,
                    ],
                    'related_type' => Task::class,
                    'related_id' => $task->id,
                    'action_url' => route('projects.show', $project),
                ]);
            });

        return redirect()->route('projects.show', $request->project_id)
            ->with('success', 'Tarea creada exitosamente');
    }

    /**
     * Mostrar detalle de tarea
     */
    public function show(Task $task)
    {
        // Verificar acceso
        $project = $task->project;
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            abort(403, 'No tienes acceso a esta tarea');
        }
        
        $task->load(['project', 'assignedUser', 'creator']);
        return view('tasks.show', compact('task'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Task $task)
    {
        // Verificar acceso
        $project = $task->project;
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            abort(403, 'No tienes acceso a esta tarea');
        }
        
        $members = $project->members;
        return view('tasks.edit', compact('task', 'members'));
    }

    /**
     * Actualizar tarea (CON NOTIFICACIONES)
     */
    public function update(Request $request, Task $task)
    {
        // Verificar acceso
        $project = $task->project;
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            abort(403, 'No tienes acceso a esta tarea');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'status' => 'required|in:todo,in_progress,done'
        ]);

        // Si se asigna a alguien, verificar que sea miembro del proyecto
        if ($request->assigned_to && !$project->members->contains($request->assigned_to)) {
            return back()->withErrors(['assigned_to' => 'El usuario asignado debe ser miembro del proyecto']);
        }

        // Guardar valores anteriores para comparación
        $oldAssignedTo = $task->assigned_to;
        $oldStatus = $task->status;

        $task->update($request->only([
            'title', 'description', 'priority', 'assigned_to', 'due_date', 'status'
        ]));

        // NOTIFICACIÓN: Si se cambió la asignación
        if ($oldAssignedTo != $task->assigned_to && $task->assigned_to) {
            $assignedUser = User::find($task->assigned_to);
            if ($assignedUser && $task->assigned_to !== Auth::id()) {
                $assignedUser->notifyTaskAssigned($task);
            }
        }

        // NOTIFICACIÓN: Si la tarea se completó
        if ($oldStatus !== 'done' && $task->status === 'done') {
            // Notificar al creador del proyecto
            if ($project->creator_id !== Auth::id()) {
                $project->creator->notify(
                    'Tarea completada',
                    'La tarea "' . $task->title . '" ha sido completada por ' . Auth::user()->name,
                    Notification::TYPE_TASK_COMPLETED,
                    route('tasks.show', $task)
                );
            }

            // Notificar al creador de la tarea si es diferente
            if ($task->created_by !== Auth::id() && $task->created_by !== $project->creator_id) {
                User::find($task->created_by)?->notify(
                    'Tu tarea fue completada',
                    Auth::user()->name . ' completó la tarea "' . $task->title . '"',
                    Notification::TYPE_TASK_COMPLETED,
                    route('tasks.show', $task)
                );
            }
        }

        return redirect()->route('projects.show', $task->project_id)
            ->with('success', 'Tarea actualizada exitosamente');
    }

    /**
     * Eliminar tarea
     */
    public function destroy(Task $task)
    {
        // Solo el creador de la tarea o coordinadores pueden eliminar
        $project = $task->project;
        $userRole = $project->members()
            ->where('user_id', Auth::id())
            ->first()
            ->pivot
            ->role ?? null;
            
        if ($task->created_by !== Auth::id() && 
            $project->creator_id !== Auth::id() && 
            $userRole !== 'coordinator') {
            abort(403, 'No tienes permisos para eliminar esta tarea');
        }

        $projectId = $task->project_id;
        $task->delete();

        return redirect()->route('projects.show', $projectId)
            ->with('success', 'Tarea eliminada exitosamente');
    }

    /**
     * Actualizar estado de tarea (API para Kanban) - CON NOTIFICACIONES
     */
    public function updateStatus(Request $request, Task $task)
    {
        // Verificar acceso
        $project = $task->project;
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'status' => 'required|in:todo,in_progress,done'
        ]);
        
        $oldStatus = $task->status;
        $task->update(['status' => $request->status]);

        // NOTIFICACIONES: Según cambios de estado
        if ($oldStatus !== $request->status) {
            // Si se marca como completada
            if ($request->status === 'done' && $task->assigned_to !== Auth::id()) {
                User::find($task->assigned_to)?->notify(
                    'Tarea completada',
                    'Tu tarea "' . $task->title . '" fue marcada como completada',
                    Notification::TYPE_TASK_COMPLETED,
                    route('projects.show', $project)
                );
            }

            // Si se mueve a en progreso
            if ($oldStatus === 'todo' && $request->status === 'in_progress') {
                // Notificar al creador de la tarea
                if ($task->created_by !== Auth::id()) {
                    User::find($task->created_by)?->notify(
                        'Tarea en progreso',
                        Auth::user()->name . ' comenzó a trabajar en "' . $task->title . '"',
                        'task_progress',
                        route('projects.show', $project)
                    );
                }
            }
        }
        
        // Log de actividad (opcional - comentado porque requiere spatie/laravel-activitylog)
        /*
        activity()
            ->performedOn($task)
            ->causedBy(Auth::user())
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ])
            ->log('Cambió el estado de la tarea');
        */
        
        return response()->json([
            'success' => true,
            'task' => $task->fresh()->load('assignedUser'),
            'message' => 'Estado actualizado correctamente'
        ]);
    }

    /**
     * Actualizar orden de tarea en kanban (API)
     */
    public function updateOrder(Request $request, Task $task)
    {
        // Verificar acceso
        $project = $task->project;
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'order' => 'required|integer|min:0',
            'status' => 'required|in:todo,in_progress,done'
        ]);
        
        $oldStatus = $task->status;
        $oldOrder = $task->order;
        
        // Si cambió de columna, actualizar estado
        if ($task->status !== $request->status) {
            $task->status = $request->status;
        }
        
        // Obtener todas las tareas de la columna destino
        $tasksInColumn = Task::where('project_id', $task->project_id)
            ->where('status', $request->status)
            ->where('id', '!=', $task->id)
            ->orderBy('order')
            ->get();
        
        // Reordenar tareas
        $newOrder = 0;
        $taskUpdated = false;
        
        foreach ($tasksInColumn as $index => $columnTask) {
            // Insertar la tarea movida en la posición correcta
            if ($newOrder == $request->order && !$taskUpdated) {
                $task->order = $newOrder++;
                $taskUpdated = true;
            }
            
            // Actualizar orden de las demás tareas
            if ($columnTask->order != $newOrder) {
                $columnTask->update(['order' => $newOrder]);
            }
            $newOrder++;
        }
        
        // Si la tarea debe ir al final
        if (!$taskUpdated) {
            $task->order = $newOrder;
        }
        
        $task->save();
        
        // Log de actividad (opcional - comentado porque requiere spatie/laravel-activitylog)
        /*
        if ($oldStatus !== $task->status || $oldOrder !== $task->order) {
            activity()
                ->performedOn($task)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $task->status,
                    'old_order' => $oldOrder,
                    'new_order' => $task->order
                ])
                ->log('Movió la tarea en el tablero');
        }
        */
        
        return response()->json([
            'success' => true,
            'task' => $task->fresh()->load('assignedUser'),
            'message' => 'Orden actualizado correctamente'
        ]);
    }
}