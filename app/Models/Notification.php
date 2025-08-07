<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'related_type',
        'related_id',
        'read_at',
        'action_url',
        'notifiable_type',
        'notifiable_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Tipos de notificaciones disponibles
     */
    const TYPE_TASK_ASSIGNED = 'task_assigned';
    const TYPE_TASK_COMPLETED = 'task_completed';
    const TYPE_TASK_OVERDUE = 'task_overdue';
    const TYPE_PROJECT_INVITATION = 'project_invitation';
    const TYPE_PROJECT_DEADLINE = 'project_deadline';
    const TYPE_PROJECT_MEMBER_ADDED = 'project_member_added';
    const TYPE_PROJECT_MEMBER_REMOVED = 'project_member_removed';
    const TYPE_MESSAGE_RECEIVED = 'message_received';
    const TYPE_RESOURCE_SHARED = 'resource_shared';
    const TYPE_COMMENT_ADDED = 'comment_added';
    
    // NUEVOS TIPOS PARA EL SISTEMA DE MIEMBROS
    const TYPE_MEMBER_ADDED_TO_PROJECT = 'member_added_to_project';
    const TYPE_MEMBER_ADDED_NOTIFICATION = 'member_added_notification';

    /**
     * Iconos para cada tipo de notificación
     */
    const TYPE_ICONS = [
        self::TYPE_TASK_ASSIGNED => 'fas fa-tasks',
        self::TYPE_TASK_COMPLETED => 'fas fa-check-circle',
        self::TYPE_TASK_OVERDUE => 'fas fa-exclamation-triangle',
        self::TYPE_PROJECT_INVITATION => 'fas fa-envelope',
        self::TYPE_PROJECT_DEADLINE => 'fas fa-clock',
        self::TYPE_PROJECT_MEMBER_ADDED => 'fas fa-user-plus',
        self::TYPE_PROJECT_MEMBER_REMOVED => 'fas fa-user-minus',
        self::TYPE_MESSAGE_RECEIVED => 'fas fa-comment',
        self::TYPE_RESOURCE_SHARED => 'fas fa-file-alt',
        self::TYPE_COMMENT_ADDED => 'fas fa-comment-dots',
        // NUEVOS ICONOS
        self::TYPE_MEMBER_ADDED_TO_PROJECT => 'fas fa-user-plus',
        self::TYPE_MEMBER_ADDED_NOTIFICATION => 'fas fa-users',
    ];

    /**
     * Colores para cada tipo de notificación
     */
    const TYPE_COLORS = [
        self::TYPE_TASK_ASSIGNED => 'primary',
        self::TYPE_TASK_COMPLETED => 'success',
        self::TYPE_TASK_OVERDUE => 'danger',
        self::TYPE_PROJECT_INVITATION => 'info',
        self::TYPE_PROJECT_DEADLINE => 'warning',
        self::TYPE_PROJECT_MEMBER_ADDED => 'success',
        self::TYPE_PROJECT_MEMBER_REMOVED => 'secondary',
        self::TYPE_MESSAGE_RECEIVED => 'info',
        self::TYPE_RESOURCE_SHARED => 'primary',
        self::TYPE_COMMENT_ADDED => 'secondary',
        // NUEVOS COLORES
        self::TYPE_MEMBER_ADDED_TO_PROJECT => 'success',
        self::TYPE_MEMBER_ADDED_NOTIFICATION => 'info',
    ];

    /**
     * Obtener el usuario al que pertenece la notificación
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener el modelo relacionado polimórficamente
     * CORREGIDO: morphTo() sin parámetros usa las convenciones de Laravel
     */
    public function related()
    {
        return $this->morphTo();
    }

    /**
     * Scope para notificaciones no leídas
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope para notificaciones leídas
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope para notificaciones recientes (últimos 7 días)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(7));
    }

    /**
     * Scope para notificaciones por tipo
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Marcar la notificación como leída
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Marcar la notificación como no leída
     */
    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Verificar si la notificación está leída
     */
    public function isRead()
    {
        return !is_null($this->read_at);
    }

    /**
     * Verificar si la notificación está sin leer
     */
    public function isUnread()
    {
        return is_null($this->read_at);
    }

    /**
     * Obtener el icono de la notificación
     */
    public function getIconAttribute()
    {
        return self::TYPE_ICONS[$this->type] ?? 'fas fa-bell';
    }

    /**
     * Obtener el color de la notificación
     */
    public function getColorAttribute()
    {
        return self::TYPE_COLORS[$this->type] ?? 'secondary';
    }

    /**
     * Obtener el texto formateado de la notificación
     */
    public function getFormattedMessageAttribute()
    {
        $data = $this->data ?? [];
        $message = $this->message;

        // Reemplazar placeholders en el mensaje
        foreach ($data as $key => $value) {
            $message = str_replace(":{$key}", $value, $message);
        }

        return $message;
    }

    /**
     * Obtener el tiempo relativo de la notificación
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Crear notificación para tarea asignada
     */
    public static function taskAssigned(User $user, Task $task)
    {
        return self::create([
            'user_id' => $user->id,
            'type' => self::TYPE_TASK_ASSIGNED,
            'title' => 'Nueva tarea asignada',
            'message' => 'Se te ha asignado la tarea ":task_title" en el proyecto :project_title',
            'data' => [
                'task_title' => $task->title,
                'project_title' => $task->project->title,
                'task_id' => $task->id,
                'project_id' => $task->project_id,
            ],
            'related_type' => Task::class,
            'related_id' => $task->id,
            'action_url' => route('tasks.show', $task),
        ]);
    }

    /**
     * Crear notificación para proyecto próximo a vencer
     */
    public static function projectDeadlineApproaching(User $user, Project $project, $daysRemaining)
    {
        return self::create([
            'user_id' => $user->id,
            'type' => self::TYPE_PROJECT_DEADLINE,
            'title' => 'Proyecto próximo a vencer',
            'message' => 'El proyecto ":project_title" vence en :days días',
            'data' => [
                'project_title' => $project->title,
                'days' => $daysRemaining,
                'deadline' => $project->deadline->format('d/m/Y'),
                'project_id' => $project->id,
            ],
            'related_type' => Project::class,
            'related_id' => $project->id,
            'action_url' => route('projects.show', $project),
        ]);
    }

    /**
     * Crear notificación para nuevo miembro en proyecto
     */
    public static function projectMemberAdded(User $user, Project $project, User $newMember)
    {
        return self::create([
            'user_id' => $user->id,
            'type' => self::TYPE_PROJECT_MEMBER_ADDED,
            'title' => 'Nuevo miembro en proyecto',
            'message' => ':member_name se ha unido al proyecto ":project_title"',
            'data' => [
                'member_name' => $newMember->name,
                'member_id' => $newMember->id,
                'project_title' => $project->title,
                'project_id' => $project->id,
            ],
            'related_type' => Project::class,
            'related_id' => $project->id,
            'action_url' => route('projects.show', $project),
        ]);
    }

    /**
     * NUEVO: Crear notificación cuando te agregan a un proyecto
     */
    public static function memberAddedToProject(User $user, Project $project, User $addedBy)
    {
        return self::create([
            'user_id' => $user->id,
            'type' => self::TYPE_MEMBER_ADDED_TO_PROJECT,
            'title' => 'Te agregaron a un proyecto',
            'message' => 'Te han agregado al proyecto ":project_title". ¡Bienvenido al equipo!',
            'data' => [
                'project_title' => $project->title,
                'project_id' => $project->id,
                'added_by' => $addedBy->name,
                'added_by_id' => $addedBy->id,
            ],
            'related_type' => Project::class,
            'related_id' => $project->id,
            'action_url' => route('projects.show', $project->id),
        ]);
    }

    /**
     * Crear notificación para mensaje recibido
     */
    public static function messageReceived(User $user, Message $message)
    {
        return self::create([
            'user_id' => $user->id,
            'type' => self::TYPE_MESSAGE_RECEIVED,
            'title' => 'Nuevo mensaje',
            'message' => ':sender_name envió un mensaje en ":project_title"',
            'data' => [
                'sender_name' => $message->user->name,
                'sender_id' => $message->user_id,
                'project_title' => $message->project->title,
                'project_id' => $message->project_id,
                'message_preview' => Str::limit($message->content, 50),
            ],
            'related_type' => Message::class,
            'related_id' => $message->id,
            'action_url' => route('projects.chat', $message->project),
        ]);
    }

    /**
     * Crear notificación genérica
     */
    public static function generic(User $user, $title, $message, $type = 'info', $actionUrl = null)
    {
        return self::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
        ]);
    }
}