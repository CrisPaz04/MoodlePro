<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Relaciones para MoodlePro
public function createdProjects()
{
    return $this->hasMany(Project::class, 'creator_id');
}

public function projects()
{
    return $this->belongsToMany(Project::class, 'project_members')
                ->withPivot('role', 'joined_at')
                ->withTimestamps();
}

public function assignedTasks()
{
    return $this->hasMany(Task::class, 'assigned_to');
}

public function createdTasks()
{
    return $this->hasMany(Task::class, 'created_by');
}

public function messages()
{
    return $this->hasMany(Message::class);
}

public function uploadedResources()
{
    return $this->hasMany(Resource::class, 'uploaded_by');
}

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
