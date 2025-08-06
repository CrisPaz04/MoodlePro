<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Project;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ResourceController extends Controller
{
    /**
     * Mostrar lista de recursos
     */
    public function index(Request $request)
    {
        $query = Resource::with(['uploader', 'project']);

        // Filtros
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $sort = $request->get('sort', 'recent');
        switch ($sort) {
            case 'popular':
                $query->orderBy('downloads_count', 'desc');
                break;
            case 'rated':
                $query->orderBy('rating', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $resources = $query->paginate(12);

        // Estadísticas
        $stats = [
            'total' => Resource::count(),
            'downloads' => Resource::sum('downloads_count'),
            'rating' => round(Resource::avg('rating') ?? 0, 1)
        ];

        // Proyectos del usuario para el modal de subida
        $projects = Auth::user()->projects()->get();

        return view('resources.index', compact('resources', 'stats', 'projects'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $projects = Auth::user()->projects()->get();
        return view('resources.create', compact('projects'));
    }

    /**
     * Guardar nuevo recurso
     */
    public function store(Request $request)
    {
        \Log::info('=== INICIO SUBIDA DE RECURSO ===');
        \Log::info('Datos recibidos:', $request->all());
        \Log::info('Tiene archivo: ' . ($request->hasFile('file') ? 'SI' : 'NO'));
        
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'file' => 'required|file|max:51200', // 50MB máximo
                'category' => 'required|in:document,presentation,video,code,other',
                'project_id' => 'nullable|exists:projects,id'
            ]);

            \Log::info('Validación pasada');

            // Verificar que el usuario tenga acceso al proyecto si se especifica
            if ($request->project_id) {
                $project = Project::findOrFail($request->project_id);
                if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
                    abort(403, 'No tienes acceso a este proyecto');
                }
            }

            // Subir archivo a AWS S3
            $file = $request->file('file');
            \Log::info('Archivo recibido', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType()
            ]);
            
            // Generar nombre único para el archivo
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            
            // Definir la ruta en S3
            $path = 'resources/' . date('Y/m') . '/' . $filename;
            \Log::info('Ruta S3: ' . $path);
            
            // Subir a S3 usando el disco 'moodlepro'
            $disk = Storage::disk('moodlepro');
            try {
                $uploadResult = $disk->put($path, file_get_contents($file), 'public');
                \Log::info('Resultado de subida S3: ' . ($uploadResult ? 'exitoso' : 'falló'));
            } catch (\Exception $s3Error) {
                \Log::error('Error S3: ' . $s3Error->getMessage());
                throw $s3Error;
            }
            
            // Obtener la URL completa del archivo
            $url = $disk->url($path);
            \Log::info('URL generada: ' . $url);

        // Determinar el ícono según el tipo de archivo
        $extension = strtolower($file->getClientOriginalExtension());
        $icon = $this->getFileIcon($extension);

            // Crear recurso
            $resource = Resource::create([
                'title' => $request->title,
                'description' => $request->description,
                'file_path' => $path,
                'file_url' => $url, // Guardar URL de S3
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $extension,
                'file_size' => $this->formatFileSize($file->getSize()),
                'category' => $request->category,
                'icon' => $icon,
                'uploaded_by' => Auth::id(),
                'project_id' => $request->project_id,
                'downloads_count' => 0,
                'rating' => 0,
                'ratings_count' => 0
            ]);

            \Log::info('Recurso creado con ID: ' . $resource->id);

            // Crear notificación para miembros del proyecto si aplica
            if ($resource->project_id) {
                $project = Project::find($resource->project_id);
                $members = $project->members()->where('user_id', '!=', Auth::id())->get();
                
                foreach ($members as $member) {
                    Notification::create([
                        'user_id' => $member->id,
                        'type' => 'resource_shared',
                        'title' => 'Nuevo recurso compartido',
                        'message' => Auth::user()->name . ' ha compartido "' . $resource->title . '" en ' . $project->title,
                        'related_type' => 'resource',
                        'related_id' => $resource->id
                    ]);
                }
            }

            // Si es una petición AJAX, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Recurso subido exitosamente',
                    'redirect' => route('resources.index')
                ]);
            }
            
            return redirect()->route('resources.index')
                ->with('success', 'Recurso subido exitosamente');
                
        } catch (\Exception $e) {
            \Log::error('Error al subir recurso: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al subir el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar recurso específico
     */
    public function show(Resource $resource)
    {
        $resource->load(['uploader', 'project']);
        
        // Recursos relacionados
        $relatedResources = Resource::where('category', $resource->category)
            ->where('id', '!=', $resource->id)
            ->limit(4)
            ->get();

        return view('resources.show', compact('resource', 'relatedResources'));
    }

    /**
     * Descargar recurso
     */
    public function download(Resource $resource)
    {
        // Incrementar contador de descargas
        $resource->increment('downloads_count');

        // Descargar desde S3
        try {
            return Storage::disk('moodlepro')->download($resource->file_path, $resource->file_name);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al descargar el archivo. Por favor, intenta de nuevo.');
        }
    }

    /**
     * Calificar recurso
     */
    public function rate(Request $request, Resource $resource)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5'
        ]);

        // Actualizar calificación
        $currentTotal = $resource->rating * $resource->ratings_count;
        $newTotal = $currentTotal + $request->rating;
        $newCount = $resource->ratings_count + 1;
        $newAverage = $newTotal / $newCount;

        $resource->update([
            'rating' => $newAverage,
            'ratings_count' => $newCount
        ]);

        return response()->json([
            'success' => true,
            'message' => '¡Gracias por tu calificación!',
            'rating' => round($newAverage, 1),
            'count' => $newCount
        ]);
    }

    /**
     * Agregar a favoritos
     */
    public function favorite(Resource $resource)
    {
        // Aquí podrías implementar un sistema de favoritos
        // Por ahora, solo retornamos una respuesta exitosa
        
        return response()->json([
            'success' => true,
            'message' => 'Recurso agregado a favoritos'
        ]);
    }

    /**
     * Eliminar recurso
     */
    public function destroy(Resource $resource)
    {
        // Verificar permisos
        if ($resource->uploaded_by !== Auth::id()) {
            abort(403, 'No tienes permiso para eliminar este recurso');
        }

        // Eliminar archivo de S3
        try {
            Storage::disk('moodlepro')->delete($resource->file_path);
        } catch (\Exception $e) {
            // Log del error pero continuar con la eliminación del registro
            \Log::error('Error al eliminar archivo de S3: ' . $e->getMessage());
        }

        // Eliminar registro de la base de datos
        $resource->delete();

        return redirect()->route('resources.index')
            ->with('success', 'Recurso eliminado exitosamente');
    }

    /**
     * Obtener ícono según extensión de archivo
     */
    private function getFileIcon($extension)
    {
        $icons = [
            // Documentos
            'pdf' => 'file-pdf',
            'doc' => 'file-word',
            'docx' => 'file-word',
            'xls' => 'file-excel',
            'xlsx' => 'file-excel',
            'ppt' => 'file-powerpoint',
            'pptx' => 'file-powerpoint',
            'txt' => 'file-alt',
            
            // Imágenes
            'jpg' => 'file-image',
            'jpeg' => 'file-image',
            'png' => 'file-image',
            'gif' => 'file-image',
            'svg' => 'file-image',
            
            // Videos
            'mp4' => 'file-video',
            'avi' => 'file-video',
            'mov' => 'file-video',
            'wmv' => 'file-video',
            
            // Audio
            'mp3' => 'file-audio',
            'wav' => 'file-audio',
            'ogg' => 'file-audio',
            
            // Código
            'html' => 'file-code',
            'css' => 'file-code',
            'js' => 'file-code',
            'php' => 'file-code',
            'py' => 'file-code',
            'java' => 'file-code',
            'cpp' => 'file-code',
            'c' => 'file-code',
            
            // Archivos comprimidos
            'zip' => 'file-archive',
            'rar' => 'file-archive',
            '7z' => 'file-archive',
            'tar' => 'file-archive',
            'gz' => 'file-archive',
        ];

        return $icons[$extension] ?? 'file';
    }

    /**
     * Formatear tamaño de archivo
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}