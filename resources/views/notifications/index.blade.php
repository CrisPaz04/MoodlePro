@extends('layouts.app')

@section('title', 'Notificaciones - MoodlePro')

@push('styles')
<style>
    .notifications-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 2rem 2rem;
    }

    .notifications-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }

    .header-btn {
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid white;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.3s;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .header-btn:hover {
        background: white;
        color: #4e73df;
    }

    .notifications-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .filter-tabs {
        background: white;
        padding: 1rem;
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        margin-bottom: 2rem;
        display: flex;
        gap: 1rem;
        overflow-x: auto;
    }

    .filter-tab {
        padding: 0.75rem 1.5rem;
        border: 2px solid #e3e6f0;
        background: white;
        color: #5a5c69;
        border-radius: 2rem;
        font-weight: 500;
        transition: all 0.3s;
        cursor: pointer;
        white-space: nowrap;
        text-decoration: none;
    }

    .filter-tab.active,
    .filter-tab:hover {
        background: #4e73df;
        color: white;
        border-color: #4e73df;
    }

    .filter-tab .badge {
        background: rgba(0, 0, 0, 0.1);
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        margin-left: 0.5rem;
    }

    .filter-tab.active .badge {
        background: rgba(255, 255, 255, 0.3);
    }

    .notifications-list {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        overflow: hidden;
    }

    .notification-item {
        padding: 1.5rem;
        border-bottom: 1px solid #e3e6f0;
        display: flex;
        gap: 1rem;
        transition: all 0.3s;
        cursor: pointer;
        position: relative;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item.unread {
        background: #f8f9fc;
    }

    .notification-item:hover {
        background: #f8f9fc;
    }

    .notification-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .icon-task { background: rgba(78, 115, 223, 0.2); color: #4e73df; }
    .icon-success { background: rgba(28, 200, 138, 0.2); color: #1cc88a; }
    .icon-warning { background: rgba(246, 194, 62, 0.2); color: #f6c23e; }
    .icon-danger { background: rgba(231, 74, 59, 0.2); color: #e74a3b; }
    .icon-info { background: rgba(54, 185, 204, 0.2); color: #36b9cc; }

    .notification-content {
        flex: 1;
        min-width: 0;
    }

    .notification-title {
        font-weight: 600;
        color: #2e3440;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .notification-message {
        color: #5a5c69;
        margin-bottom: 0.5rem;
        line-height: 1.5;
    }

    .notification-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 0.875rem;
        color: #858796;
    }

    .notification-time {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .notification-action {
        color: #4e73df;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .notification-action:hover {
        color: #2e59d9;
    }

    .unread-indicator {
        width: 8px;
        height: 8px;
        background: #4e73df;
        border-radius: 50%;
        position: absolute;
        top: 1.5rem;
        left: 0.5rem;
    }

    .notification-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .action-btn {
        padding: 0.5rem;
        border: none;
        background: none;
        color: #858796;
        cursor: pointer;
        transition: all 0.3s;
    }

    .action-btn:hover {
        color: #4e73df;
        transform: scale(1.1);
    }

    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        color: #858796;
    }

    .empty-state i {
        font-size: 5rem;
        color: #d1d3e2;
        margin-bottom: 2rem;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        color: #5a5c69;
        margin-bottom: 1rem;
    }

    .load-more {
        display: block;
        width: 100%;
        padding: 1rem;
        background: #f8f9fc;
        border: none;
        color: #4e73df;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .load-more:hover {
        background: #e3e6f0;
    }

    @media (max-width: 768px) {
        .notifications-header h1 {
            font-size: 1.5rem;
        }

        .header-actions {
            flex-wrap: wrap;
        }

        .notification-item {
            padding: 1rem;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
        }

        .notification-meta {
            flex-direction: column;
            align-items: start;
            gap: 0.5rem;
        }
    }
</style>
@endpush

@section('content')
<!-- Notifications Header -->
<div class="notifications-header">
    <div class="container">
        <h1>Notificaciones</h1>
        <p class="lead mb-0">Mantente al día con toda la actividad de tus proyectos</p>
        <div class="header-actions">
            <button class="header-btn" onclick="markAllAsRead()">
                <i class="fas fa-check-double"></i>
                Marcar todas como leídas
            </button>
            <button class="header-btn" onclick="clearAllNotifications()">
                <i class="fas fa-trash"></i>
                Limpiar todo
            </button>
        </div>
    </div>
</div>

<div class="container">
    <div class="notifications-container">
        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="#" class="filter-tab active" data-filter="all">
                Todas
                <span class="badge">{{ $notifications->count() }}</span>
            </a>
            <a href="#" class="filter-tab" data-filter="unread">
                No leídas
                <span class="badge">{{ $notifications->where('read_at', null)->count() }}</span>
            </a>
            <a href="#" class="filter-tab" data-filter="tasks">
                Tareas
                <span class="badge">{{ $notifications->whereIn('type', ['task_assigned', 'task_completed', 'task_overdue'])->count() }}</span>
            </a>
            <a href="#" class="filter-tab" data-filter="projects">
                Proyectos
                <span class="badge">{{ $notifications->whereIn('type', ['project_invitation', 'project_deadline', 'project_member_added'])->count() }}</span>
            </a>
            <a href="#" class="filter-tab" data-filter="messages">
                Mensajes
                <span class="badge">{{ $notifications->where('type', 'message_received')->count() }}</span>
            </a>
        </div>

        <!-- Notifications List -->
        <div class="notifications-list">
            @forelse($notifications as $notification)
                <div class="notification-item {{ $notification->read_at ? '' : 'unread' }}" 
                     data-id="{{ $notification->id }}"
                     data-type="{{ $notification->type }}"
                     data-read="{{ $notification->read_at ? 'true' : 'false' }}"
                     onclick="handleNotificationClick({{ $notification->id }}, '{{ $notification->action_url }}')">
                    
                    @if(!$notification->read_at)
                        <div class="unread-indicator"></div>
                    @endif
                    
                    <div class="notification-icon icon-{{ $notification->color }}">
                        <i class="{{ $notification->icon }}"></i>
                    </div>
                    
                    <div class="notification-content">
                        <div class="notification-title">
                            {{ $notification->title }}
                        </div>
                        <div class="notification-message">
                            {{ $notification->formatted_message }}
                        </div>
                        <div class="notification-meta">
                            <div class="notification-time">
                                <i class="fas fa-clock"></i>
                                {{ $notification->time_ago }}
                            </div>
                            @if($notification->action_url)
                                <a href="{{ $notification->action_url }}" class="notification-action" onclick="event.stopPropagation()">
                                    Ver detalles
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                    
                    <div class="notification-actions">
                        <button class="action-btn" title="Marcar como {{ $notification->read_at ? 'no leída' : 'leída' }}"
                                onclick="event.stopPropagation(); toggleRead({{ $notification->id }})">
                            <i class="fas fa-{{ $notification->read_at ? 'envelope' : 'envelope-open' }}"></i>
                        </button>
                        <button class="action-btn" title="Eliminar"
                                onclick="event.stopPropagation(); deleteNotification({{ $notification->id }})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h3>No tienes notificaciones</h3>
                    <p>Cuando haya actividad en tus proyectos, aparecerá aquí</p>
                </div>
            @endforelse
            
            @if($notifications->count() > 10)
                <button class="load-more">
                    Cargar más notificaciones
                </button>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Filter functionality
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Update active state
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        const notifications = document.querySelectorAll('.notification-item');
        
        notifications.forEach(notification => {
            const type = notification.dataset.type;
            const isUnread = notification.dataset.read === 'false';
            
            let show = false;
            
            switch(filter) {
                case 'all':
                    show = true;
                    break;
                case 'unread':
                    show = isUnread;
                    break;
                case 'tasks':
                    show = ['task_assigned', 'task_completed', 'task_overdue'].includes(type);
                    break;
                case 'projects':
                    show = ['project_invitation', 'project_deadline', 'project_member_added'].includes(type);
                    break;
                case 'messages':
                    show = type === 'message_received';
                    break;
            }
            
            notification.style.display = show ? 'flex' : 'none';
        });
    });
});

// Handle notification click
function handleNotificationClick(id, url) {
    // Mark as read
    markAsRead(id);
    
    // Navigate if URL exists
    if (url) {
        window.location.href = url;
    }
}

// Mark as read
function markAsRead(id) {
    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            const item = document.querySelector(`[data-id="${id}"]`);
            item.classList.remove('unread');
            item.dataset.read = 'true';
            const indicator = item.querySelector('.unread-indicator');
            if (indicator) indicator.remove();
            updateBadgeCounts();
        }
    });
}

