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
        
        // Construir query usando tu modelo personalizado
        $query = Notification::where('user_id', $user->id);
        
        // Aplicar filtros
        if ($filter === 'unread') {
            $query->unread();
        } elseif ($filter === 'read') {
            $query->read();
        }
        
        if ($type) {
            $query->ofType($type);
        }
        
        // Obtener notificaciones paginadas ordenadas por fecha
        $notifications = $query->orderBy('created_at', 'desc')
                               ->paginate(20);
        
        // Asegurarnos de que cada notificación tenga las propiedades necesarias
        $notifications->getCollection()->transform(function ($notification) {
            // Decodificar el campo data si es un string JSON
            if (is_string($notification->data)) {
                $notification->data = json_decode($notification->data, true) ?: [];
            }
            
            // Asegurar que data sea siempre un array
            if (!is_array($notification->data)) {
                $notification->data = [];
            }
            
            // Agregar propiedades dinámicas para la vista
            $notification->formatted_message = $notification->message ?? 'Sin descripción';
            $notification->time_ago = $notification->created_at->diffForHumans();
            $notification->icon = $this->getIconForType($notification->type);
            $notification->color = $this->getColorForType($notification->type);
            $notification->action_url = $notification->data['action_url'] ?? null;
            
            return $notification;
        });
        
        // Estadísticas
        $stats = [
            'total' => Notification::where('user_id', $user->id)->count(),
            'unread' => Notification::where('user_id', $user->id)->unread()->count(),
            'today' => Notification::where('user_id', $user->id)->whereDate('created_at', today())->count(),
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
     * Obtener icono según el tipo de notificación
     */
    private function getIconForType($type)
    {
        $icons = [
            'task_assigned' => 'fas fa-tasks',
            'task_completed' => 'fas fa-check-circle',
            'task_overdue' => 'fas fa-exclamation-triangle',
            'project_invitation' => 'fas fa-envelope',
            'project_deadline' => 'fas fa-calendar-alt',
            'project_member_added' => 'fas fa-user-plus',
            'project_member_removed' => 'fas fa-user-minus',
            'message_received' => 'fas fa-comment',
            'info' => 'fas fa-info-circle',
            'success' => 'fas fa-check-circle',
            'warning' => 'fas fa-exclamation-triangle',
            'error' => 'fas fa-times-circle',
        ];

        return $icons[$type] ?? 'fas fa-bell';
    }

    /**
     * Obtener color según el tipo de notificación
     */
    private function getColorForType($type)
    {
        $colors = [
            'task_assigned' => 'task',
            'task_completed' => 'success',
            'task_overdue' => 'danger',
            'project_invitation' => 'info',
            'project_deadline' => 'warning',
            'project_member_added' => 'success',
            'project_member_removed' => 'warning',
            'message_received' => 'info',
            'info' => 'info',
            'success' => 'success',
            'warning' => 'warning',
            'error' => 'danger',
        ];

        return $colors[$type] ?? 'info';
    }

    /**
     * Marcar una notificación como leída
     */
    public function markAsRead(Notification $notification)
    {
        // Verificar que la notificación pertenece al usuario
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída',
        ]);
    }

    /**
     * Marcar una notificación como no leída
     */
    public function markAsUnread(Notification $notification)
    {
        // Verificar que la notificación pertenece al usuario
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }
        
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
        $user = Auth::user();
        
        Notification::where('user_id', $user->id)
                    ->unread()
                    ->update(['read_at' => now()]);
        
        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas',
        ]);
    }

    /**
     * Eliminar una notificación
     */
    public function destroy(Notification $notification)
    {
        // Verificar que la notificación pertenece al usuario
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }
        
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
        $user = Auth::user();
        
        Notification::where('user_id', $user->id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones eliminadas',
        ]);
    }

    /**
     * Crear notificaciones de prueba (para desarrollo)
     */
    public function createTestNotifications()
    {
        $user = Auth::user();
        $notifications = [];
        
        // Notificación de tarea asignada
        $notifications[] = Notification::create([
            'user_id' => $user->id,
            'type' => 'task_assigned',
            'title' => 'Nueva tarea asignada',
            'message' => 'Se te ha asignado la tarea "Implementar sistema de login"',
            'data' => [
                'task_id' => 1,
                'task_title' => 'Implementar sistema de login',
                'project_id' => 1,
                'project_title' => 'MoodlePro',
                'action_url' => '/tasks/1',
            ],
        ]);
        
        // Notificación de proyecto
        $notifications[] = Notification::create([
            'user_id' => $user->id,
            'type' => 'project_invitation',
            'title' => 'Invitación a proyecto',
            'message' => 'Has sido invitado al proyecto "Frontend Development"',
            'data' => [
                'project_id' => 2,
                'project_title' => 'Frontend Development',
                'inviter_name' => 'Admin',
                'action_url' => '/projects/2',
            ],
        ]);
        
        // Notificación de mensaje
        $notifications[] = Notification::create([
            'user_id' => $user->id,
            'type' => 'message_received',
            'title' => 'Nuevo mensaje',
            'message' => 'Has recibido un nuevo mensaje en el proyecto MoodlePro',
            'data' => [
                'sender_name' => 'Oscar',
                'project_title' => 'MoodlePro',
                'message_preview' => 'Hey, ¿cómo va el backend?',
                'action_url' => '/projects/1/chat',
            ],
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notificaciones de prueba creadas',
            'notifications' => $notifications,
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
}