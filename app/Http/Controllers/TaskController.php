<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
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
     * Guardar nueva tarea
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
     * Actualizar tarea
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

        $task->update($request->only([
            'title', 'description', 'priority', 'assigned_to', 'due_date', 'status'
        ]));

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
     * Actualizar estado de tarea (API para Kanban)
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
        
        // Log de actividad (opcional)
        activity()
            ->performedOn($task)
            ->causedBy(Auth::user())
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ])
            ->log('Cambió el estado de la tarea');
        
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
        
        // Log de actividad (opcional)
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
        
        return response()->json([
            'success' => true,
            'task' => $task->fresh()->load('assignedUser'),
            'message' => 'Orden actualizado correctamente'
        ]);
    }
}