// Toggle read status
function toggleRead(id) {
    const item = document.querySelector(`[data-id="${id}"]`);
    const isRead = item.dataset.read === 'true';
    
    fetch(`/notifications/${id}/${isRead ? 'unread' : 'read'}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            if (isRead) {
                item.classList.add('unread');
                item.dataset.read = 'false';
                item.insertAdjacentHTML('afterbegin', '<div class="unread-indicator"></div>');
            } else {
                item.classList.remove('unread');
                item.dataset.read = 'true';
                const indicator = item.querySelector('.unread-indicator');
                if (indicator) indicator.remove();
            }
            updateBadgeCounts();
        }
    });
}

// Delete notification
function deleteNotification(id) {
    if (confirm('¿Estás seguro de eliminar esta notificación?')) {
        fetch(`/notifications/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                const item = document.querySelector(`[data-id="${id}"]`);
                item.style.transition = 'all 0.3s';
                item.style.opacity = '0';
                item.style.transform = 'translateX(100%)';
                setTimeout(() => item.remove(), 300);
                updateBadgeCounts();
            }
        });
    }
}

// Mark all as read
function markAllAsRead() {
    fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
                item.dataset.read = 'true';
                const indicator = item.querySelector('.unread-indicator');
                if (indicator) indicator.remove();
            });
            updateBadgeCounts();
        }
    });
}

// Clear all notifications
function clearAllNotifications() {
    if (confirm('¿Estás seguro de eliminar todas las notificaciones?')) {
        fetch('/notifications/clear-all', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}

// Update badge counts
function updateBadgeCounts() {
    const allCount = document.querySelectorAll('.notification-item').length;
    const unreadCount = document.querySelectorAll('.notification-item.unread').length;
    const tasksCount = document.querySelectorAll('.notification-item[data-type*="task"]').length;
    const projectsCount = document.querySelectorAll('.notification-item[data-type*="project"]').length;
    const messagesCount = document.querySelectorAll('.notification-item[data-type="message_received"]').length;
    
    document.querySelector('[data-filter="all"] .badge').textContent = allCount;
    document.querySelector('[data-filter="unread"] .badge').textContent = unreadCount;
    document.querySelector('[data-filter="tasks"] .badge').textContent = tasksCount;
    document.querySelector('[data-filter="projects"] .badge').textContent = projectsCount;
    document.querySelector('[data-filter="messages"] .badge').textContent = messagesCount;
}
</script>
@endpush