<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Constructor - Requiere autenticación
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar chat del proyecto
     */
    public function index(Project $project)
    {
        // Verificar que el usuario tenga acceso al proyecto
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            abort(403, 'No tienes acceso a este proyecto');
        }

        // Cargar mensajes con usuarios
        $messages = $project->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // Cargar información del proyecto con miembros
        $project->load('members');

        return view('projects.chat', compact('project', 'messages'));
    }

    /**
     * Guardar nuevo mensaje
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'content' => 'required|string|max:500'
        ]);

        // Verificar acceso al proyecto
        $project = Project::findOrFail($request->project_id);
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            abort(403, 'No tienes acceso a este proyecto');
        }

        // Crear mensaje
        $message = Message::create([
            'project_id' => $request->project_id,
            'user_id' => Auth::id(),
            'content' => $request->content
        ]);

        // Cargar relación con usuario
        $message->load('user');

        // Notificar a los miembros del proyecto (excepto al emisor)
        foreach ($project->members as $member) {
            if ($member->id !== Auth::id()) {
                // Aquí se crearía la notificación
                \App\Models\Notification::messageReceived($member, $message);
            }
        }

        // Si es petición AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'user' => [
                        'id' => $message->user->id,
                        'name' => $message->user->name
                    ],
                    'created_at' => $message->created_at->toISOString()
                ]
            ]);
        }

        return redirect()->back();
    }

    /**
     * Obtener mensajes nuevos (para polling/real-time)
     */
    public function getNewMessages(Project $project, Request $request)
    {
        // Verificar acceso
        if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
            abort(403);
        }

        $lastMessageId = $request->get('last_message_id', 0);
        
        $newMessages = $project->messages()
            ->with('user')
            ->where('id', '>', $lastMessageId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'user' => [
                        'id' => $message->user->id,
                        'name' => $message->user->name
                    ],
                    'created_at' => $message->created_at->toISOString()
                ];
            });

        return response()->json([
            'messages' => $newMessages,
            'count' => $newMessages->count()
        ]);
    }

    /**
     * Eliminar mensaje (solo el autor)
     */
    public function destroy(Message $message)
    {
        // Solo el autor puede eliminar
        if ($message->user_id !== Auth::id()) {
            abort(403, 'No tienes permisos para eliminar este mensaje');
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mensaje eliminado'
        ]);
    }
}