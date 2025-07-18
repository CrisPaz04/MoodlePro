<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    /**
     * Constructor - Requiere autenticación
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar lista de recursos
     */
    public function index(Request $request)
    {
        // Query base
        $query = Resource::with('uploader');

        // Filtro por categoría
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort', 'recent');
        switch ($sortBy) {
            case 'popular':
                $query->orderBy('downloads_count', 'desc');
                break;
            case 'rated':
                $query->orderBy('rating', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        // Paginación
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
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:51200', // 50MB máximo
            'category' => 'required|in:document,presentation,video,code,other',
            'project_id' => 'nullable|exists:projects,id'
        ]);

        // Verificar que el usuario tenga acceso al proyecto si se especifica
        if ($request->project_id) {
            $project = Project::findOrFail($request->project_id);
            if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
                abort(403, 'No tienes acceso a este proyecto');
            }
        }

        // Subir archivo
        $file = $request->file('file');
        $path = $file->store('resources/' . date('Y/m'), 'public');

        // Determinar el ícono según el tipo de archivo
        $extension = strtolower($file->getClientOriginalExtension());
        $icon = $this->getFileIcon($extension);

        // Crear recurso
        $resource = Resource::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
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

        return redirect()->route('resources.index')
            ->with('success', 'Recurso subido exitosamente');
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

        // Descargar archivo
        return Storage::disk('public')->download($resource->file_path, $resource->file_name);
    }

    /**
     * Calificar recurso
     */
    public function rate(Request $request, Resource $resource)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5'
        ]);

        // Actualizar rating (simplificado - en producción sería más complejo)
        $newRating = ($resource->rating * $resource->ratings_count + $request->rating) / ($resource->ratings_count + 1);
        
        $resource->update([
            'rating' => $newRating,
            'ratings_count' => $resource->ratings_count + 1
        ]);

        return response()->json([
            'success' => true,
            'new_rating' => round($newRating, 1),
            'ratings_count' => $resource->ratings_count
        ]);
    }

    /**
     * Marcar como favorito
     */
    public function favorite(Resource $resource)
    {
        // Implementación simplificada
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
        // Solo el propietario puede eliminar
        if ($resource->uploaded_by !== Auth::id()) {
            abort(403, 'No tienes permisos para eliminar este recurso');
        }

        // Eliminar archivo físico
        Storage::disk('public')->delete($resource->file_path);
        
        // Eliminar registro
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
            'pdf' => 'file-pdf',
            'doc' => 'file-word',
            'docx' => 'file-word',
            'xls' => 'file-excel',
            'xlsx' => 'file-excel',
            'ppt' => 'file-powerpoint',
            'pptx' => 'file-powerpoint',
            'zip' => 'file-archive',
            'rar' => 'file-archive',
            'mp4' => 'file-video',
            'avi' => 'file-video',
            'mov' => 'file-video',
            'jpg' => 'file-image',
            'jpeg' => 'file-image',
            'png' => 'file-image',
            'gif' => 'file-image',
            'mp3' => 'file-audio',
            'wav' => 'file-audio',
            'txt' => 'file-alt',
            'html' => 'file-code',
            'css' => 'file-code',
            'js' => 'file-code',
            'php' => 'file-code',
            'java' => 'file-code',
            'py' => 'file-code'
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