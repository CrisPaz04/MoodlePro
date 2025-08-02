<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
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
                
                foreach ($membersEmails as $email) {
                    $user = User::where('email', $email)->first();
                    if ($user && !$project->members->contains($user->id)) {
                        $project->members()->attach($user->id, [
                            'role' => 'member',
                            'joined_at' => now()
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('projects.show', $project)
                ->with('success', 'Proyecto creado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating project: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Hubo un error al crear el proyecto');
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
     * Mostrar miembros del proyecto
     */
    public function members(Project $project)
    {
        // Verificar acceso
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            abort(403, 'No tienes acceso a este proyecto');
        }

        $project->load(['members', 'creator']);
        
        // Usuarios disponibles para agregar (que no estén ya en el proyecto)
        $availableUsers = User::whereNotIn('id', $project->members->pluck('id'))
                             ->where('id', '!=', $project->creator_id)
                             ->orderBy('name')
                             ->get();

        return view('projects.members', compact('project', 'availableUsers'));
    }

    /**
     * Agregar miembro al proyecto
     */
    public function addMember(Request $request, Project $project)
    {
        // Solo coordinadores pueden agregar miembros
        $userRole = $project->members->firstWhere('id', Auth::id())->pivot->role ?? null;
        if ($project->creator_id !== Auth::id() && $userRole !== 'coordinator') {
            abort(403, 'No tienes permisos para agregar miembros');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:member,coordinator',
        ]);

        try {
            // Verificar que el usuario no esté ya en el proyecto
            if ($project->members->contains($request->user_id)) {
                return redirect()
                    ->back()
                    ->with('error', 'El usuario ya es miembro de este proyecto');
            }

            $project->members()->attach($request->user_id, [
                'role' => $request->role,
                'joined_at' => now()
            ]);

            $user = User::find($request->user_id);

            return redirect()
                ->back()
                ->with('success', "Se agregó a {$user->name} como {$request->role}");

        } catch (\Exception $e) {
            Log::error('Error adding member: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Hubo un error al agregar el miembro');
        }
    }

    /**
     * Remover miembro del proyecto
     */
    public function removeMember(Project $project, User $user)
    {
        // Solo coordinadores pueden remover miembros
        $userRole = $project->members->firstWhere('id', Auth::id())->pivot->role ?? null;
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