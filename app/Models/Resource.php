<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'file_url',
        'file_name',
        'file_type',
        'file_size',
        'category',
        'icon',
        'uploaded_by',
        'project_id',
        'downloads_count',
        'rating',
        'ratings_count'
    ];

    protected $casts = [
        'downloads_count' => 'integer',
        'rating' => 'float',
        'ratings_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who uploaded the resource
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the project this resource belongs to
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute()
    {
        $labels = [
            'document' => 'Documento',
            'presentation' => 'Presentación',
            'video' => 'Video',
            'code' => 'Código',
            'other' => 'Otro'
        ];

        return $labels[$this->category] ?? ucfirst($this->category);
    }

    /**
     * Get formatted rating
     */
    public function getFormattedRatingAttribute()
    {
        return number_format($this->rating, 1);
    }

    /**
     * Check if resource is an image
     */
    public function isImage()
    {
        return in_array($this->file_type, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
    }

    /**
     * Check if resource is a video
     */
    public function isVideo()
    {
        return in_array($this->file_type, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm']);
    }

    /**
     * Check if resource is audio
     */
    public function isAudio()
    {
        return in_array($this->file_type, ['mp3', 'wav', 'ogg', 'flac', 'm4a']);
    }

    /**
     * Check if resource is a document
     */
    public function isDocument()
    {
        return in_array($this->file_type, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt']);
    }

    /**
     * Check if resource is code
     */
    public function isCode()
    {
        return in_array($this->file_type, ['html', 'css', 'js', 'php', 'py', 'java', 'cpp', 'c', 'rb', 'go']);
    }

    /**
     * Get the file URL (from S3 or fallback to local)
     */
    public function getFileUrlAttribute()
    {
        // Si ya tenemos una URL de S3, usarla
        if ($this->attributes['file_url'] ?? null) {
            return $this->attributes['file_url'];
        }

        // Fallback para archivos antiguos en storage local
        if ($this->file_path) {
            return Storage::url($this->file_path);
        }

        return null;
    }

    /**
     * Delete the file from storage when the model is deleted
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($resource) {
            // Intentar eliminar de S3
            try {
                if ($resource->file_path) {
                    Storage::disk('moodlepro')->delete($resource->file_path);
                }
            } catch (\Exception $e) {
                \Log::error('Error al eliminar archivo de S3: ' . $e->getMessage());
            }
        });
    }
}