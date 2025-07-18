<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Crear una nueva instancia del controlador
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar lista de notificaciones del usuario
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Obtener todas las notificaciones del usuario ordenadas por fecha
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Estadísticas básicas
        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->notifications()->whereNull('read_at')->count(),
            'today' => $user->notifications()->whereDate('created_at', today())->count(),
        ];
        
        return view('notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Marcar una notificación como leída
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída',
        ]);
    }

    /**
     * Marcar una notificación como no leída
     */
    public function markAsUnread($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        
        $notification->markAsUnread();
        
        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como no leída',
        ]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead()
    {
        Auth::user()->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas',
        ]);
    }

    /**
     * Eliminar una notificación
     */
    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notificación eliminada',
        ]);
    }

    /**
     * Eliminar todas las notificaciones
     */
    public function clearAll()
    {
        Auth::user()->notifications()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones eliminadas',
        ]);
    }

    /**
     * Obtener conteo de notificaciones no leídas (para el badge del header)
     */
    public function unreadCount()
    {
        $count = Auth::user()->notifications()
            ->whereNull('read_at')
            ->count();
        
        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Obtener notificaciones recientes (para el dropdown del header)
     */
    public function recent()
    {
        $notifications = Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->formatted_message,
                    'icon' => $notification->icon,
                    'color' => $notification->color,
                    'time' => $notification->time_ago,
                    'is_read' => $notification->isRead(),
                    'action_url' => $notification->action_url,
                ];
            });
        
        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    /**
     * Crear notificación de prueba (solo desarrollo)
     */
    public function test()
    {
        $user = Auth::user();
        
        // Crear notificación de prueba
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'info',
            'title' => 'Notificación de prueba',
            'message' => 'Esta es una notificación de prueba creada en ' . now()->format('H:i:s'),
            'data' => [
                'test' => true,
                'timestamp' => now()->toDateTimeString(),
            ],
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notificación de prueba creada',
            'notification' => $notification,
        ]);
    }
}