<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar lista de proyectos CON CONTADORES ARREGLADOS
     */
    public function index()
    {
        $user = Auth::user();
        
        // Obtener proyectos del usuario
        $projects = Project::with(['creator', 'members', 'tasks'])
                          ->where('creator_id', $user->id)
                          ->orWhereHas('members', function($query) use ($user) {
                              $query->where('user_id', $user->id);
                          })
                          ->latest()
                          ->get();

        // Calcular estadísticas correctamente
        $stats = [
            'total' => $projects->count(),
            'active' => $projects->where('status', 'active')->count(),
            'completed' => $projects->where('status', 'completed')->count(),
            'planning' => $projects->where('status', 'planning')->count(),
        ];

        return view('projects.index', compact('projects', 'stats'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Guardar nuevo proyecto - CORREGIDO PARA PROCESAR MIEMBROS
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'deadline' => 'required|date|after:start_date',
            'members' => 'nullable|string', // JSON string de emails
        ]);

        DB::beginTransaction();
        
        try {
            // Crear el proyecto
            $project = Project::create([
                'title' => $request->title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'deadline' => $request->deadline,
                'creator_id' => Auth::id(),
                'status' => 'planning'
            ]);

            // Agregar al creador como coordinador
            $project->members()->attach(Auth::id(), [
                'role' => 'coordinator',
                'joined_at' => now()
            ]);

            // Procesar miembros adicionales si se enviaron
            if ($request->filled('members')) {
                $membersEmails = json_decode($request->members, true);
                
                if (is_array($membersEmails) && !empty($membersEmails)) {
                    $addedMembers = [];
                    $notFoundEmails = [];
                    
                    foreach ($membersEmails as $email) {
                        // Buscar usuario por email
                        $user = User::where('email', $email)->first();
                        
                        if ($user) {
                            // Verificar que no sea el creador (ya está agregado)
                            if ($user->id !== Auth::id()) {
                                // Verificar que no esté ya agregado
                                if (!$project->members()->where('user_id', $user->id)->exists()) {
                                    $project->members()->attach($user->id, [
                                        'role' => 'member',
                                        'joined_at' => now()
                                    ]);
                                    $addedMembers[] = $user->name . ' (' . $user->email . ')';
                                    
                                    // Crear notificación para el nuevo miembro
                                    if (class_exists('\App\Models\Notification')) {
                                        Notification::memberAddedToProject($user, $project, Auth::user());
                                    }
                                }
                            }
                        } else {
                            $notFoundEmails[] = $email;
                        }
                    }
                    
                    // Log para debugging
                    if (!empty($addedMembers)) {
                        Log::info("Miembros agregados al proyecto {$project->title}:", $addedMembers);
                    }
                    
                    if (!empty($notFoundEmails)) {
                        Log::warning("Emails no encontrados al crear proyecto {$project->title}:", $notFoundEmails);
                    }
                }
            }

            DB::commit();
            
            // Mensaje de éxito con información detallada
            $successMessage = 'Proyecto creado exitosamente';
            if ($request->filled('members')) {
                $membersCount = $project->members()->where('user_id', '!=', Auth::id())->count();
                if ($membersCount > 0) {
                    $successMessage .= " con {$membersCount} miembro(s) agregado(s)";
                }
            }

            return redirect()->route('projects.show', $project)
                            ->with('success', $successMessage);
                            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al crear proyecto: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->except(['_token']),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al crear el proyecto. Por favor intenta nuevamente.');
        }
    }

    /**
     * Mostrar detalles del proyecto
     */
    public function show(Project $project)
    {
        // Verificar acceso
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            abort(403, 'No tienes acceso a este proyecto');
        }

        $project->load(['members', 'tasks.assignedUser', 'creator']);
        
        // Organizar tareas por estado para el Kanban
        $tasksByStatus = [
            'todo' => $project->tasks->where('status', 'todo')->sortBy('order'),
            'in_progress' => $project->tasks->where('status', 'in_progress')->sortBy('order'),
            'done' => $project->tasks->where('status', 'done')->sortBy('order'),
        ];

        return view('projects.show', compact('project', 'tasksByStatus'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Project $project)
    {
        // Solo el creador puede editar
        if ($project->creator_id !== Auth::id()) {
            abort(403, 'Solo el creador puede editar este proyecto');
        }

        return view('projects.edit', compact('project'));
    }

    /**
     * Actualizar proyecto
     */
    public function update(Request $request, Project $project)
    {
        // Solo el creador puede editar
        if ($project->creator_id !== Auth::id()) {
            abort(403, 'Solo el creador puede editar este proyecto');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'deadline' => 'required|date|after:start_date',
            'status' => 'required|in:planning,active,paused,completed,cancelled',
        ]);

        try {
            $project->update([
                'title' => $request->title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'deadline' => $request->deadline,
                'status' => $request->status,
            ]);

            return redirect()
                ->route('projects.show', $project)
                ->with('success', 'Proyecto actualizado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error updating project: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Hubo un error al actualizar el proyecto');
        }
    }

    /**
     * Eliminar proyecto
     */
    public function destroy(Project $project)
    {
        // Solo el creador puede eliminar
        if ($project->creator_id !== Auth::id()) {
            abort(403, 'Solo el creador puede eliminar este proyecto');
        }

        try {
            $project->delete();

            return redirect()
                ->route('projects.index')
                ->with('success', 'Proyecto eliminado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error deleting project: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Hubo un error al eliminar el proyecto');
        }
    }

    /**
     * Mostrar miembros del proyecto - CORREGIDO
     */
    public function members(Project $project)
    {
        // Verificar que el usuario tenga acceso al proyecto
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            abort(403, 'No tienes acceso a este proyecto');
        }

        // Solo coordinadores pueden gestionar miembros
        $userRole = $project->members()
            ->where('user_id', Auth::id())
            ->first()
            ->pivot
            ->role ?? null;
            
        if ($project->creator_id !== Auth::id() && $userRole !== 'coordinator') {
            abort(403, 'No tienes permisos para gestionar miembros');
        }

        // CORREGIDO: Cargar miembros con pivot data
        $project->load(['members', 'creator']);
        $members = $project->members()->withPivot('role', 'joined_at')->get();
        
        // Usuarios disponibles para agregar (que no estén ya en el proyecto)
        $availableUsers = User::whereNotIn('id', $members->pluck('id'))
                             ->orderBy('name')
                             ->get();

        return view('projects.members', compact('project', 'members', 'availableUsers'));
    }

    /**
     * Agregar miembro al proyecto - MEJORADO CON NOTIFICACIONES
     */
    public function addMember(Request $request, Project $project)
    {
        // Solo coordinadores pueden agregar miembros
        $userRole = $project->members()
            ->where('user_id', Auth::id())
            ->first()
            ->pivot
            ->role ?? null;
            
        if ($project->creator_id !== Auth::id() && $userRole !== 'coordinator') {
            abort(403, 'No tienes permisos para agregar miembros');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:member,coordinator',
        ]);

        DB::beginTransaction();
        
        try {
            // Verificar que el usuario no esté ya en el proyecto
            if ($project->members()->where('user_id', $request->user_id)->exists()) {
                return back()->with('error', 'El usuario ya es miembro del proyecto');
            }

            // Agregar miembro al proyecto
            $project->members()->attach($request->user_id, [
                'role' => $request->role,
                'joined_at' => now()
            ]);

            // Obtener el usuario agregado
            $newMember = User::findOrFail($request->user_id);
            
            // Crear notificación para el nuevo miembro
            if (class_exists('\App\Models\Notification')) {
                Notification::memberAddedToProject($newMember, $project, Auth::user());
                
                // Notificar a otros coordinadores sobre el nuevo miembro
                $coordinators = $project->members()
                    ->where('project_members.role', 'coordinator')
                    ->where('users.id', '!=', Auth::id()) // Excepto quien agregó
                    ->where('users.id', '!=', $request->user_id) // Excepto el nuevo miembro
                    ->get();
                    
                foreach ($coordinators as $coordinator) {
                    Notification::create([
                        'user_id' => $coordinator->id,
                        'type' => 'member_added_notification',
                        'title' => 'Nuevo miembro agregado',
                        'message' => ':added_by agregó a :new_member al proyecto :project_title',
                        'data' => [
                            'project_title' => $project->title,
                            'project_id' => $project->id,
                            'added_by' => Auth::user()->name,
                            'new_member' => $newMember->name,
                            'new_member_role' => $request->role === 'coordinator' ? 'coordinador' : 'miembro',
                        ],
                        'related_type' => Project::class,
                        'related_id' => $project->id,
                        'action_url' => route('projects.members', $project->id),
                    ]);
                }
            }

            DB::commit();

            $successMessage = "Miembro {$newMember->name} agregado exitosamente como " . 
                            ($request->role === 'coordinator' ? 'coordinador' : 'miembro');

            return back()->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al agregar miembro al proyecto: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'project_id' => $project->id,
                'new_member_id' => $request->user_id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Error al agregar el miembro. Por favor intenta nuevamente.');
        }
    }

    /**
     * Remover miembro del proyecto
     */
    public function removeMember(Project $project, User $user)
    {
        // Solo coordinadores pueden remover miembros
        $userRole = $project->members()
            ->where('user_id', Auth::id())
            ->first()
            ->pivot
            ->role ?? null;
            
        if ($project->creator_id !== Auth::id() && $userRole !== 'coordinator') {
            abort(403, 'No tienes permisos para remover miembros');
        }

        // No se puede remover al creador
        if ($user->id === $project->creator_id) {
            return redirect()
                ->back()
                ->with('error', 'No se puede remover al creador del proyecto');
        }

        try {
            // Reasignar tareas del usuario removido
            $project->tasks()
                ->where('assigned_to', $user->id)
                ->update(['assigned_to' => null]);

            $project->members()->detach($user->id);

            return redirect()
                ->back()
                ->with('success', "Se removió a {$user->name} del proyecto");

        } catch (\Exception $e) {
            Log::error('Error removing member: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Hubo un error al remover el miembro');
        }
    }
}