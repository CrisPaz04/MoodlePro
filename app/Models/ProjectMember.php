<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'role',
        'joined_at'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    // Un miembro pertenece a un proyecto
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Un miembro es un usuario
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes para filtrar por rol
    public function scopeCoordinators($query)
    {
        return $query->where('role', 'coordinator');
    }

    public function scopeMembers($query)
    {
        return $query->where('role', 'member');
    }

    public function scopeViewers($query)
    {
        return $query->where('role', 'viewer');
    }

    // Método para verificar si es coordinador
    public function isCoordinator()
    {
        return $this->role === 'coordinator';
    }

    // Método para verificar si puede editar
    public function canEdit()
    {
        return in_array($this->role, ['coordinator', 'member']);
    }

    // Método para verificar si solo puede ver
    public function isViewerOnly()
    {
        return $this->role === 'viewer';
    }
}