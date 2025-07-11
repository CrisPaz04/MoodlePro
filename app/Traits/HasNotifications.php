<?php

namespace App\Traits;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasNotifications
{
    /**
     * Obtener todas las notificaciones del usuario
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->latest();
    }

    /**
     * Obtener notificaciones no leídas
     */
    public function unreadNotifications(): HasMany
    {
        return $this->notifications()->unread();
    }

    /**
     * Obtener notificaciones leídas
     */
    public function readNotifications(): HasMany
    {
        return $this->notifications()->read();
    }

    /**
     * Obtener el conteo de notificaciones no leídas
     */
    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Verificar si tiene notificaciones no leídas
     */
    public function hasUnreadNotifications(): bool
    {
        return $this->unreadNotifications()->exists();
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllNotificationsAsRead(): int
    {
        return $this->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Marcar notificaciones específicas como leídas
     */
    public function markNotificationsAsRead(array $notificationIds): int
    {
        return $this->notifications()
            ->whereIn('id', $notificationIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Eliminar todas las notificaciones leídas
     */
    public function deleteReadNotifications(): int
    {
        return $this->readNotifications()->delete();
    }

    /**
     * Eliminar notificaciones antiguas (más de X días)
     */
    public function deleteOldNotifications(int $days = 30): int
    {
        return $this->notifications()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * Notificar al usuario sobre una tarea asignada
     */
    public function notifyTaskAssigned($task): Notification
    {
        return Notification::taskAssigned($this, $task);
    }

    /**
     * Notificar al usuario sobre un proyecto próximo a vencer
     */
    public function notifyProjectDeadline($project, $daysRemaining): Notification
    {
        return Notification::projectDeadlineApproaching($this, $project, $daysRemaining);
    }

    /**
     * Notificar al usuario sobre un nuevo miembro en el proyecto
     */
    public function notifyProjectMemberAdded($project, $newMember): Notification
    {
        return Notification::projectMemberAdded($this, $project, $newMember);
    }

    /**
     * Notificar al usuario sobre un mensaje recibido
     */
    public function notifyMessageReceived($message): Notification
    {
        return Notification::messageReceived($this, $message);
    }

    /**
     * Enviar una notificación genérica
     */
    public function notify($title, $message, $type = 'info', $actionUrl = null): Notification
    {
        return Notification::generic($this, $title, $message, $type, $actionUrl);
    }

    /**
     * Obtener notificaciones agrupadas por fecha
     */
    public function getNotificationsGroupedByDate()
    {
        return $this->notifications()
            ->latest()
            ->get()
            ->groupBy(function ($notification) {
                $date = $notification->created_at;
                
                if ($date->isToday()) {
                    return 'Hoy';
                } elseif ($date->isYesterday()) {
                    return 'Ayer';
                } elseif ($date->isCurrentWeek()) {
                    return 'Esta semana';
                } elseif ($date->isCurrentMonth()) {
                    return 'Este mes';
                } else {
                    return $date->format('F Y');
                }
            });
    }

    /**
     * Obtener notificaciones paginadas
     */
    public function getNotificationsPaginated($perPage = 20)
    {
        return $this->notifications()->paginate($perPage);
    }

    /**
     * Obtener notificaciones por tipo
     */
    public function getNotificationsByType($type)
    {
        return $this->notifications()->ofType($type)->get();
    }

    /**
     * Configurar preferencias de notificación (para futuro)
     */
    public function notificationPreferences()
    {
        return [
            'email_notifications' => $this->email_notifications ?? true,
            'push_notifications' => $this->push_notifications ?? true,
            'task_notifications' => $this->task_notifications ?? true,
            'project_notifications' => $this->project_notifications ?? true,
            'message_notifications' => $this->message_notifications ?? true,
        ];
    }

    /**
     * Verificar si el usuario quiere recibir un tipo específico de notificación
     */
    public function wantsNotification($type): bool
    {
        $preferences = $this->notificationPreferences();
        
        // Mapear tipos de notificación a preferencias
        $typeToPreference = [
            Notification::TYPE_TASK_ASSIGNED => 'task_notifications',
            Notification::TYPE_TASK_COMPLETED => 'task_notifications',
            Notification::TYPE_TASK_OVERDUE => 'task_notifications',
            Notification::TYPE_PROJECT_INVITATION => 'project_notifications',
            Notification::TYPE_PROJECT_DEADLINE => 'project_notifications',
            Notification::TYPE_PROJECT_MEMBER_ADDED => 'project_notifications',
            Notification::TYPE_PROJECT_MEMBER_REMOVED => 'project_notifications',
            Notification::TYPE_MESSAGE_RECEIVED => 'message_notifications',
        ];

        $preference = $typeToPreference[$type] ?? 'email_notifications';
        
        return $preferences[$preference] ?? true;
    }

    /**
     * Obtener resumen de notificaciones para el dashboard
     */
    public function getNotificationsSummary()
    {
        $unreadCount = $this->unreadNotifications()->count();
        $todayCount = $this->notifications()
            ->whereDate('created_at', today())
            ->count();
        
        $recentNotifications = $this->notifications()
            ->with('related')
            ->limit(5)
            ->get();

        return [
            'unread_count' => $unreadCount,
            'today_count' => $todayCount,
            'recent' => $recentNotifications,
            'has_unread' => $unreadCount > 0,
        ];
    }
}