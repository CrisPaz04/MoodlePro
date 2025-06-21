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

    // Mostrar lista de proyectos
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

    // Mostrar formulario de creación
    public function create()
    {
        return view('projects.create');
    }

    // Guardar nuevo proyecto
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

    // Mostrar proyecto específico
    public function show(Project $project)
    {
        $project->load(['creator', 'members', 'tasks.assignedUser', 'messages.user']);
        
        return view('projects.show', compact('project'));
    }

    // Mostrar formulario de edición
    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    // Actualizar proyecto
    public function update(Request $request, Project $project)
    {
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

    // Eliminar proyecto
    public function destroy(Project $project)
    {
        $project->delete();
        
        return redirect()->route('projects.index')
                        ->with('success', 'Proyecto eliminado exitosamente');
    }
}