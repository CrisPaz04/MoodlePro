<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Resource::public()->with('uploader');

        if ($request->category) {
            $query->byCategory($request->category);
        }

        if ($request->search) {
            $query->whereFullText(['title', 'description', 'tags'], $request->search);
        }

        $sortBy = $request->get('sort', 'recent');
        switch ($sortBy) {
            case 'popular':
                $query->popular();
                break;
            case 'rated':
                $query->topRated();
                break;
            default:
                $query->recent();
        }

        $resources = $query->paginate(12);
        $categories = Resource::distinct()->pluck('category');

        return view('resources.index', compact('resources', 'categories'));
    }

    public function create()
    {
        return view('resources.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:51200', // 50MB máximo
            'category' => 'required|in:document,presentation,spreadsheet,image,other',
            'tags' => 'nullable|string',
            'is_public' => 'boolean'
        ]);

        $file = $request->file('file');
        $path = $file->store('resources', 'public');

        Resource::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'category' => $request->category,
            'tags' => $request->tags,
            'uploaded_by' => Auth::id(),
            'is_public' => $request->boolean('is_public', true)
        ]);

        return redirect()->route('resources.index')
            ->with('success', 'Recurso subido exitosamente');
    }

    public function show(Resource $resource)
    {
        $resource->load('uploader');
        return view('resources.show', compact('resource'));
    }

    public function download(Resource $resource)
    {
        $resource->incrementDownloads();
        return Storage::disk('public')->download($resource->file_path, $resource->file_name);
    }

    public function destroy(Resource $resource)
    {
        if ($resource->uploaded_by !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        Storage::disk('public')->delete($resource->file_path);
        $resource->delete();

        return redirect()->route('resources.index')
            ->with('success', 'Recurso eliminado exitosamente');
    }

    public function rate(Request $request, Resource $resource)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5'
        ]);

        $resource->updateRating($request->rating);

        return response()->json(['success' => true, 'new_rating' => $resource->average_rating]);
    }
}