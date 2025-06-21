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

    public function index()
    {
        $tasks = Auth::user()->assignedTasks()
            ->with(['project', 'creator'])
            ->orderBy('due_date', 'asc')
            ->get();
            
        return view('tasks.index', compact('tasks'));
    }

    public function create(Request $request)
    {
        $project = Project::findOrFail($request->project_id);
        $members = $project->members;
        
        return view('tasks.create', compact('project', 'members'));
    }

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

        Task::create([
            'project_id' => $request->project_id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'assigned_to' => $request->assigned_to,
            'due_date' => $request->due_date,
            'created_by' => Auth::id(),
            'status' => 'todo',
            'order' => Task::where('project_id', $request->project_id)->max('order') + 1
        ]);

        return redirect()->route('projects.show', $request->project_id)
            ->with('success', 'Tarea creada exitosamente');
    }

    public function show(Task $task)
    {
        $task->load(['project', 'assignedUser', 'creator']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $members = $task->project->members;
        return view('tasks.edit', compact('task', 'members'));
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,done',
            'priority' => 'required|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $task->update($request->only(['title', 'description', 'status', 'priority', 'assigned_to', 'due_date']));

        return redirect()->route('projects.show', $task->project_id)
            ->with('success', 'Tarea actualizada exitosamente');
    }

    public function destroy(Task $task)
    {
        $projectId = $task->project_id;
        $task->delete();
        
        return redirect()->route('projects.show', $projectId)
            ->with('success', 'Tarea eliminada exitosamente');
    }

    // API endpoint para drag & drop Kanban
    public function updateStatus(Request $request, Task $task)
    {
        $request->validate([
            'status' => 'required|in:todo,in_progress,done',
            'order' => 'required|integer'
        ]);

        $task->update([
            'status' => $request->status,
            'order' => $request->order
        ]);

        return response()->json(['success' => true]);
    }
}