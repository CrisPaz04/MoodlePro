<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'category',
        'tags',
        'uploaded_by',
        'average_rating',
        'total_ratings',
        'download_count',
        'is_public'
    ];

    protected $casts = [
        'average_rating' => 'decimal:1',
        'is_public' => 'boolean',
    ];

    // Un recurso pertenece a un usuario (quien lo subió)
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes para filtrar recursos
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('download_count', 'desc');
    }

    public function scopeTopRated($query)
    {
        return $query->orderBy('average_rating', 'desc');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Método para obtener URL de descarga
    public function getDownloadUrl()
    {
        return Storage::url($this->file_path);
    }

    // Método para formatear tamaño de archivo
    public function getFormattedSize()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Método para incrementar descargas
    public function incrementDownloads()
    {
        $this->increment('download_count');
    }

    // Método para calcular rating promedio
    public function updateRating($newRating)
    {
        $this->total_ratings++;
        $this->average_rating = (($this->average_rating * ($this->total_ratings - 1)) + $newRating) / $this->total_ratings;
        $this->save();
    }
}