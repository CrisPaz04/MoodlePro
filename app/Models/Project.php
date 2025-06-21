<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'deadline',
        'status',
        'creator_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
    ];

    // Un proyecto pertenece a un creador (usuario)
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    // Un proyecto tiene muchas tareas
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // Un proyecto tiene muchos mensajes
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    // Un proyecto tiene muchos miembros a través de project_members
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    // Scope para proyectos activos
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}