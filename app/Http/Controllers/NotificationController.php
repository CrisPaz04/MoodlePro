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
        
        // Filtros
        $filter = $request->get('filter', 'all'); // all, unread, read
        $type = $request->get('type'); // tipo específico de notificación
        
        // Construir query
        $query = $user->notifications();
        
        // Aplicar filtros
        if ($filter === 'unread') {
            $query->unread();
        } elseif ($filter === 'read') {
            $query->read();
        }
        
        if ($type) {
            $query->ofType($type);
        }
        
        // Obtener notificaciones paginadas
        $notifications = $query->with('related')->paginate(20);
        
        // Estadísticas
        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'today' => $user->notifications()->whereDate('created_at', today())->count(),
        ];
        
        // Si es petición AJAX, devolver JSON
        if ($request->ajax()) {
            return response()->json([
                'notifications' => $notifications,
                'stats' => $stats,
            ]);
        }
        
        return view('notifications.index', compact('notifications', 'stats', 'filter', 'type'));
    }

    /**
     * Marcar una notificación como leída
     */
    public function markAsRead(Notification $notification)
    {
        // Verificar que la notificación pertenece al usuario
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída',
        ]);
    }

    /**
     * Marcar múltiples notificaciones como leídas
     */
    public function markMultipleAsRead(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id',
        ]);
        
        $count = Auth::user()->markNotificationsAsRead($request->notification_ids);
        
        return response()->json([
            'success' => true,
            'message' => "{$count} notificaciones marcadas como leídas",
            'count' => $count,
        ]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead()
    {
        $count = Auth::user()->markAllNotificationsAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas',
            'count' => $count,
        ]);
    }

    /**
     * Eliminar una notificación
     */
    public function destroy(Notification $notification)
    {
        // Verificar que la notificación pertenece al usuario
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notificación eliminada',
        ]);
    }

    /**
     * Eliminar múltiples notificaciones
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id',
        ]);
        
        $count = Auth::user()->notifications()
            ->whereIn('id', $request->notification_ids)
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => "{$count} notificaciones eliminadas",
            'count' => $count,
        ]);
    }

    /**
     * Eliminar todas las notificaciones leídas
     */
    public function clearRead()
    {
        $count = Auth::user()->deleteReadNotifications();
        
        return response()->json([
            'success' => true,
            'message' => "{$count} notificaciones leídas eliminadas",
            'count' => $count,
        ]);
    }

    /**
     * Obtener conteo de notificaciones no leídas (para polling)
     */
    public function unreadCount()
    {
        $count = Auth::user()->unreadNotificationsCount;
        
        return response()->json([
            'count' => $count,
            'has_unread' => $count > 0,
        ]);
    }

    /**
     * Obtener notificaciones recientes (para el dropdown del header)
     */
    public function recent()
    {
        $notifications = Auth::user()->notifications()
            ->with('related')
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
            'unread_count' => Auth::user()->unreadNotificationsCount,
        ]);
    }

    /**
     * Obtener notificaciones agrupadas por fecha
     */
    public function grouped()
    {
        $grouped = Auth::user()->getNotificationsGroupedByDate();
        
        return response()->json([
            'grouped' => $grouped,
            'unread_count' => Auth::user()->unreadNotificationsCount,
        ]);
    }

    /**
     * Actualizar preferencias de notificación
     */
    public function updatePreferences(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'task_notifications' => 'boolean',
            'project_notifications' => 'boolean',
            'message_notifications' => 'boolean',
        ]);
        
        $user = Auth::user();
        
        // Aquí actualizarías las preferencias en la base de datos
        // Por ahora, simulamos la actualización
        $preferences = $request->only([
            'email_notifications',
            'push_notifications',
            'task_notifications',
            'project_notifications',
            'message_notifications',
        ]);
        
        // $user->update($preferences); // Si tienes estos campos en la tabla users
        
        return response()->json([
            'success' => true,
            'message' => 'Preferencias actualizadas',
            'preferences' => $preferences,
        ]);
    }

    /**
     * Enviar notificación de prueba
     */
    public function test()
    {
        $user = Auth::user();
        
        // Crear diferentes tipos de notificaciones de prueba
        $notifications = [];
        
        // Notificación de tarea
        $notifications[] = Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_TASK_ASSIGNED,
            'title' => 'Notificación de prueba - Tarea',
            'message' => 'Esta es una notificación de prueba para una tarea asignada',
            'data' => [
                'task_title' => 'Tarea de ejemplo',
                'project_title' => 'Proyecto de prueba',
            ],
        ]);
        
        // Notificación de proyecto
        $notifications[] = Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_PROJECT_DEADLINE,
            'title' => 'Notificación de prueba - Proyecto',
            'message' => 'El proyecto vence en 3 días',
            'data' => [
                'project_title' => 'Proyecto de prueba',
                'days' => 3,
            ],
        ]);
        
        // Notificación de mensaje
        $notifications[] = Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_MESSAGE_RECEIVED,
            'title' => 'Notificación de prueba - Mensaje',
            'message' => 'Has recibido un nuevo mensaje',
            'data' => [
                'sender_name' => 'Usuario de prueba',
                'project_title' => 'Proyecto de prueba',
            ],
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notificaciones de prueba creadas',
            'notifications' => $notifications,
        ]);
    }

    /**
     * Obtener estadísticas de notificaciones para el dashboard
     */
    public function stats()
    {
        $user = Auth::user();
        
        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotificationsCount,
            'today' => $user->notifications()->whereDate('created_at', today())->count(),
            'this_week' => $user->notifications()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'by_type' => $user->notifications()
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
        ];
        
        return response()->json($stats);
    }
}