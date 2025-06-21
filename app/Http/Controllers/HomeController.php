<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\Task;
use App\Models\Message;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        // Estadísticas principales
        $stats = [
            'total_projects' => $user->projects()->count(),
            'active_projects' => $user->projects()->where('status', 'active')->count(),
            'total_tasks' => $user->assignedTasks()->count(),
            'completed_tasks' => $user->assignedTasks()->where('status', 'done')->count(),
            'in_progress_tasks' => $user->assignedTasks()->where('status', 'in_progress')->count(),
            'todo_tasks' => $user->assignedTasks()->where('status', 'todo')->count(),
            'overdue_tasks' => $user->assignedTasks()
                ->where('due_date', '<', now())
                ->where('status', '!=', 'done')
                ->count(),
        ];

        // Proyectos recientes (últimos 5)
        $recentProjects = $user->projects()
            ->with(['tasks'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Próximos deadlines (próximas 5 tareas)
        $upcomingDeadlines = $user->assignedTasks()
            ->with(['project'])
            ->where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        // Redirigir según tipo de usuario si es necesario
        if ($user->hasRole && $user->hasRole('teacher')) {
            return $this->teacherDashboard($user, $stats);
        }

        return view('dashboard.index', compact('stats', 'recentProjects', 'upcomingDeadlines'));
    }

    /**
     * Dashboard específico para profesores (futuro)
     */
    private function teacherDashboard($user, $stats)
    {
        // Métricas específicas de profesores
        $teacherStats = [
            'created_projects' => $user->createdProjects()->count(),
            'total_students' => $user->createdProjects()
                ->with('members')
                ->get()
                ->pluck('members')
                ->flatten()
                ->unique('id')
                ->count(),
            'pending_reviews' => Task::whereHas('project', function($query) use ($user) {
                $query->where('creator_id', $user->id);
            })->where('status', 'done')->count(),
        ];

        $stats = array_merge($stats, $teacherStats);

        return view('dashboard.teacher', compact('stats'));
    }

    /**
     * API endpoint para métricas del dashboard
     */
    public function getStats()
    {
        $user = Auth::user();
        
        return response()->json([
            'total_projects' => $user->projects()->count(),
            'active_projects' => $user->projects()->where('status', 'active')->count(),
            'total_tasks' => $user->assignedTasks()->count(),
            'completed_tasks' => $user->assignedTasks()->where('status', 'done')->count(),
            'overdue_tasks' => $user->assignedTasks()
                ->where('due_date', '<', now())
                ->where('status', '!=', 'done')
                ->count(),
            'completion_rate' => $user->assignedTasks()->count() > 0 
                ? round(($user->assignedTasks()->where('status', 'done')->count() / $user->assignedTasks()->count()) * 100, 1)
                : 0
        ]);
    }

    /**
     * API endpoint para actividad reciente
     */
    public function getActivity()
    {
        $user = Auth::user();
        
        // Combinar diferentes tipos de actividad
        $activities = collect();

        // Tareas recientes
        $recentTasks = $user->assignedTasks()
            ->with('project')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($task) {
                return [
                    'type' => 'task',
                    'title' => $task->title,
                    'subtitle' => $task->project->title,
                    'date' => $task->updated_at,
                    'status' => $task->status,
                    'url' => route('projects.show', $task->project_id)
                ];
            });

        // Mensajes recientes
        $recentMessages = $user->messages()
            ->with('project')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($message) {
                return [
                    'type' => 'message',
                    'title' => 'Mensaje en ' . $message->project->title,
                    'subtitle' => substr($message->content, 0, 50) . '...',
                    'date' => $message->created_at,
                    'url' => route('projects.chat', $message->project_id)
                ];
            });

        // Proyectos actualizados
        $updatedProjects = $user->projects()
            ->orderBy('updated_at', 'desc')
            ->take(3)
            ->get()
            ->map(function($project) {
                return [
                    'type' => 'project',
                    'title' => $project->title,
                    'subtitle' => 'Proyecto actualizado',
                    'date' => $project->updated_at,
                    'status' => $project->status,
                    'url' => route('projects.show', $project)
                ];
            });

        // Combinar y ordenar por fecha
        $allActivity = $activities
            ->merge($recentTasks)
            ->merge($recentMessages)
            ->merge($updatedProjects)
            ->sortByDesc('date')
            ->take(10)
            ->values();

        return response()->json($allActivity);
    }

    /**
     * Obtener datos para gráficos
     */
    public function getChartData()
    {
        $user = Auth::user();
        
        // Datos para gráfico de progreso de tareas
        $taskProgress = [
            'completed' => $user->assignedTasks()->where('status', 'done')->count(),
            'in_progress' => $user->assignedTasks()->where('status', 'in_progress')->count(),
            'todo' => $user->assignedTasks()->where('status', 'todo')->count(),
        ];

        // Datos para gráfico de proyectos por estado
        $projectStatus = [
            'planning' => $user->projects()->where('status', 'planning')->count(),
            'active' => $user->projects()->where('status', 'active')->count(),
            'completed' => $user->projects()->where('status', 'completed')->count(),
            'cancelled' => $user->projects()->where('status', 'cancelled')->count(),
        ];

        // Actividad de los últimos 7 días
        $weeklyActivity = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $weeklyActivity[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'tasks_completed' => $user->assignedTasks()
                    ->whereDate('updated_at', $date)
                    ->where('status', 'done')
                    ->count(),
                'messages_sent' => $user->messages()
                    ->whereDate('created_at', $date)
                    ->count()
            ];
        }

        return response()->json([
            'task_progress' => $taskProgress,
            'project_status' => $projectStatus,
            'weekly_activity' => $weeklyActivity
        ]);
    }
}