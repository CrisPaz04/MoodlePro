<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = File::with(['user', 'project']);

        // Filter by project if specified
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by mime type
        if ($request->has('type')) {
            switch ($request->type) {
                case 'image':
                    $query->where('mime_type', 'like', 'image/%');
                    break;
                case 'video':
                    $query->where('mime_type', 'like', 'video/%');
                    break;
                case 'audio':
                    $query->where('mime_type', 'like', 'audio/%');
                    break;
                case 'document':
                    $query->whereIn('mime_type', [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'text/plain',
                    ]);
                    break;
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('original_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $files = $query->paginate(12);

        return view('files.index', compact('files'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $projects = Project::where('user_id', auth()->id())
            ->orWhereHas('members', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->get();

        $projectId = $request->get('project_id');

        return view('files.create', compact('projects', 'projectId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $uploadedFile = $request->file('file');
        
        // Generate unique filename
        $filename = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        
        // Define the path
        $path = 'files/' . date('Y/m/d') . '/' . $filename;
        
        // Upload to S3
        $disk = Storage::disk('moodlepro');
        $disk->put($path, file_get_contents($uploadedFile), 'public');
        
        // Get the full URL
        $url = $disk->url($path);

        // Create file record
        $file = File::create([
            'name' => $request->name,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'path' => $path,
            'url' => $url,
            'disk' => 'moodlepro',
            'user_id' => auth()->id(),
            'project_id' => $request->project_id,
            'description' => $request->description,
        ]);

        // Create notification if file is for a project
        if ($file->project_id) {
            // Here you would create notifications for project members
            // We'll implement this when we create the notification system
        }

        return redirect()->route('files.show', $file)
            ->with('success', 'File uploaded successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(File $file)
    {
        $file->load(['user', 'project']);
        
        // Get related files
        $relatedFiles = File::where('id', '!=', $file->id)
            ->where(function ($query) use ($file) {
                $query->where('project_id', $file->project_id)
                      ->orWhere('mime_type', $file->mime_type);
            })
            ->limit(6)
            ->get();

        return view('files.show', compact('file', 'relatedFiles'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(File $file)
    {
        $this->authorize('update', $file);

        $projects = Project::where('user_id', auth()->id())
            ->orWhereHas('members', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->get();

        return view('files.edit', compact('file', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, File $file)
    {
        $this->authorize('update', $file);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $file->update($request->only(['name', 'description', 'project_id']));

        return redirect()->route('files.show', $file)
            ->with('success', 'File updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file)
    {
        $this->authorize('delete', $file);

        $file->delete(); // This will also delete from S3 thanks to the model boot method

        return redirect()->route('files.index')
            ->with('success', 'File deleted successfully!');
    }

    /**
     * Download the file
     */
    public function download(File $file)
    {
        $file->incrementDownloads();

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }

    /**
     * Rate the file
     */
    public function rate(Request $request, File $file)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $file->addRating($request->rating);

        return back()->with('success', 'Thank you for rating this file!');
    }

    /**
     * Preview the file (for images and PDFs)
     */
    public function preview(File $file)
    {
        if ($file->isImage() || $file->mime_type === 'application/pdf') {
            return response()->file(Storage::disk($file->disk)->path($file->path));
        }

        abort(404);
    }
}