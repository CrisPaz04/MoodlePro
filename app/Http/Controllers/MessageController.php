<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Project $project)
    {
        $messages = $project->messages()
            ->with(['user', 'replies.user'])
            ->main()
            ->oldest()
            ->get();
            
        return view('messages.index', compact('project', 'messages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'content' => 'required|string|max:1000',
            'reply_to' => 'nullable|exists:messages,id'
        ]);

        $message = Message::create([
            'project_id' => $request->project_id,
            'user_id' => Auth::id(),
            'content' => $request->content,
            'reply_to' => $request->reply_to
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message->load('user')
            ]);
        }

        return redirect()->back()->with('success', 'Mensaje enviado');
    }

    public function update(Request $request, Message $message)
    {
        if ($message->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $message->update([
            'content' => $request->content,
            'is_edited' => true,
            'edited_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Message $message)
    {
        if ($message->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $message->delete();
        return response()->json(['success' => true]);
    }

    // API endpoint para obtener mensajes nuevos (polling)
    public function getNewMessages(Project $project, Request $request)
    {
        $lastMessageId = $request->get('last_message_id', 0);
        
        $newMessages = $project->messages()
            ->with('user')
            ->where('id', '>', $lastMessageId)
            ->oldest()
            ->get();

        return response()->json([
            'messages' => $newMessages,
            'count' => $newMessages->count()
        ]);
    }
}