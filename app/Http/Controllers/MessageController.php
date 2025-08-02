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
        try {
            $request->validate([
                'project_id' => 'required|exists:projects,id',
                'content' => 'required|string|max:500'
            ]);

            // Verificar acceso al proyecto
            $project = Project::findOrFail($request->project_id);
            if (!$project->members->contains(Auth::id()) && $project->creator_id !== Auth::id()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes acceso a este proyecto'
                    ], 403);
                }
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

            // SIEMPRE devolver JSON para peticiones con Accept: application/json
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (\Exception $e) {
            \Log::error('Error al enviar mensaje: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar el mensaje',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error al enviar el mensaje');
        }
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
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar este mensaje'
                ], 403);
            }
            abort(403, 'No tienes permisos para eliminar este mensaje');
        }

        try {
            $message->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mensaje eliminado'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el mensaje'
            ], 500);
        }
    }
}