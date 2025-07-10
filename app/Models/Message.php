<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'content',
        'reply_to',
        'is_edited',
        'edited_at'
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    // Un mensaje pertenece a un proyecto
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Un mensaje pertenece a un usuario
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Un mensaje puede ser respuesta a otro mensaje
    public function parentMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to');
    }

    // Un mensaje puede tener muchas respuestas
    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_to');
    }

    // Scope para mensajes principales (no respuestas)
    public function scopeMain($query)
    {
        return $query->whereNull('reply_to');
    }

    // Scope para ordenar por fecha
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }
}