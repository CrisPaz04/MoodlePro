<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Resource;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $users = User::all();
        $projects = Project::all();
        $tasks = Task::all();
        $resources = Resource::all();
        
        if ($users->count() == 0) {
            $this->command->error('❌ Se necesitan usuarios. Ejecuta UserSeeder primero.');
            return;
        }

        // Crear notificaciones para cada usuario
        foreach ($users->take(10) as $user) { // Solo primeros 10 usuarios
            $this->createNotificationsForUser($user, $projects, $tasks, $resources);
        }

        $this->command->info('✅ Notificaciones creadas exitosamente');
    }

    /**
     * Crear notificaciones para un usuario específico
     */
    private function createNotificationsForUser(User $user, $projects, $tasks, $resources)
    {
        $notificationCount = rand(8, 15);
        
        for ($i = 0; $i < $notificationCount; $i++) {
            $this->createRandomNotification($user, $projects, $tasks, $resources);
        }

        $this->command->info("✅ Notificaciones creadas para: {$user->name}");
    }

    /**
     * Crear una notificación aleatoria
     */
    private function createRandomNotification(User $user, $projects, $tasks, $resources)
    {
        $types = [
            'task_assigned',
            'task_completed', 
            'task_overdue',
            'project_invitation',
            'project_deadline',
            'project_member_added',
            'message_received',
            'resource_shared'
        ];

        $type = $types[array_rand($types)];
        
        switch ($type) {
            case 'task_assigned':
                $this->createTaskAssignedNotification($user, $tasks);
                break;
            case 'task_completed':
                $this->createTaskCompletedNotification($user, $tasks);
                break;
            case 'task_overdue':
                $this->createTaskOverdueNotification($user, $tasks);
                break;
            case 'project_invitation':
                $this->createProjectInvitationNotification($user, $projects);
                break;
            case 'project_deadline':
                $this->createProjectDeadlineNotification($user, $projects);
                break;
            case 'project_member_added':
                $this->createMemberAddedNotification($user, $projects);
                break;
            case 'message_received':
                $this->createMessageNotification($user, $projects);
                break;
            case 'resource_shared':
                $this->createResourceNotification($user, $resources);
                break;
        }
    }

    /**
     * Notificación de tarea asignada
     */
    private function createTaskAssignedNotification(User $user, $tasks)
    {
        $task = $tasks->random();
        
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'user_id' => $user->id,
            'type' => 'task_assigned',
            'title' => 'Nueva tarea asignada',
            'message' => "Se te ha asignado la tarea '{$task->title}' en el proyecto {$task->project->title}",
            'data' => [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'project_id' => $task->project_id,
                'project_title' => $task->project->title,
            ],
            'related_type' => Task::class,
            'related_id' => $task->id,
            'action_url' => "/projects/{$task->project_id}",
            'read_at' => rand(0, 3) == 0 ? null : Carbon::now()->subDays(rand(0, 5)), // 25% sin leer
            'created_at' => Carbon::now()->subDays(rand(0, 10))
        ]);
    }

    /**
     * Notificación de tarea completada
     */
    private function createTaskCompletedNotification(User $user, $tasks)
    {
        $task = $tasks->where('status', 'done')->random();
        
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'user_id' => $user->id,
            'type' => 'task_completed',
            'title' => 'Tarea completada',
            'message' => "La tarea '{$task->title}' ha sido marcada como completada",
            'data' => [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'project_id' => $task->project_id,
                'project_title' => $task->project->title,
            ],
            'related_type' => Task::class,
            'related_id' => $task->id,
            'action_url' => "/projects/{$task->project_id}",
            'read_at' => rand(0, 4) == 0 ? null : Carbon::now()->subDays(rand(0, 3)),
            'created_at' => Carbon::now()->subDays(rand(0, 7))
        ]);
    }

    /**
     * Notificación de tarea vencida
     */
    private function createTaskOverdueNotification(User $user, $tasks)
    {
        $task = $tasks->random();
        
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'user_id' => $user->id,
            'type' => 'task_overdue',
            'title' => 'Tarea vencida',
            'message' => "La tarea '{$task->title}' ha vencido y requiere atención",
            'data' => [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'project_id' => $task->project_id,
                'project_title' => $task->project->title,
                'due_date' => $task->due_date?->format('d/m/Y'),
            ],
            'related_type' => Task::class,
            'related_id' => $task->id,
            'action_url' => "/projects/{$task->project_id}",
            'read_at' => rand(0, 2) == 0 ? null : Carbon::now()->subDays(rand(0, 2)), // 33% sin leer
            'created_at' => Carbon::now()->subDays(rand(0, 5))
        ]);
    }

    /**
     * Notificación de invitación a proyecto
     */
    private function createProjectInvitationNotification(User $user, $projects)
    {
        $project = $projects->random();
        $inviter = User::where('id', '!=', $user->id)->inRandomOrder()->first();
        
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'user_id' => $user->id,
            'type' => 'project_invitation',
            'title' => 'Invitación a proyecto',
            'message' => "{$inviter->name} te ha invitado al proyecto '{$project->title}'",
            'data' => [
                'project_id' => $project->id,
                'project_title' => $project->title,
                'inviter_id' => $inviter->id,
                'inviter_name' => $inviter->name,
            ],
            'related_type' => Project::class,
            'related_id' => $project->id,
            'action_url' => "/projects/{$project->id}",
            'read_at' => rand(0, 3) == 0 ? null : Carbon::now()->subDays(rand(0, 4)),
            'created_at' => Carbon::now()->subDays(rand(0, 8))
        ]);
    }

    /**
     * Notificación de deadline próximo
     */
    private function createProjectDeadlineNotification(User $user, $projects)
    {
        $project = $projects->random();
        $daysRemaining = rand(1, 7);
        
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'user_id' => $user->id,
            'type' => 'project_deadline',
            'title' => 'Deadline próximo',
            'message' => "El proyecto '{$project->title}' vence en {$daysRemaining} días",
            'data' => [
                'project_id' => $project->id,
                'project_title' => $project->title,
                'days_remaining' => $daysRemaining,
                'deadline' => $project->deadline->format('d/m/Y'),
            ],
            'related_type' => Project::class,
            'related_id' => $project->id,
            'action_url' => "/projects/{$project->id}",
            'read_at' => rand(0, 2) == 0 ? null : Carbon::now()->subDays(rand(0, 1)), // 33% sin leer
            'created_at' => Carbon::now()->subDays(rand(0, 3))
        ]);
    }

    /**
     * Notificación de nuevo miembro
     */
    private function createMemberAddedNotification(User $user, $projects)
    {
        $project = $projects->random();
        $newMember = User::where('id', '!=', $user->id)->inRandomOrder()->first();
        
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'user_id' => $user->id,
            'type' => 'project_member_added',
            'title' => 'Nuevo miembro en proyecto',
            'message' => "{$newMember->name} se ha unido al proyecto '{$project->title}'",
            'data' => [
                'project_id' => $project->id,
                'project_title' => $project->title,
                'member_id' => $newMember->id,
                'member_name' => $newMember->name,
            ],
            'related_type' => Project::class,
            'related_id' => $project->id,
            'action_url' => "/projects/{$project->id}",
            'read_at' => rand(0, 4) == 0 ? null : Carbon::now()->subDays(rand(0, 6)),
            'created_at' => Carbon::now()->subDays(rand(0, 12))
        ]);
    }

    /**
     * Notificación de mensaje recibido
     */
    private function createMessageNotification(User $user, $projects)
    {
        $project = $projects->random();
        $sender = User::where('id', '!=', $user->id)->inRandomOrder()->first();
        
        $messages = [
            'Hola equipo, ¿cómo va el avance?',
            'Necesito ayuda con esta funcionalidad',
            'Revisé el código y se ve bien',
            'Tengamos una reunión mañana',
            'Subí la documentación actualizada',
            'El testing está casi listo',
            'Excelente trabajo en el diseño'
        ];
        
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'user_id' => $user->id,
            'type' => 'message_received',
            'title' => 'Nuevo mensaje',
            'message' => "{$sender->name} envió un mensaje en '{$project->title}'",
            'data' => [
                'project_id' => $project->id,
                'project_title' => $project->title,
                'sender_id' => $sender->id,
                'sender_name' => $sender->name,
                'message_preview' => $messages[array_rand($messages)],
            ],
            'related_type' => Project::class,
            'related_id' => $project->id,
            'action_url' => "/projects/{$project->id}/chat",
            'read_at' => rand(0, 3) == 0 ? null : Carbon::now()->subDays(rand(0, 2)),
            'created_at' => Carbon::now()->subDays(rand(0, 5))
        ]);
    }

    /**
     * Notificación de recurso compartido
     */
    private function createResourceNotification(User $user, $resources)
    {
        if ($resources->count() == 0) return;
        
        $resource = $resources->random();
        $uploader = User::where('id', '!=', $user->id)->inRandomOrder()->first();
        
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'user_id' => $user->id,
            'type' => 'resource_shared',
            'title' => 'Recurso compartido',
            'message' => "{$uploader->name} compartió el recurso '{$resource->title}'",
            'data' => [
                'resource_id' => $resource->id,
                'resource_title' => $resource->title,
                'uploader_id' => $uploader->id,
                'uploader_name' => $uploader->name,
                'category' => $resource->category,
            ],
            'related_type' => Resource::class,
            'related_id' => $resource->id,
            'action_url' => "/resources/{$resource->id}",
            'read_at' => rand(0, 4) == 0 ? null : Carbon::now()->subDays(rand(0, 7)),
            'created_at' => Carbon::now()->subDays(rand(0, 14))
        ]);
    }
}