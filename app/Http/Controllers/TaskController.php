<?php
// Ruta: app/Http/Controllers/TaskController.php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

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
     * Guardar nueva tarea (CON NOTIFICACIONES Y MANEJO DE ERRORES)
     */
    public function store(Request $request)
    {
        try {
            // Validación con mensajes personalizados
            $validatedData = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'priority' => 'required|in:low,medium,high',
                'assigned_to' => 'nullable|exists:users,id',
                'due_date' => 'nullable|date|after_or_equal:today',
            ], [
                'title.required' => 'El título de la tarea es obligatorio',
                'priority.required' => 'La prioridad es obligatoria',
                'priority.in' => 'La prioridad debe ser: baja, media o alta',
                'assigned_to.exists' => 'El usuario asignado no es válido',
                'due_date.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a hoy',
            ]);

            // Verificar acceso al proyecto
            $project = Project::findOrFail($request->project_id);
            if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
                return redirect()->back()
                    ->withErrors(['error' => 'No tienes acceso a este proyecto'])
                    ->withInput();
            }

            // Si se asigna a alguien, verificar que sea miembro del proyecto
            if (!empty($validatedData['assigned_to'])) {
                $isMember = $project->members()->where('user_id', $validatedData['assigned_to'])->exists();
                $isCreator = $project->creator_id == $validatedData['assigned_to'];
                
                if (!$isMember && !$isCreator) {
                    return redirect()->back()
                        ->withErrors(['assigned_to' => 'El usuario asignado debe ser miembro del proyecto'])
                        ->withInput();
                }
            }

            // Usar transacción para garantizar consistencia
            DB::beginTransaction();
            
            $task = Task::create([
                'project_id' => $validatedData['project_id'],
                'title' => $validatedData['title'],
                'description' => $validatedData['description'] ?? null,
                'priority' => $validatedData['priority'],
                'assigned_to' => $validatedData['assigned_to'] ?? null,
                'due_date' => $validatedData['due_date'] ?? null,
                'created_by' => Auth::id(),
                'status' => 'todo',
                'order' => Task::where('project_id', $validatedData['project_id'])
                              ->where('status', 'todo')
                              ->max('order') + 1 ?? 0
            ]);

            // NOTIFICACIÓN: Al usuario asignado
            if ($task->assigned_to && $task->assigned_to !== Auth::id()) {
                $assignedUser = User::find($task->assigned_to);
                if ($assignedUser) {
                    Notification::create([
                        'user_id' => $assignedUser->id,
                        'type' => Notification::TYPE_TASK_ASSIGNED,
                        'title' => 'Nueva tarea asignada',
                        'message' => 'Se te ha asignado la tarea ":task_title" en el proyecto :project_title',
                        'data' => [
                            'task_title' => $task->title,
                            'project_title' => $project->title,
                            'assigned_by' => Auth::user()->name,
                        ],
                        'related_type' => Task::class,
                        'related_id' => $task->id,
                        'action_url' => route('projects.show', $project->id),
                    ]);
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
                        'action_url' => route('projects.show', $project->id),
                    ]);
                });

            DB::commit();

            // Si es una solicitud AJAX, retornar JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tarea creada exitosamente',
                    'task' => $task->load(['assignedUser', 'creator']),
                    'redirect' => route('projects.show', $validatedData['project_id'])
                ]);
            }

            return redirect()->route('projects.show', $validatedData['project_id'])
                ->with('success', 'Tarea creada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            
            // Log del error
            \Log::error('Error al crear tarea: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'project_id' => $request->project_id,
                'error' => $e->getMessage()
            ]);

            // Si es una solicitud AJAX, retornar error JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la tarea. Por favor intenta nuevamente.',
                    'errors' => ['general' => ['Ha ocurrido un error al crear la tarea']]
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Error al crear la tarea. Por favor intenta nuevamente.'])
                ->withInput();
        }
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
        return view('tasks.edit', compact('task', 'project', 'members'));
    }

    /**
     * Actualizar tarea (CON NOTIFICACIONES)
     */
    public function update(Request $request, Task $task)
    {
        try {
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
                'due_date' => 'nullable|date|after_or_equal:today',
                'status' => 'required|in:todo,in_progress,done'
            ], [
                'title.required' => 'El título de la tarea es obligatorio',
                'priority.required' => 'La prioridad es obligatoria',
                'priority.in' => 'La prioridad debe ser: baja, media o alta',
                'assigned_to.exists' => 'El usuario asignado no es válido',
                'due_date.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a hoy',
                'status.required' => 'El estado es obligatorio',
                'status.in' => 'Estado inválido'
            ]);

            // Si se asigna a alguien, verificar que sea miembro del proyecto
            if ($request->assigned_to && !$project->members->contains($request->assigned_to)) {
                return back()->withErrors(['assigned_to' => 'El usuario asignado debe ser miembro del proyecto']);
            }

            DB::beginTransaction();

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

            DB::commit();

            return redirect()->route('projects.show', $task->project_id)
                ->with('success', 'Tarea actualizada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            
            \Log::error('Error al actualizar tarea: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'task_id' => $task->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Error al actualizar la tarea'])
                ->withInput();
        }
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
        try {
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
                if ($request->status === 'done' && $task->assigned_to && $task->assigned_to !== Auth::id()) {
                    Notification::create([
                        'user_id' => $task->assigned_to,
                        'type' => Notification::TYPE_TASK_COMPLETED,
                        'title' => 'Tarea completada',
                        'message' => 'Tu tarea ":task_title" fue marcada como completada',
                        'data' => [
                            'task_title' => $task->title,
                        ],
                        'related_type' => Task::class,
                        'related_id' => $task->id,
                        'action_url' => route('projects.show', $project),
                    ]);
                }

                // Si se mueve a en progreso
                if ($oldStatus === 'todo' && $request->status === 'in_progress') {
                    // Notificar al creador de la tarea
                    if ($task->created_by !== Auth::id()) {
                        Notification::create([
                            'user_id' => $task->created_by,
                            'type' => 'task_updated',
                            'title' => 'Tarea en progreso',
                            'message' => ':user comenzó a trabajar en ":task_title"',
                            'data' => [
                                'task_title' => $task->title,
                                'user' => Auth::user()->name,
                            ],
                            'related_type' => Task::class,
                            'related_id' => $task->id,
                            'action_url' => route('projects.show', $project),
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente'
            ]);

        } catch (Exception $e) {
            \Log::error('Error al actualizar estado de tarea: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar el estado'
            ], 500);
        }
    }

    /**
     * Actualizar orden de tareas en el tablero Kanban
     */
    public function updateOrder(Request $request, Task $task)
    {
        try {
            // Verificar acceso
            $project = $task->project;
            if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $request->validate([
                'status' => 'required|in:todo,in_progress,done',
                'order' => 'required|integer|min:0',
                'previous_task_id' => 'nullable|exists:tasks,id',
                'next_task_id' => 'nullable|exists:tasks,id'
            ]);
            
            DB::beginTransaction();
            
            $oldStatus = $task->status;
            $oldOrder = $task->order;
            
            // Actualizar estado si cambió
            if ($task->status !== $request->status) {
                $task->status = $request->status;
            }
            
            // Reordenar todas las tareas en la columna de destino
            $tasksInColumn = Task::where('project_id', $task->project_id)
                ->where('status', $request->status)
                ->where('id', '!=', $task->id)
                ->orderBy('order')
                ->get();
            
            $newOrder = 0;
            $taskUpdated = false;
            
            foreach ($tasksInColumn as $columnTask) {
                // Si encontramos la posición donde debe ir nuestra tarea
                if ($request->previous_task_id && $columnTask->id == $request->previous_task_id) {
                    $newOrder++;
                    $task->order = $newOrder;
                    $taskUpdated = true;
                    $newOrder++;
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
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'task' => $task->fresh()->load('assignedUser'),
                'message' => 'Orden actualizado correctamente'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar orden de tarea: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar el orden'
            ], 500);
        }
    }
}