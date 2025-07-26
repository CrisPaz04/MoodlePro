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
        $recentActivity = $this->getRecentActivity($user, 10);

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
     */
    public function getActivity(Request $request)
    {
        $limit = $request->get('limit', 20);
        $user = Auth::user();
        
        $activity = $this->getRecentActivity($user, $limit);
        
        return response()->json($activity);
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
     * Obtener actividad reciente del usuario
     */
    private function getRecentActivity($user, $limit = 10)
    {
        $activities = collect();

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
                    'date' => $task->created_at,
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
                    'date' => $message->created_at,
                    'icon' => 'comment',
                    'color' => 'info'
                ];
            });

        $activities = $activities->merge($recentMessages);

        // Recursos subidos
        $recentResources = Resource::where('uploaded_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($resource) {
                return [
                    'type' => 'resource_uploaded',
                    'title' => "Recurso subido: {$resource->title}",
                    'description' => "Categoría: {$resource->category}",
                    'date' => $resource->created_at,
                    'icon' => 'file-upload',
                    'color' => 'success'
                ];
            });

        $activities = $activities->merge($recentResources);

        // Ordenar por fecha y limitar
        return $activities->sortByDesc('date')->take($limit)->values();
    }

    /**
     * Datos para gráfico de progreso de tareas
     */
    private function getTaskProgressChart($user)
    {
        $projects = $user->projects()->with('tasks')->get();
        
        $labels = [];
        $todoData = [];
        $inProgressData = [];
        $doneData = [];
        
        foreach ($projects as $project) {
            $labels[] = $project->title;
            $tasks = $project->tasks;
            
            $todoData[] = $tasks->where('status', 'todo')->count();
            $inProgressData[] = $tasks->where('status', 'in_progress')->count();
            $doneData[] = $tasks->where('status', 'done')->count();
        }
        
        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Por hacer',
                    'data' => $todoData,
                    'backgroundColor' => '#f6c23e',
                ],
                [
                    'label' => 'En progreso',
                    'data' => $inProgressData,
                    'backgroundColor' => '#36b9cc',
                ],
                [
                    'label' => 'Completadas',
                    'data' => $doneData,
                    'backgroundColor' => '#1cc88a',
                ]
            ]
        ]);
    }

    /**
     * Datos para timeline de proyectos
     */
    private function getProjectTimelineChart($user)
    {
        $projects = $user->projects()
            ->orderBy('start_date')
            ->get();
            
        $data = $projects->map(function ($project) {
            return [
                'name' => $project->title,
                'start' => $project->start_date->format('Y-m-d'),
                'end' => $project->deadline->format('Y-m-d'),
                'progress' => $project->tasks->count() > 0 
                    ? round(($project->tasks->where('status', 'done')->count() / $project->tasks->count()) * 100)
                    : 0,
                'status' => $project->status
            ];
        });
        
        return response()->json($data);
    }

    /**
     * Datos para gráfico de productividad
     */
    private function getProductivityChart($user)
    {
        $endDate = now();
        $startDate = now()->subDays(30);
        
        $tasksCompleted = Task::where('assigned_to', $user->id)
            ->where('status', 'done')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->selectRaw('DATE(updated_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        $labels = [];
        $data = [];
        
        // Llenar todos los días del período
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $labels[] = $date->format('M d');
            
            $completed = $tasksCompleted->firstWhere('date', $dateStr);
            $data[] = $completed ? $completed->count : 0;
        }
        
        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Tareas completadas',
                    'data' => $data,
                    'borderColor' => '#4e73df',
                    'backgroundColor' => 'rgba(78, 115, 223, 0.1)',
                    'tension' => 0.3
                ]
            ]
        ]);
    }

    /**
     * Datos para gráfico de carga de trabajo
     */
    private function getWorkloadChart($user)
    {
        $tasks = $user->assignedTasks()
            ->where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(14))
            ->get();
            
        $workloadByDay = [];
        
        for ($i = 0; $i < 14; $i++) {
            $date = now()->addDays($i);
            $dateStr = $date->format('Y-m-d');
            
            $workloadByDay[$date->format('M d')] = $tasks->filter(function ($task) use ($dateStr) {
                return $task->due_date->format('Y-m-d') === $dateStr;
            })->count();
        }
        
        return response()->json([
            'labels' => array_keys($workloadByDay),
            'datasets' => [
                [
                    'label' => 'Tareas por vencer',
                    'data' => array_values($workloadByDay),
                    'backgroundColor' => '#e74a3b',
                    'borderColor' => '#e74a3b',
                    'borderWidth' => 2
                ]
            ]
        ]);
    }

    /**
     * Calcular puntaje de productividad
     */
    private function calculateProductivityScore($user)
    {
        $lastWeek = now()->subWeek();
        
        $tasksCompleted = $user->assignedTasks()
            ->where('status', 'done')
            ->where('updated_at', '>=', $lastWeek)
            ->count();
            
        $tasksOnTime = $user->assignedTasks()
            ->where('status', 'done')
            ->where('updated_at', '>=', $lastWeek)
            ->whereColumn('updated_at', '<=', 'due_date')
            ->count();
            
        $score = 0;
        if ($tasksCompleted > 0) {
            $score = round(($tasksOnTime / $tasksCompleted) * 100);
        }
        
        return $score;
    }

    /**
     * Calcular días de racha
     */
    private function calculateStreak($user)
    {
        $streak = 0;
        $date = now()->startOfDay();
        
        while (true) {
            $hasActivity = Task::where('assigned_to', $user->id)
                ->where('status', 'done')
                ->whereDate('updated_at', $date)
                ->exists();
                
            if (!$hasActivity) {
                break;
            }
            
            $streak++;
            $date->subDay();
        }
        
        return $streak;
    }
}