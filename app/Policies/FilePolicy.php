<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, File $file): bool
    {
        // If file has no project, everyone can view it
        if (!$file->project_id) {
            return true;
        }

        // If file belongs to a project, check if user is member
        return $file->project->user_id === $user->id ||
               $file->project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, File $file): bool
    {
        // Only the owner can update
        return $user->id === $file->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, File $file): bool
    {
        // Owner can delete
        if ($user->id === $file->user_id) {
            return true;
        }

        // Project owner can delete files in their project
        if ($file->project_id && $file->project->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, File $file): bool
    {
        return $user->id === $file->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, File $file): bool
    {
        return $user->id === $file->user_id;
    }

    /**
     * Determine whether the user can download the file.
     */
    public function download(User $user, File $file): bool
    {
        return $this->view($user, $file);
    }
}