<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;

class SendScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar notificaciones programadas para proyectos y tareas próximos a vencer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando envío de notificaciones programadas...');
        
        // Notificar proyectos próximos a vencer
        $this->notifyUpcomingProjectDeadlines();
        
        // Notificar tareas vencidas
        $this->notifyOverdueTasks();
        
        // Notificar tareas próximas a vencer
        $this->notifyUpcomingTaskDeadlines();
        
        // Resumen de actividad semanal (los lunes)
        if (now()->isMonday()) {
            $this->sendWeeklySummary();
        }
        
        $this->info('Notificaciones programadas enviadas exitosamente.');
        
        return Command::SUCCESS;
    }

    /**
     * Notificar proyectos próximos a vencer
     */
    protected function notifyUpcomingProjectDeadlines()
    {
        $this->info('Verificando proyectos próximos a vencer...');
        
        // Proyectos que vencen en 7, 3 y 1 día
        $notificationDays = [7, 3, 1];
        
        foreach ($notificationDays as $days) {
            $targetDate = now()->addDays($days)->startOfDay();
            
            $projects = Project::where('status', 'active')
                ->whereDate('deadline', $targetDate)
                ->with(['members', 'creator'])
                ->get();
            
            foreach ($projects as $project) {
                // Notificar al creador
                $this->createProjectDeadlineNotification($project->creator, $project, $days);
                
                // Notificar a coordinadores
                $coordinators = $project->members()
                    ->wherePivot('role', 'coordinator')
                    ->get();
                
                foreach ($coordinators as $coordinator) {
                    $this->createProjectDeadlineNotification($coordinator, $project, $days);
                }
                
                $this->info("- Notificado proyecto '{$project->title}' que vence en {$days} días");
            }
        }
    }

    /**
     * Notificar tareas vencidas
     */
    protected function notifyOverdueTasks()
    {
        $this->info('Verificando tareas vencidas...');
        
        $overdueTasks = Task::where('status', '!=', 'done')
            ->whereDate('due_date', '<', now())
            ->whereDoesntHave('notifications', function ($query) {
                // No notificar si ya se envió una notificación de vencimiento hoy
                $query->where('type', Notification::TYPE_TASK_OVERDUE)
                      ->whereDate('created_at', today());
            })
            ->with(['assignedUser', 'project', 'creator'])
            ->get();
        
        foreach ($overdueTasks as $task) {
            $daysOverdue = now()->diffInDays($task->due_date);
            
            // Notificar al usuario asignado
            if ($task->assignedUser) {
                Notification::create([
                    'user_id' => $task->assigned_to,
                    'type' => Notification::TYPE_TASK_OVERDUE,
                    'title' => 'Tarea vencida',
                    'message' => 'La tarea ":task_title" venció hace :days días',
                    'data' => [
                        'task_title' => $task->title,
                        'project_title' => $task->project->title,
                        'days' => $daysOverdue,
                        'due_date' => $task->due_date->format('d/m/Y'),
                    ],
                    'related_type' => Task::class,
                    'related_id' => $task->id,
                    'action_url' => route('tasks.show', $task),
                ]);
            }
            
            // También notificar al creador de la tarea si es diferente
            if ($task->created_by !== $task->assigned_to) {
                Notification::create([
                    'user_id' => $task->created_by,
                    'type' => Notification::TYPE_TASK_OVERDUE,
                    'title' => 'Tarea vencida',
                    'message' => 'La tarea ":task_title" asignada a :assigned_to venció hace :days días',
                    'data' => [
                        'task_title' => $task->title,
                        'project_title' => $task->project->title,
                        'assigned_to' => $task->assignedUser?->name ?? 'Sin asignar',
                        'days' => $daysOverdue,
                        'due_date' => $task->due_date->format('d/m/Y'),
                    ],
                    'related_type' => Task::class,
                    'related_id' => $task->id,
                    'action_url' => route('tasks.show', $task),
                ]);
            }
            
            $this->info("- Notificada tarea vencida '{$task->title}'");
        }
    }

    /**
     * Notificar tareas próximas a vencer
     */
    protected function notifyUpcomingTaskDeadlines()
    {
        $this->info('Verificando tareas próximas a vencer...');
        
        // Notificar tareas que vencen mañana
        $tomorrow = now()->addDay()->startOfDay();
        
        $upcomingTasks = Task::where('status', '!=', 'done')
            ->whereDate('due_date', $tomorrow)
            ->with(['assignedUser', 'project'])
            ->get();
        
        foreach ($upcomingTasks as $task) {
            if ($task->assignedUser) {
                Notification::create([
                    'user_id' => $task->assigned_to,
                    'type' => 'task_deadline_tomorrow',
                    'title' => 'Tarea por vencer',
                    'message' => 'La tarea ":task_title" vence mañana',
                    'data' => [
                        'task_title' => $task->title,
                        'project_title' => $task->project->title,
                        'due_date' => $task->due_date->format('d/m/Y'),
                        'priority' => $task->priority,
                    ],
                    'related_type' => Task::class,
                    'related_id' => $task->id,
                    'action_url' => route('tasks.show', $task),
                ]);
                
                $this->info("- Notificada tarea '{$task->title}' que vence mañana");
            }
        }
        
        // Notificar tareas que vencen en 3 días (solo alta prioridad)
        $in3Days = now()->addDays(3)->startOfDay();
        
        $highPriorityTasks = Task::where('status', '!=', 'done')
            ->where('priority', 'high')
            ->whereDate('due_date', $in3Days)
            ->with(['assignedUser', 'project'])
            ->get();
        
        foreach ($highPriorityTasks as $task) {
            if ($task->assignedUser) {
                Notification::create([
                    'user_id' => $task->assigned_to,
                    'type' => 'task_deadline_soon',
                    'title' => 'Tarea de alta prioridad próxima a vencer',
                    'message' => 'La tarea de alta prioridad ":task_title" vence en 3 días',
                    'data' => [
                        'task_title' => $task->title,
                        'project_title' => $task->project->title,
                        'due_date' => $task->due_date->format('d/m/Y'),
                        'days_remaining' => 3,
                    ],
                    'related_type' => Task::class,
                    'related_id' => $task->id,
                    'action_url' => route('tasks.show', $task),
                ]);
                
                $this->info("- Notificada tarea de alta prioridad '{$task->title}'");
            }
        }
    }

    /**
     * Enviar resumen semanal (los lunes)
     */
    protected function sendWeeklySummary()
    {
        $this->info('Enviando resumen semanal...');
        
        $users = User::all();
        
        foreach ($users as $user) {
            // Estadísticas de la semana pasada
            $lastWeekStart = now()->subWeek()->startOfWeek();
            $lastWeekEnd = now()->subWeek()->endOfWeek();
            
            $stats = [
                'tasks_completed' => $user->assignedTasks()
                    ->where('status', 'done')
                    ->whereBetween('updated_at', [$lastWeekStart, $lastWeekEnd])
                    ->count(),
                
                'tasks_created' => $user->createdTasks()
                    ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
                    ->count(),
                
                'tasks_pending' => $user->assignedTasks()
                    ->where('status', '!=', 'done')
                    ->count(),
                
                'upcoming_deadlines' => $user->assignedTasks()
                    ->where('status', '!=', 'done')
                    ->whereBetween('due_date', [now(), now()->addWeek()])
                    ->count(),
            ];
            
            // Solo enviar si hay actividad
            if ($stats['tasks_completed'] > 0 || $stats['tasks_pending'] > 0) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'weekly_summary',
                    'title' => 'Resumen semanal de MoodlePro',
                    'message' => 'Completaste :completed tareas la semana pasada. Tienes :pending tareas pendientes y :upcoming con fecha próxima.',
                    'data' => [
                        'completed' => $stats['tasks_completed'],
                        'created' => $stats['tasks_created'],
                        'pending' => $stats['tasks_pending'],
                        'upcoming' => $stats['upcoming_deadlines'],
                        'week_start' => $lastWeekStart->format('d/m'),
                        'week_end' => $lastWeekEnd->format('d/m'),
                    ],
                    'action_url' => route('dashboard'),
                ]);
                
                $this->info("- Resumen semanal enviado a {$user->name}");
            }
        }
    }

    /**
     * Crear notificación de deadline de proyecto
     */
    protected function createProjectDeadlineNotification($user, $project, $daysRemaining)
    {
        // Verificar si ya se envió esta notificación hoy
        $existingNotification = Notification::where('user_id', $user->id)
            ->where('related_type', Project::class)
            ->where('related_id', $project->id)
            ->where('type', Notification::TYPE_PROJECT_DEADLINE)
            ->whereDate('created_at', today())
            ->exists();
        
        if (!$existingNotification) {
            $user->notifyProjectDeadline($project, $daysRemaining);
        }
    }
}