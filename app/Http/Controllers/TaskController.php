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
        $user = Auth::user();
        
        // Obtener tareas del usuario
        $tasks = $user->assignedTasks()
            ->with(['project', 'creator'])
            ->orderBy('due_date', 'asc')
            ->get();
            
        // Obtener TODOS los proyectos del usuario (para el filtro)
        $allUserProjects = $user->projects()
            ->orderBy('title', 'asc')
            ->get();
            
        return view('tasks.index', compact('tasks', 'allUserProjects'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request)
    {
        $project = Project::findOrFail($request->project_id);
        
        // Verificar que el usuario sea miembro del proyecto
        if (!$project->members->contains(Auth::id()) && $project->created_by !== Auth::id()) {
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
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'project_id' => 'required|exists:projects,id',
                'priority' => 'required|in:low,medium,high',
                'status' => 'required|in:todo,in_progress,done',
                'assigned_to' => 'required|exists:users,id', // CAMBIADO: ahora es required
                'due_date' => 'required|date|after_or_equal:today', // CAMBIADO: ahora es required
            ], [
                'title.required' => 'El título de la tarea es obligatorio',
                'project_id.required' => 'Debe seleccionar un proyecto',
                'priority.required' => 'La prioridad es obligatoria',
                'status.required' => 'El estado es obligatorio',
                'assigned_to.required' => 'Debe asignar la tarea a un miembro del equipo', // NUEVO mensaje
                'assigned_to.exists' => 'El usuario seleccionado no es válido',
                'due_date.required' => 'La fecha de entrega es obligatoria', // NUEVO mensaje
                'due_date.after_or_equal' => 'La fecha de entrega debe ser hoy o posterior',
            ]);

            // Verificar acceso al proyecto
            $project = Project::findOrFail($validatedData['project_id']);
            if (!$project->members->contains(Auth::id()) && $project->created_by !== Auth::id()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes acceso a este proyecto'
                    ], 403);
                }
                return redirect()->back()->with('error', 'No tienes acceso a este proyecto');
            }

            // Verificar que el usuario asignado sea miembro del proyecto
            if (!$project->members->contains($validatedData['assigned_to'])) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El usuario asignado debe ser miembro del proyecto'
                    ], 422);
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'El usuario asignado debe ser miembro del proyecto');
            }

            DB::beginTransaction();

            // Obtener el orden máximo
            $maxOrder = Task::where('project_id', $validatedData['project_id'])
                ->where('status', $validatedData['status'])
                ->max('order') ?? -1;

            // Crear la tarea
            $task = Task::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'project_id' => $validatedData['project_id'],
                'created_by' => Auth::id(),
                'assigned_to' => $validatedData['assigned_to'],
                'priority' => $validatedData['priority'],
                'status' => $validatedData['status'],
                'due_date' => $validatedData['due_date'],
                'order' => $maxOrder + 1,
            ]);

            // Crear notificación si se asignó a alguien diferente del creador
              if ($task->assigned_to !== Auth::id()) {
                Notification::create([
                    'user_id' => $task->assigned_to,
                    'type' => 'task_assigned',
                    'title' => 'Nueva tarea asignada',
                    'message' => "Te han asignado la tarea: {$task->title}",
                    'data' => json_encode([
                        'task_id' => $task->id,
                        'project_id' => $task->project_id,
                        'assigned_by' => Auth::user()->name,
                    ]),
                    'notifiable_type' => 'App\\Models\\User',  
                    'notifiable_id' => $task->assigned_to,     
                ]);
            }

            DB::commit();

            // Si es petición AJAX, devolver JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tarea creada exitosamente',
                    'task' => $task->load(['project', 'creator', 'assignedUser']),
                    'redirect' => route('projects.show', $task->project_id)
                ]);
            }

            return redirect()
                ->route('projects.show', $task->project)
                ->with('success', 'Tarea creada exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Manejar errores de validación para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error creating task: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hubo un error al crear la tarea',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Hubo un error al crear la tarea');
        }
    }

    /**
     * Mostrar tarea específica
     */
    public function show(Task $task)
    {
        // Verificar acceso
        $project = $task->project;
        if (!$project->members->contains(Auth::id()) && $project->created_by !== Auth::id()) {
            abort(403, 'No tienes acceso a esta tarea');
        }
        
        $task->load(['project', 'creator', 'assignedUser']);
        
        return view('tasks.show', compact('task'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Task $task)
    {
        // Solo el creador puede editar
        if ($task->created_by !== Auth::id()) {
            abort(403, 'Solo el creador puede editar esta tarea');
        }
        
        $project = $task->project;
        $members = $project->members;
        
        return view('tasks.edit', compact('task', 'project', 'members'));
    }

    /**
     * Actualizar tarea
     */
    public function update(Request $request, Task $task)
    {
        // Solo el creador puede actualizar
        if ($task->created_by !== Auth::id()) {
            abort(403, 'No tienes permisos para editar esta tarea');
        }

        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'priority' => 'required|in:low,medium,high',
                'status' => 'required|in:todo,in_progress,done',
                'assigned_to' => 'required|exists:users,id', // CAMBIADO: ahora es required
                'due_date' => 'required|date', // CAMBIADO: ahora es required
            ], [
                'title.required' => 'El título de la tarea es obligatorio',
                'priority.required' => 'La prioridad es obligatoria',
                'status.required' => 'El estado es obligatorio',
                'assigned_to.required' => 'Debe asignar la tarea a un miembro del equipo', // NUEVO mensaje
                'assigned_to.exists' => 'El usuario seleccionado no es válido',
                'due_date.required' => 'La fecha de entrega es obligatoria', // NUEVO mensaje
            ]);

            $oldAssignedTo = $task->assigned_to;
            $oldStatus = $task->status;

            $task->update($validatedData);

            // Notificar si cambió el asignado
            if ($validatedData['assigned_to'] !== $oldAssignedTo) {
                Notification::create([
                    'user_id' => $validatedData['assigned_to'],
                    'type' => 'task_assigned',
                    'title' => 'Tarea reasignada',
                    'message' => "Te han asignado la tarea: {$task->title}",
                    'data' => json_encode([
                        'task_id' => $task->id,
                        'project_id' => $task->project_id,
                        'assigned_by' => Auth::user()->name,
                    ]),
                    'notifiable_type' => 'App\\Models\\User',  
                    'notifiable_id' => $validatedData['assigned_to'], 
                ]);
            }

            // Notificar si cambió el estado
            if ($task->status !== $oldStatus && $task->status === 'done') {
                // Notificar al creador si la tarea fue completada por otro
                if ($task->assigned_to !== $task->created_by) {
                    Notification::create([
                        'user_id' => $task->created_by,
                        'type' => 'task_completed',
                        'title' => 'Tarea completada',
                        'message' => "{$task->assignedUser->name} ha completado la tarea: {$task->title}",
                        'data' => json_encode([
                            'task_id' => $task->id,
                            'project_id' => $task->project_id,
                            'completed_by' => $task->assignedUser->name,
                        ]),
                    ]);
                }
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tarea actualizada exitosamente',
                    'task' => $task->load(['project', 'creator', 'assignedUser'])
                ]);
            }

            return redirect()
                ->route('projects.show', $task->project)
                ->with('success', 'Tarea actualizada exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (Exception $e) {
            \Log::error('Error updating task: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hubo un error al actualizar la tarea',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Hubo un error al actualizar la tarea');
        }
    }

    /**
     * Eliminar tarea
     */
    public function destroy(Task $task)
    {
        // Solo el creador puede eliminar
        if ($task->created_by !== Auth::id()) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar esta tarea'
                ], 403);
            }
            abort(403, 'No tienes permisos para eliminar esta tarea');
        }

        try {
            $projectId = $task->project_id;
            $task->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tarea eliminada exitosamente'
                ]);
            }

            return redirect()
                ->route('projects.show', $projectId)
                ->with('success', 'Tarea eliminada exitosamente');

        } catch (Exception $e) {
            \Log::error('Error deleting task: ' . $e->getMessage());
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hubo un error al eliminar la tarea'
                ], 500);
            }
            
            return redirect()
                ->back()
                ->with('error', 'Hubo un error al eliminar la tarea');
        }
    }

    /**
     * Actualizar estado de tarea (para Kanban)
     */
    public function updateStatus(Request $request, Task $task)
    {
        // Verificar acceso
        $project = $task->project;
        if (!$project->members->contains(Auth::id()) && $project->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a esta tarea'
            ], 403);
        }

        try {
            $request->validate([
                'status' => 'required|in:todo,in_progress,done',
            ]);

            $oldStatus = $task->status;
            $task->update(['status' => $request->status]);

            // Log para debugging
            \Log::info('Task status updated', [
                'task_id' => $task->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'updated_by' => Auth::id()
            ]);

            // Si la tarea se marcó como completada
            if ($oldStatus !== 'done' && $request->status === 'done') {
                // Notificar al creador si no es el mismo que la completó
                if ($task->created_by !== Auth::id()) {
                    Notification::create([
                        'user_id' => $task->created_by,
                        'type' => 'task_completed',
                        'title' => 'Tarea completada',
                        'message' => Auth::user()->name . " ha completado la tarea: {$task->title}",
                        'data' => json_encode([
                            'task_id' => $task->id,
                            'project_id' => $task->project_id,
                            'completed_by' => Auth::user()->name,
                        ]),
                        'notifiable_type' => 'App\\Models\\User',  
                        'notifiable_id' => $task->created_by,       
                    ]);
                }
            }

            // Forzar recarga del modelo para asegurar que el estado se actualizó
            $task->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente',
                'task' => $task->load(['assignedUser', 'project'])
            ]);

        } catch (Exception $e) {
            \Log::error('Error updating task status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar orden de tarea (para Kanban)
     */
    public function updateOrder(Request $request, Task $task)
    {
        // Verificar acceso
        $project = $task->project;
        if (!$project->members->contains(Auth::id()) && $project->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a esta tarea'
            ], 403);
        }

        try {
            $request->validate([
                'order' => 'required|integer|min:0',
            ]);

            $task->update(['order' => $request->order]);

            return response()->json([
                'success' => true,
                'message' => 'Orden actualizado exitosamente'
            ]);

        } catch (Exception $e) {
            \Log::error('Error updating task order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el orden'
            ], 500);
        }
    }

    /**
     * Marcar tarea como completada (método alternativo)
     */
    public function markComplete(Task $task)
    {
        // Verificar acceso
        $project = $task->project;
        if (!$project->members->contains(Auth::id()) && $project->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a esta tarea'
            ], 403);
        }

        try {
            $task->update(['status' => 'done']);

            // Notificar al creador si no es el mismo que la completó
            if ($task->created_by !== Auth::id()) {
                Notification::create([
                    'user_id' => $task->created_by,
                    'type' => 'task_completed',
                    'title' => 'Tarea completada',
                    'message' => Auth::user()->name . " ha completado la tarea: {$task->title}",
                    'data' => json_encode([
                        'task_id' => $task->id,
                        'project_id' => $task->project_id,
                        'completed_by' => Auth::user()->name,
                    ]),
                    'notifiable_type' => 'App\\Models\\User',  
                    'notifiable_id' => $task->created_by,       
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tarea marcada como completada',
                'task' => $task
            ]);

        } catch (Exception $e) {
            \Log::error('Error marking task as complete: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al completar la tarea'
            ], 500);
        }
    }