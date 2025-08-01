<?php

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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Tareas del usuario con sus proyectos
        $tasks = $user->assignedTasks()
            ->with(['project', 'assignedUser', 'creator'])
            ->orderBy('due_date', 'asc')
            ->paginate(12);
            
        // Proyectos del usuario para el filtro
        $projects = $user->projects()->orderBy('title')->get();
        
        // Estadísticas
        $stats = [
            'total' => $user->assignedTasks()->count(),
            'pending' => $user->assignedTasks()->whereIn('status', ['todo', 'in_progress'])->count(),
            'completed' => $user->assignedTasks()->where('status', 'done')->count(),
            'overdue' => $user->assignedTasks()
                ->where('due_date', '<', now())
                ->where('status', '!=', 'done')
                ->count()
        ];
        
        return view('tasks.index', compact('tasks', 'projects', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projects = Auth::user()->projects()->orderBy('title')->get();
        return view('tasks.create', compact('projects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validar datos de entrada
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'project_id' => 'required|exists:projects,id',
                'assigned_to' => 'nullable|exists:users,id',
                'priority' => 'required|in:low,medium,high',
                'due_date' => 'nullable|date|after_or_equal:today',
                'status' => 'required|in:todo,in_progress,done'
            ], [
                'title.required' => 'El título es obligatorio',
                'project_id.required' => 'Debes seleccionar un proyecto',
                'project_id.exists' => 'El proyecto seleccionado no es válido',
                'priority.required' => 'La prioridad es obligatoria',
                'priority.in' => 'La prioridad debe ser: baja, media o alta',
                'due_date.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a hoy',
                'status.required' => 'El estado es obligatorio'
            ]);

            // Verificar que el usuario tenga acceso al proyecto
            $project = Project::findOrFail($validatedData['project_id']);
            if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
                return back()->withErrors(['project_id' => 'No tienes acceso a este proyecto'])->withInput();
            }

            // Si se asigna a alguien, verificar que sea miembro del proyecto
            if ($request->assigned_to && !$project->members->contains($request->assigned_to)) {
                return back()->withErrors(['assigned_to' => 'El usuario asignado debe ser miembro del proyecto'])->withInput();
            }

            DB::beginTransaction();

            // Obtener el orden máximo para las tareas en el estado inicial
            $maxOrder = Task::where('project_id', $validatedData['project_id'])
                ->where('status', $validatedData['status'])
                ->max('order') ?? -1;

            // Crear la tarea
            $task = Task::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'project_id' => $validatedData['project_id'],
                'assigned_to' => $validatedData['assigned_to'],
                'created_by' => Auth::id(),
                'priority' => $validatedData['priority'],
                'due_date' => $validatedData['due_date'],
                'status' => $validatedData['status'],
                'order' => $maxOrder + 1
            ]);

            // NOTIFICACIÓN: Si se asignó a alguien
            if ($task->assigned_to && $task->assigned_to !== Auth::id()) {
                $assignedUser = User::find($task->assigned_to);
                if ($assignedUser) {
                    Notification::create([
                        'user_id' => $task->assigned_to,
                        'type' => Notification::TYPE_TASK_ASSIGNED,
                        'title' => 'Nueva tarea asignada',
                        'message' => ':assigner te asignó la tarea ":task_title" en el proyecto :project_title',
                        'data' => [
                            'task_title' => $task->title,
                            'task_id' => $task->id,
                            'project_title' => $project->title,
                            'project_id' => $project->id,
                            'assigner' => Auth::user()->name,
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
     * Display the specified resource.
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
     * Show the form for editing the specified resource.
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        try {
            // Verificar acceso
            $project = $task->project;
            if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
                abort(403, 'No tienes acceso a esta tarea');
            }

            $validated = $request->validate([
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

            $task->update($validated);

            // NOTIFICACIÓN: Si se cambió la asignación
            if ($oldAssignedTo != $task->assigned_to && $task->assigned_to) {
                $assignedUser = User::find($task->assigned_to);
                if ($assignedUser && $task->assigned_to !== Auth::id()) {
                    Notification::create([
                        'user_id' => $task->assigned_to,
                        'type' => Notification::TYPE_TASK_ASSIGNED,
                        'title' => 'Tarea reasignada',
                        'message' => ':assigner te asignó la tarea ":task_title"',
                        'data' => [
                            'task_title' => $task->title,
                            'assigner' => Auth::user()->name,
                        ],
                        'related_type' => Task::class,
                        'related_id' => $task->id,
                        'action_url' => route('tasks.show', $task->id),
                    ]);
                }
            }

            // NOTIFICACIÓN: Si la tarea se completó
            if ($oldStatus !== 'done' && $task->status === 'done') {
                // Notificar al creador del proyecto
                if ($project->creator_id !== Auth::id()) {
                    Notification::create([
                        'user_id' => $project->creator_id,
                        'type' => Notification::TYPE_TASK_COMPLETED,
                        'title' => 'Tarea completada',
                        'message' => 'La tarea ":task_title" ha sido completada por :user',
                        'data' => [
                            'task_title' => $task->title,
                            'user' => Auth::user()->name,
                        ],
                        'related_type' => Task::class,
                        'related_id' => $task->id,
                        'action_url' => route('tasks.show', $task->id),
                    ]);
                }
            }

            DB::commit();

            // Si es petición AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tarea actualizada exitosamente',
                    'task' => $task->fresh()->load(['assignedUser', 'creator']),
                    'redirect' => route('projects.show', $task->project_id)
                ]);
            }

            return redirect()->route('projects.show', $task->project_id)
                ->with('success', 'Tarea actualizada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            
            \Log::error('Error al actualizar tarea: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'task_id' => $task->id,
                'error' => $e->getMessage()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la tarea',
                    'errors' => ['general' => ['Ha ocurrido un error al actualizar la tarea']]
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Error al actualizar la tarea'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        try {
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
            $taskTitle = $task->title;
            
            $task->delete();

            // Si es petición AJAX
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tarea eliminada exitosamente',
                    'redirect' => route('projects.show', $projectId)
                ]);
            }

            return redirect()->route('projects.show', $projectId)
                ->with('success', 'Tarea eliminada exitosamente');
                
        } catch (Exception $e) {
            \Log::error('Error al eliminar tarea: ' . $e->getMessage());
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la tarea'
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error al eliminar la tarea');
        }
    }

    /**
     * Update task status via API (for Kanban)
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
            }

            return response()->json([
                'success' => true,
                'task' => $task->fresh()->load('assignedUser'),
                'message' => 'Estado actualizado correctamente'
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
     * Update task order in Kanban board
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
            ]);
            
            DB::beginTransaction();
            
            $oldStatus = $task->status;
            
            // Actualizar estado si cambió
            if ($task->status !== $request->status) {
                $task->status = $request->status;
            }
            
            // Actualizar orden
            $task->order = $request->order;
            $task->save();
            
            // Reordenar otras tareas si es necesario
            $tasksInColumn = Task::where('project_id', $task->project_id)
                ->where('status', $request->status)
                ->where('id', '!=', $task->id)
                ->where('order', '>=', $request->order)
                ->orderBy('order')
                ->get();
            
            $currentOrder = $request->order + 1;
            foreach ($tasksInColumn as $columnTask) {
                if ($columnTask->order != $currentOrder) {
                    $columnTask->update(['order' => $currentOrder]);
                }
                $currentOrder++;
            }
            
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