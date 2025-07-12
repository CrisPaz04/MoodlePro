<?php

namespace App\Traits;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasNotifications
{
    /**
     * Obtener todas las notificaciones personalizadas del usuario
     */
    public function customNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->latest();
    }

    /**
     * Obtener notificaciones no leídas personalizadas
     */
    public function customUnreadNotifications(): HasMany
    {
        return $this->customNotifications()->unread();
    }

    /**
     * Obtener notificaciones leídas personalizadas
     */
    public function customReadNotifications(): HasMany
    {
        return $this->customNotifications()->read();
    }

    /**
     * Obtener el conteo de notificaciones no leídas personalizadas
     */
    public function getCustomUnreadNotificationsCountAttribute(): int
    {
        return $this->customUnreadNotifications()->count();
    }

    /**
     * Verificar si tiene notificaciones no leídas personalizadas
     */
    public function hasCustomUnreadNotifications(): bool
    {
        return $this->customUnreadNotifications()->exists();
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllCustomNotificationsAsRead(): int
    {
        return $this->customUnreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Marcar notificaciones específicas como leídas
     */
    public function markCustomNotificationsAsRead(array $notificationIds): int
    {
        return $this->customNotifications()
            ->whereIn('id', $notificationIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Eliminar todas las notificaciones leídas
     */
    public function deleteCustomReadNotifications(): int
    {
        return $this->customReadNotifications()->delete();
    }

    /**
     * Eliminar notificaciones antiguas (más de X días)
     */
    public function deleteOldCustomNotifications(int $days = 30): int
    {
        return $this->customNotifications()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * Notificar al usuario sobre una tarea asignada
     */
    public function sendTaskAssignedNotification($task): Notification
    {
        return Notification::taskAssigned($this, $task);
    }

    /**
     * Notificar al usuario sobre un proyecto próximo a vencer
     */
    public function sendProjectDeadlineNotification($project, $daysRemaining): Notification
    {
        return Notification::projectDeadlineApproaching($this, $project, $daysRemaining);
    }

    /**
     * Notificar al usuario sobre un nuevo miembro en el proyecto
     */
    public function sendProjectMemberAddedNotification($project, $newMember): Notification
    {
        return Notification::projectMemberAdded($this, $project, $newMember);
    }

    /**
     * Notificar al usuario sobre un mensaje recibido
     */
    public function sendMessageReceivedNotification($message): Notification
    {
        return Notification::messageReceived($this, $message);
    }

    /**
     * Enviar una notificación genérica
     */
    public function sendCustomNotification($title, $message, $type = 'info', $actionUrl = null): Notification
    {
        return Notification::generic($this, $title, $message, $type, $actionUrl);
    }

    /**
     * Obtener notificaciones agrupadas por fecha
     */
    public function getCustomNotificationsGroupedByDate()
    {
        return $this->customNotifications()
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
    public function getCustomNotificationsPaginated($perPage = 20)
    {
        return $this->customNotifications()->paginate($perPage);
    }

    /**
     * Obtener notificaciones por tipo
     */
    public function getCustomNotificationsByType($type)
    {
        return $this->customNotifications()->ofType($type)->get();
    }

    /**
     * Configurar preferencias de notificación (para futuro)
     */
    public function customNotificationPreferences()
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
    public function wantsCustomNotification($type): bool
    {
        $preferences = $this->customNotificationPreferences();

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
    public function getCustomNotificationsSummary()
    {
        $unreadCount = $this->customUnreadNotifications()->count();
        $todayCount = $this->customNotifications()
            ->whereDate('created_at', today())
            ->count();

        $recentNotifications = $this->customNotifications()
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
