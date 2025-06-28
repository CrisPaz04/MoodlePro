<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        ]);

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

        return redirect()->route('projects.show', $project)
                        ->with('success', 'Proyecto creado exitosamente');
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
        
        $project->members()->attach($request->user_id, [
            'role' => $request->role,
            'joined_at' => now()
        ]);
        
        // Opcional: Enviar notificación al nuevo miembro
        // Notification::send($user, new AddedToProject($project));
        
        return back()->with('success', 'Miembro agregado exitosamente');
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