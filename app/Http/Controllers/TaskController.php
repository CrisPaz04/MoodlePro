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
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            abort(403, 'No tienes acceso a este proyecto');
        }
        
        $members = $project->members;
        
        return view('tasks.create', compact('project', 'members'));
    }

/**
 * Guardar nueva tarea (VERSIÓN FINAL CORREGIDA)
 */
public function store(Request $request)
{
    try {
        // Log para debug
        \Log::info('Creando tarea', [
            'user_id' => Auth::id(),
            'project_id' => $request->project_id,
            'request_data' => $request->all()
        ]);

        // Validación
        $validatedData = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date|after_or_equal:today',
            'status' => 'nullable|in:todo,in_progress,done',
        ], [
            'project_id.required' => 'El proyecto es obligatorio',
            'project_id.exists' => 'El proyecto no existe',
            'title.required' => 'El título de la tarea es obligatorio',
            'title.max' => 'El título no puede exceder 255 caracteres',
            'priority.required' => 'La prioridad es obligatoria',
            'priority.in' => 'La prioridad debe ser: baja, media o alta',
            'assigned_to.exists' => 'El usuario asignado no es válido',
            'due_date.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a hoy',
            'status.in' => 'Estado inválido',
        ]);

        // Verificar acceso al proyecto
        $project = Project::findOrFail($validatedData['project_id']);
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a este proyecto',
                    'errors' => ['project_id' => ['No tienes acceso a este proyecto']]
                ], 403);
            }
            abort(403, 'No tienes acceso a este proyecto');
        }

        // Si se asigna a alguien, verificar que sea miembro del proyecto
        if ($validatedData['assigned_to'] && !$project->members->contains($validatedData['assigned_to'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario asignado no es miembro del proyecto',
                    'errors' => ['assigned_to' => ['El usuario asignado no es miembro del proyecto']]
                ], 422);
            }
            return redirect()->back()
                ->withErrors(['assigned_to' => 'El usuario asignado no es miembro del proyecto'])
                ->withInput();
        }

        // Crear la tarea (USANDO NOMENCLATURA CORRECTA)
        $task = Task::create([
            'project_id' => $validatedData['project_id'],
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'priority' => $validatedData['priority'],
            'status' => $validatedData['status'] ?? 'todo',
            'created_by' => Auth::id(),
            'assigned_to' => $validatedData['assigned_to'] ?? Auth::id(),
            'due_date' => $validatedData['due_date'],
            'order' => Task::where('project_id', $validatedData['project_id'])->max('order') + 1,
        ]);

        // Crear notificación si se asigna a otro usuario
        if ($task->assigned_to && $task->assigned_to !== Auth::id()) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'type' => 'task_assigned',
                'title' => 'Nueva tarea asignada',
                'message' => "Te han asignado la tarea: {$task->title}",
                'data' => json_encode([
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'project_name' => $project->title,
                    'creator_name' => Auth::user()->name,
                ]),
            ]);
        }

        // Log de éxito
        \Log::info('Tarea creada exitosamente', [
            'task_id' => $task->id,
            'user_id' => Auth::id()
        ]);

        // Respuesta para peticiones AJAX/JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tarea creada exitosamente',
                'task' => $task->load(['assignedUser', 'creator']),
                'redirect' => route('projects.show', $project)
            ], 201);
        }

        // Respuesta para peticiones normales
        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Tarea creada exitosamente');

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Log de error de validación
        \Log::warning('Error de validación al crear tarea', [
            'user_id' => Auth::id(),
            'errors' => $e->errors()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        return redirect()
            ->back()
            ->withErrors($e->errors())
            ->withInput();

    } catch (Exception $e) {
        // Log del error
        \Log::error('Error al crear tarea: ' . $e->getMessage(), [
            'user_id' => Auth::id(),
            'project_id' => $request->project_id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor. Por favor intenta nuevamente.',
                'errors' => ['general' => ['Ha ocurrido un error interno']]
            ], 500);
        }

        return redirect()
            ->back()
            ->withErrors(['error' => 'Error al crear la tarea. Por favor intenta nuevamente.'])
            ->withInput();
    }
}

    /**
     * Mostrar detalles de una tarea
     */
    public function show(Task $task)
    {
        // Verificar acceso
        $project = $task->project;
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
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
        // Verificar acceso (solo creador o asignado)
        if ($task->creator_id !== Auth::id() && $task->assigned_to !== Auth::id()) {
            abort(403, 'No tienes permisos para editar esta tarea');
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
        // Verificar acceso
        if ($task->creator_id !== Auth::id() && $task->assigned_to !== Auth::id()) {
            abort(403, 'No tienes permisos para editar esta tarea');
        }

        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'priority' => 'required|in:low,medium,high',
                'status' => 'required|in:todo,in_progress,done',
                'assigned_to' => 'nullable|exists:users,id',
                'due_date' => 'nullable|date',
            ], [
                'title.required' => 'El título de la tarea es obligatorio',
                'priority.required' => 'La prioridad es obligatoria',
                'status.required' => 'El estado es obligatorio',
            ]);

            $oldAssignedTo = $task->assigned_to;
            $oldStatus = $task->status;

            $task->update($validatedData);

            // Notificar si cambió el asignado
            if ($validatedData['assigned_to'] && $validatedData['assigned_to'] !== $oldAssignedTo) {
                Notification::create([
                    'user_id' => $validatedData['assigned_to'],
                    'type' => 'task_assigned',
                    'title' => 'Tarea reasignada',
                    'message' => "Te han asignado la tarea: {$task->title}",
                    'data' => json_encode([
                        'task_id' => $task->id,
                        'project_id' => $task->project_id,
                    ]),
                ]);
            }

            // Notificar si se completó
            if ($oldStatus !== 'done' && $validatedData['status'] === 'done') {
                Notification::create([
                    'user_id' => $task->creator_id,
                    'type' => 'task_completed',
                    'title' => 'Tarea completada',
                    'message' => "La tarea '{$task->title}' ha sido completada",
                    'data' => json_encode([
                        'task_id' => $task->id,
                        'project_id' => $task->project_id,
                        'completed_by' => Auth::user()->name,
                    ]),
                ]);
            }

            return redirect()
                ->route('projects.show', $task->project)
                ->with('success', 'Tarea actualizada exitosamente');

        } catch (Exception $e) {
            \Log::error('Error updating task: ' . $e->getMessage());
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
        if ($task->creator_id !== Auth::id()) {
            abort(403, 'Solo el creador puede eliminar esta tarea');
        }

        try {
            $project = $task->project;
            $task->delete();

            return redirect()
                ->route('projects.show', $project)
                ->with('success', 'Tarea eliminada exitosamente');

        } catch (Exception $e) {
            \Log::error('Error deleting task: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Hubo un error al eliminar la tarea');
        }
    }

    /**
     * API: Actualizar estado de tarea (para Kanban)
     */
    public function updateStatus(Request $request, Task $task)
    {
        try {
            // Verificar acceso
            if ($task->creator_id !== Auth::id() && $task->assigned_to !== Auth::id()) {
                return response()->json(['error' => 'Sin permisos'], 403);
            }

            $request->validate([
                'status' => 'required|in:todo,in_progress,done',
                'order' => 'nullable|integer|min:0'
            ]);

            $oldStatus = $task->status;
            $task->status = $request->status;
            
            if ($request->has('order')) {
                $task->order = $request->order;
            }
            
            $task->save();

            // Notificar si se completó
            if ($oldStatus !== 'done' && $request->status === 'done') {
                Notification::create([
                    'user_id' => $task->creator_id,
                    'type' => 'task_completed',
                    'title' => 'Tarea completada',
                    'message' => "La tarea '{$task->title}' ha sido completada",
                    'data' => json_encode([
                        'task_id' => $task->id,
                        'project_id' => $task->project_id,
                        'completed_by' => Auth::user()->name,
                    ]),
                ]);
            }

            return response()->json([
                'success' => true,
                'task' => $task->load(['project', 'creator', 'assignedUser'])
            ]);

        } catch (Exception $e) {
            \Log::error('Error updating task status: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

    /**
     * API: Marcar tarea como completada
     */
    public function markComplete(Task $task)
    {
        try {
            // Verificar acceso
            if ($task->creator_id !== Auth::id() && $task->assigned_to !== Auth::id()) {
                return response()->json(['error' => 'Sin permisos'], 403);
            }

            if ($task->status === 'done') {
                return response()->json(['error' => 'La tarea ya está completada'], 400);
            }

            $task->status = 'done';
            $task->save();

            // Crear notificación
            if ($task->creator_id !== Auth::id()) {
                Notification::create([
                    'user_id' => $task->creator_id,
                    'type' => 'task_completed',
                    'title' => 'Tarea completada',
                    'message' => "La tarea '{$task->title}' ha sido completada por " . Auth::user()->name,
                    'data' => json_encode([
                        'task_id' => $task->id,
                        'project_id' => $task->project_id,
                        'completed_by' => Auth::user()->name,
                    ]),
                ]);
            }

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            \Log::error('Error marking task complete: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }
}