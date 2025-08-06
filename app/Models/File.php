<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'original_name',
        'mime_type',
        'size',
        'path',
        'url',
        'disk',
        'user_id',
        'project_id',
        'description',
        'downloads',
        'rating',
        'total_ratings'
    ];

    protected $casts = [
        'size' => 'integer',
        'downloads' => 'integer',
        'rating' => 'float',
        'total_ratings' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who uploaded the file
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project this file belongs to
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the file size formatted for humans
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the file icon based on mime type
     */
    public function getIconAttribute()
    {
        $type = explode('/', $this->mime_type)[0];
        
        $icons = [
            'image' => 'bi-file-image',
            'video' => 'bi-file-play',
            'audio' => 'bi-file-music',
            'application/pdf' => 'bi-file-pdf',
            'application/msword' => 'bi-file-word',
            'application/vnd.ms-excel' => 'bi-file-excel',
            'application/vnd.ms-powerpoint' => 'bi-file-ppt',
            'application/zip' => 'bi-file-zip',
            'text' => 'bi-file-text',
        ];
        
        if (isset($icons[$this->mime_type])) {
            return $icons[$this->mime_type];
        }
        
        if (isset($icons[$type])) {
            return $icons[$type];
        }
        
        return 'bi-file-earmark';
    }

    /**
     * Delete the file from storage when the model is deleted
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($file) {
            Storage::disk($file->disk)->delete($file->path);
        });
    }

    /**
     * Check if file is an image
     */
    public function isImage()
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if file is a video
     */
    public function isVideo()
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Check if file is audio
     */
    public function isAudio()
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    /**
     * Check if file is a document
     */
    public function isDocument()
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
        ];
        
        return in_array($this->mime_type, $documentTypes);
    }

    /**
     * Increment download count
     */
    public function incrementDownloads()
    {
        $this->increment('downloads');
    }

    /**
     * Add rating to file
     */
    public function addRating($rating)
    {
        $newTotal = $this->total_ratings + 1;
        $newRating = (($this->rating * $this->total_ratings) + $rating) / $newTotal;
        
        $this->update([
            'rating' => $newRating,
            'total_ratings' => $newTotal
        ]);
    }
}