<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\Task;
use App\Models\Message;
use App\Models\Resource;
use Carbon\Carbon;
use DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar el dashboard principal
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
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

        // Calcular porcentaje de completitud
        if ($stats['total_tasks'] > 0) {
            $stats['completion_rate'] = round(($stats['completed_tasks'] / $stats['total_tasks']) * 100);
        } else {
            $stats['completion_rate'] = 0;
        }

        // Proyectos recientes (últimos 5)
        $recentProjects = $user->projects()
            ->with(['tasks'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($project) {
                $project->task_completion = $project->tasks->count() > 0 
                    ? round(($project->tasks->where('status', 'done')->count() / $project->tasks->count()) * 100)
                    : 0;
                return $project;
            });

        // Próximos deadlines (próximas 5 tareas)
        $upcomingDeadlines = $user->assignedTasks()
            ->with(['project'])
            ->where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        // Actividad reciente (últimos 10 eventos)
        $recentActivity = collect(); // Por ahora vacío, se carga por AJAX

        return view('dashboard.index', compact(
            'stats', 
            'recentProjects', 
            'upcomingDeadlines',
            'recentActivity'
        ));
    }

    /**
     * API: Obtener estadísticas actualizadas
     */
    public function getStats()
    {
        $user = Auth::user();
        
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
            'total_resources' => Resource::where('uploaded_by', $user->id)->count(),
            'total_messages' => Message::where('user_id', $user->id)->count(),
        ];

        // Estadísticas adicionales
        $stats['productivity_score'] = $this->calculateProductivityScore($user);
        $stats['streak_days'] = $this->calculateStreak($user);
        
        return response()->json($stats);
    }

    /**
     * API: Obtener actividad reciente
     * CORREGIDO: Nombre del método debe coincidir con la ruta
     */
    public function getRecentActivity(Request $request)
    {
        $limit = $request->get('limit', 20);
        $user = Auth::user();
        
        $activities = collect();

        try {
            // Tareas creadas recientemente
            $recentTasks = Task::where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id)
                ->with(['project'])
                ->orderBy('created_at', 'desc')
                ->take($limit)
                ->get()
                ->map(function ($task) {
                    return [
                        'type' => 'task_created',
                        'title' => "Nueva tarea: {$task->title}",
                        'description' => "En proyecto: {$task->project->title}",
                        'date' => $task->created_at->toIso8601String(),
                        'icon' => 'tasks',
                        'color' => 'primary'
                    ];
                });

            $activities = $activities->merge($recentTasks);

            // Mensajes recientes
            $recentMessages = Message::where('user_id', $user->id)
                ->with(['project'])
                ->orderBy('created_at', 'desc')
                ->take($limit)
                ->get()
                ->map(function ($message) {
                    return [
                        'type' => 'message_sent',
                        'title' => "Mensaje en: {$message->project->title}",
                        'description' => substr($message->content, 0, 50) . '...',
                        'date' => $message->created_at->toIso8601String(),
                        'icon' => 'comment',
                        'color' => 'info'
                    ];
                });

            $activities = $activities->merge($recentMessages);

            // Recursos subidos
            $recentResources = Resource::where('uploaded_by', $user->id)
                ->with(['project'])
                ->orderBy('created_at', 'desc')
                ->take($limit)
                ->get()
                ->map(function ($resource) {
                    return [
                        'type' => 'resource_uploaded',
                        'title' => "Recurso subido: {$resource->title}",
                        'description' => "En proyecto: {$resource->project->title}",
                        'date' => $resource->created_at->toIso8601String(),
                        'icon' => 'file-upload',
                        'color' => 'success'
                    ];
                });

            $activities = $activities->merge($recentResources);

            // Ordenar por fecha y limitar
            $activities = $activities->sortByDesc('date')->take($limit)->values();

            return response()->json($activities);

        } catch (\Exception $e) {
            // Log del error para debugging
            \Log::error('Error en getRecentActivity: ' . $e->getMessage());
            
            // Retornar array vacío en caso de error
            return response()->json([]);
        }
    }

    /**
     * API: Obtener datos para gráficos
     */
    public function getChartData($type)
    {
        $user = Auth::user();
        
        switch ($type) {
            case 'task_progress':
                return $this->getTaskProgressChart($user);
                
            case 'project_timeline':
                return $this->getProjectTimelineChart($user);
                
            case 'productivity':
                return $this->getProductivityChart($user);
                
            case 'workload':
                return $this->getWorkloadChart($user);
                
            default:
                return response()->json(['error' => 'Tipo de gráfico no válido'], 400);
        }
    }

    /**
     * Gráfico de progreso de tareas
     */
    private function getTaskProgressChart($user)
    {
        $data = [
            'labels' => ['Por hacer', 'En progreso', 'Completadas'],
            'datasets' => [[
                'label' => 'Tareas',
                'data' => [
                    $user->assignedTasks()->where('status', 'todo')->count(),
                    $user->assignedTasks()->where('status', 'in_progress')->count(),
                    $user->assignedTasks()->where('status', 'done')->count(),
                ],
                'backgroundColor' => ['#FFA500', '#87CEEB', '#28a745'],
                'borderColor' => ['#ff8c00', '#4682b4', '#218838'],
                'borderWidth' => 1
            ]]
        ];
        
        return response()->json($data);
    }

    /**
     * Gráfico de línea de tiempo de proyectos
     */
    private function getProjectTimelineChart($user)
    {
        $lastSixMonths = [];
        $projectCounts = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $lastSixMonths[] = $date->format('M Y');
            
            $count = $user->projects()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
                
            $projectCounts[] = $count;
        }
        
        $data = [
            'labels' => $lastSixMonths,
            'datasets' => [[
                'label' => 'Proyectos creados',
                'data' => $projectCounts,
                'fill' => false,
                'borderColor' => '#007bff',
                'tension' => 0.1
            ]]
        ];
        
        return response()->json($data);
    }

    /**
     * Gráfico de productividad
     */
    private function getProductivityChart($user)
    {
        $lastSevenDays = [];
        $tasksCompleted = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $lastSevenDays[] = $date->format('D');
            
            $count = $user->assignedTasks()
                ->where('status', 'done')
                ->whereDate('updated_at', $date->toDateString())
                ->count();
                
            $tasksCompleted[] = $count;
        }
        
        $data = [
            'labels' => $lastSevenDays,
            'datasets' => [[
                'label' => 'Tareas completadas',
                'data' => $tasksCompleted,
                'backgroundColor' => 'rgba(40, 167, 69, 0.2)',
                'borderColor' => '#28a745',
                'borderWidth' => 2,
                'fill' => true
            ]]
        ];
        
        return response()->json($data);
    }

    /**
     * Gráfico de carga de trabajo
     */
    private function getWorkloadChart($user)
    {
        $projects = $user->projects()->withCount(['tasks' => function ($query) use ($user) {
            $query->where('assigned_to', $user->id);
        }])->get();
        
        $data = [
            'labels' => $projects->pluck('title'),
            'datasets' => [[
                'label' => 'Tareas asignadas',
                'data' => $projects->pluck('tasks_count'),
                'backgroundColor' => [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ],
                'borderWidth' => 1
            ]]
        ];
        
        return response()->json($data);
    }

    /**
     * Calcular puntuación de productividad
     */
    private function calculateProductivityScore($user)
    {
        $totalTasks = $user->assignedTasks()->count();
        $completedTasks = $user->assignedTasks()->where('status', 'done')->count();
        $overdueTasks = $user->assignedTasks()
            ->where('due_date', '<', now())
            ->where('status', '!=', 'done')
            ->count();
        
        if ($totalTasks == 0) return 0;
        
        $baseScore = ($completedTasks / $totalTasks) * 100;
        $penalty = ($overdueTasks / $totalTasks) * 20;
        
        return max(0, round($baseScore - $penalty));
    }

    /**
     * Calcular racha de días
     */
    private function calculateStreak($user)
    {
        $streak = 0;
        $date = Carbon::now();
        
        while (true) {
            $hasActivity = $user->assignedTasks()
                ->whereDate('updated_at', $date->toDateString())
                ->where('status', 'done')
                ->exists();
                
            if (!$hasActivity) break;
            
            $streak++;
            $date->subDay();
        }
        
        return $streak;
    }
}