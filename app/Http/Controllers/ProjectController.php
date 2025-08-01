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
     * Mostrar lista de proyectos
     */
    public function index()
    {
        $projects = Project::with(['creator', 'members'])
                          ->where('creator_id', Auth::id())
                          ->orWhereHas('members', function($query) {
                              $query->where('user_id', Auth::id());
                          })
                          ->latest()
                          ->get();

        return view('projects.index', compact('projects'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Guardar nuevo proyecto
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
     * Mostrar proyecto específico con vista Kanban
     */
    public function show(Project $project)
    {
        // Verificar que el usuario tenga acceso al proyecto
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            abort(403, 'No tienes acceso a este proyecto');
        }

        // Cargar relaciones necesarias para la vista
        $project->load(['creator', 'members', 'messages.user']);
        
        // Organizar tareas por estado para el kanban
        $tasksByStatus = [
            'todo' => $project->tasks()->where('status', 'todo')->orderBy('order')->get(),
            'in_progress' => $project->tasks()->where('status', 'in_progress')->orderBy('order')->get(),
            'done' => $project->tasks()->where('status', 'done')->orderBy('order')->get(),
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
            abort(403, 'No tienes permisos para editar este proyecto');
        }

        return view('projects.edit', compact('project'));
    }

    /**
     * Actualizar proyecto
     */
    public function update(Request $request, Project $project)
    {
        // Solo el creador puede actualizar
        if ($project->creator_id !== Auth::id()) {
            abort(403, 'No tienes permisos para actualizar este proyecto');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'status' => 'required|in:planning,active,completed,cancelled'
        ]);

        $project->update($request->only(['title', 'description', 'deadline', 'status']));

        return redirect()->route('projects.show', $project)
                        ->with('success', 'Proyecto actualizado exitosamente');
    }

    /**
     * Eliminar proyecto
     */
    public function destroy(Project $project)
    {
        // Solo el creador puede eliminar
        if ($project->creator_id !== Auth::id()) {
            abort(403, 'No tienes permisos para eliminar este proyecto');
        }

        $project->delete();
        
        return redirect()->route('projects.index')
                        ->with('success', 'Proyecto eliminado exitosamente');
    }

    /**
     * Mostrar página de gestión de miembros
     */
    public function members(Project $project)
    {
        // Solo coordinadores pueden gestionar miembros
        $userRole = $project->members()
            ->where('user_id', Auth::id())
            ->first()
            ->pivot
            ->role ?? null;
            
        if ($project->creator_id !== Auth::id() && $userRole !== 'coordinator') {
            abort(403, 'No tienes permisos para gestionar miembros');
        }
        
        $members = $project->members()->withPivot('role', 'joined_at')->get();
        $availableUsers = User::whereNotIn('id', $members->pluck('id'))->get();
        
        return view('projects.members', compact('project', 'members', 'availableUsers'));
    }

/**
 * Agregar miembro al proyecto
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
        'role' => 'required|in:member,coordinator'
    ]);
    
    // Verificar que no esté ya en el proyecto
    if ($project->members()->where('user_id', $request->user_id)->exists()) {
        return back()->with('error', 'El usuario ya es miembro del proyecto');
    }
    
    DB::beginTransaction();
    
    try {
        // Agregar miembro al proyecto
        $project->members()->attach($request->user_id, [
            'role' => $request->role,
            'joined_at' => now()
        ]);
        
        // Obtener el usuario agregado
        $newMember = User::findOrFail($request->user_id);
        
        // Crear notificación para el nuevo miembro
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
        ]);

        return back()->with('error', 'Ocurrió un error al intentar agregar al miembro.');
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
        
        // No permitir que el coordinador se elimine a sí mismo
        if ($user->id === $project->creator_id) {
            return back()->with('error', 'No se puede eliminar al creador del proyecto');
        }
        
        // Reasignar tareas del usuario removido
        $project->tasks()
            ->where('assigned_to', $user->id)
            ->update(['assigned_to' => null]);
        
        $project->members()->detach($user->id);
        
        return back()->with('success', 'Miembro eliminado del proyecto');
    }
}