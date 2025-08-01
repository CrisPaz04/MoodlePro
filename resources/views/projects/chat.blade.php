@extends('layouts.app')

@section('title', 'Chat - ' . $project->title)

@push('styles')
<style>
    /* Chat Container */
    .chat-container {
        height: calc(100vh - 200px);
        display: flex;
        background: #f8f9fa;
    }

    /* Sidebar */
    .chat-sidebar {
        width: 280px;
        background: white;
        border-right: 1px solid #e3e6f0;
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e3e6f0;
    }

    .project-info h5 {
        margin: 0;
        color: #2e3440;
        font-size: 1.1rem;
    }

    .project-info p {
        margin: 0.5rem 0 0;
        color: #858796;
        font-size: 0.875rem;
    }

    .members-list {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
    }

    .member-item {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .member-item:hover {
        background: #f8f9fc;
    }

    .member-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e3e6f0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-weight: 600;
        color: #5a5c69;
        position: relative;
    }

    .online-indicator {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 12px;
        height: 12px;
        background: #1cc88a;
        border: 2px solid white;
        border-radius: 50%;
    }

    /* Chat Area */
    .chat-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: white;
    }

    .chat-header {
        padding: 1.5rem;
        background: white;
        border-bottom: 1px solid #e3e6f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chat-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2e3440;
    }

    .chat-actions {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        padding: 0.5rem 1rem;
        background: none;
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
        color: #858796;
        cursor: pointer;
        transition: all 0.3s;
    }

    .action-btn:hover {
        background: #f8f9fc;
        color: #4e73df;
    }

    /* Messages Area */
    .messages-container {
        flex: 1;
        overflow-y: auto;
        padding: 2rem;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .message {
        display: flex;
        gap: 1rem;
        max-width: 70%;
        animation: fadeIn 0.3s ease;
    }

    .message.own {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .message-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #e3e6f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: 600;
        color: #5a5c69;
        flex-shrink: 0;
    }

    .message-content {
        background: #f8f9fc;
        padding: 1rem 1.25rem;
        border-radius: 1rem;
        position: relative;
    }

    .message.own .message-content {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .message-text {
        margin-bottom: 0.5rem;
        line-height: 1.5;
    }

    .message-time {
        font-size: 0.75rem;
        opacity: 0.7;
    }

    .message.own .message-time {
        text-align: right;
    }

    /* Date Separator */
    .date-separator {
        text-align: center;
        margin: 2rem 0;
        position: relative;
    }

    .date-separator span {
        background: white;
        padding: 0 1rem;
        color: #858796;
        font-size: 0.875rem;
        position: relative;
        z-index: 1;
    }

    .date-separator::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: #e3e6f0;
    }

    /* Empty State */
    .empty-chat {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #858796;
    }

    .empty-chat i {
        font-size: 4rem;
        color: #d1d3e2;
        margin-bottom: 1rem;
    }

    /* Message Input */
    .message-input-container {
        padding: 1.5rem;
        background: white;
        border-top: 1px solid #e3e6f0;
    }

    .message-input-wrapper {
        position: relative;
    }

    .input-group {
        display: flex;
        align-items: flex-end;
        gap: 0.5rem;
    }

    .message-input {
        flex: 1;
        border: 1px solid #e3e6f0;
        border-radius: 1.5rem;
        padding: 0.75rem 1.25rem;
        resize: none;
        outline: none;
        font-size: 0.95rem;
        min-height: 44px;
        max-height: 120px;
    }

    .message-input:focus {
        border-color: #4e73df;
    }

    .input-actions {
        display: flex;
        gap: 0.5rem;
    }

    .input-btn {
        padding: 0.5rem 0.75rem;
        background: none;
        border: none;
        color: #858796;
        cursor: pointer;
        transition: all 0.2s;
        border-radius: 50%;
    }

    .input-btn:hover {
        background: #f8f9fc;
        color: #4e73df;
    }

    .send-btn {
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        border-radius: 1.5rem;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 500;
    }

    .send-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .send-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Typing Indicator */
    .typing-indicator {
        display: none;
        padding: 0 2rem 1rem;
        color: #858796;
        font-size: 0.875rem;
        align-items: center;
        gap: 0.5rem;
    }

    .typing-dots {
        display: flex;
        gap: 3px;
    }

    .dot {
        width: 8px;
        height: 8px;
        background: #858796;
        border-radius: 50%;
        animation: typing 1.4s infinite;
    }

    .dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes typing {
        0%, 60%, 100% {
            transform: translateY(0);
            opacity: 0.4;
        }
        30% {
            transform: translateY(-10px);
            opacity: 1;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .chat-sidebar {
            display: none;
        }
        
        .message {
            max-width: 85%;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid p-0">
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="chat-sidebar">
            <div class="sidebar-header">
                <div class="project-info">
                    <h5>{{ $project->title }}</h5>
                    <p>{{ $project->members->count() }} miembros</p>
                </div>
                <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-secondary mt-3">
                    <i class="fas fa-arrow-left me-1"></i>Volver al proyecto
                </a>
            </div>
            
            <div class="members-list">
                <h6 class="text-uppercase text-muted small mb-3">Miembros</h6>
                @foreach($project->members as $member)
                    <div class="member-item">
                        <div class="member-avatar">
                            {{ strtoupper(substr($member->name, 0, 1)) }}
                            @if(false)
                                <span class="online-indicator"></span>
                            @endif
                        </div>
                        <div class="member-info">
                            <div class="member-name">{{ $member->name }}</div>
                            <div class="member-status">
                                {{ $member->pivot->role == 'coordinator' ? 'Coordinador' : 'Miembro' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Chat Area -->
        <div class="chat-area">
            <div class="chat-header">
                <h2 class="chat-title">Chat del Proyecto</h2>
                <div class="chat-actions">
                    <button class="action-btn" onclick="searchMessages()">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="action-btn" onclick="toggleInfo()">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </div>
            </div>

            <div class="messages-container" id="messagesContainer">
                @if($messages->count() > 0)
                    @php $lastDate = null; @endphp
                    
                    @foreach($messages as $message)
                        @if(!$lastDate || $message->created_at->format('Y-m-d') != $lastDate)
                            <div class="date-separator">
                                <span>{{ $message->created_at->format('d M Y') }}</span>
                            </div>
                            @php $lastDate = $message->created_at->format('Y-m-d'); @endphp
                        @endif
                        
                        <div class="message {{ $message->user_id == auth()->id() ? 'own' : '' }}" data-message-id="{{ $message->id }}">
                            <div class="message-avatar">
                                {{ strtoupper(substr($message->user->name, 0, 1)) }}
                            </div>
                            <div class="message-content">
                                <div class="message-text">{{ $message->content }}</div>
                                <div class="message-time">
                                    {{ $message->user->name }} • {{ $message->created_at->format('g:i A') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-chat">
                        <i class="fas fa-comments"></i>
                        <h3>No hay mensajes aún</h3>
                        <p>Sé el primero en iniciar la conversación</p>
                    </div>
                @endif
            </div>

            <div class="typing-indicator" id="typingIndicator">
                <div class="typing-dots">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
                <span>Alguien está escribiendo...</span>
            </div>

            <div class="message-input-container">
                <form id="messageForm" onsubmit="sendMessage(event)">
                    <div class="message-input-wrapper">
                        <div class="input-group">
                            <textarea 
                                class="message-input" 
                                id="messageInput"
                                placeholder="Escribe un mensaje..."
                                rows="1"
                                maxlength="500"
                                onkeypress="handleKeyPress(event)"
                                oninput="autoResize(this)"></textarea>
                            
                            <div class="input-actions">
                                <button type="button" class="input-btn" onclick="attachFile()">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <button type="button" class="input-btn" onclick="insertEmoji()">
                                    <i class="fas fa-smile"></i>
                                </button>
                                <button type="submit" class="send-btn" id="sendBtn">
                                    <i class="fas fa-paper-plane me-1"></i>Enviar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Store CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Auto-resize textarea
function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

// Handle Enter key
function handleKeyPress(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage(event);
    }
}

// Send message
function sendMessage(event) {
    event.preventDefault();
    
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Disable send button
    document.getElementById('sendBtn').disabled = true;
    
    // Create temporary message ID
    const tempId = 'temp_' + Date.now();
    
    // Add message to UI immediately
    addMessageToUI({
        id: tempId,
        content: message,
        user: {
            id: {{ Auth::id() }},
            name: '{{ Auth::user()->name }}'
        },
        created_at: new Date().toISOString()
    }, true);
    
    // Clear input
    input.value = '';
    autoResize(input);
    
    // Send to server
    fetch('{{ route("messages.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            project_id: {{ $project->id }},
            content: message
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        // Enable send button
        document.getElementById('sendBtn').disabled = false;
        
        if (data.success) {
            // Update temp message with real ID
            const tempMessage = document.querySelector(`[data-message-id="${tempId}"]`);
            if (tempMessage) {
                tempMessage.dataset.messageId = data.message.id;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al enviar el mensaje');
        document.getElementById('sendBtn').disabled = false;
        
        // Remove temp message on error
        const tempMessage = document.querySelector(`[data-message-id="${tempId}"]`);
        if (tempMessage) {
            tempMessage.remove();
        }
    });
}

// Add message to UI
function addMessageToUI(message, isOwn = false) {
    const container = document.getElementById('messagesContainer');
    
    // Remove empty state if exists
    const emptyState = container.querySelector('.empty-chat');
    if (emptyState) {
        emptyState.remove();
    }
    
    // Check if we need date separator
    const lastMessage = container.querySelector('.message:last-child');
    const messageDate = new Date(message.created_at).toDateString();
    
    if (!lastMessage || new Date(lastMessage.dataset.date).toDateString() !== messageDate) {
        const separator = document.createElement('div');
        separator.className = 'date-separator';
        separator.innerHTML = `<span>${new Date(message.created_at).toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' })}</span>`;
        container.appendChild(separator);
    }
    
    // Create message element
    const messageEl = document.createElement('div');
    messageEl.className = `message ${isOwn ? 'own' : ''}`;
    messageEl.dataset.messageId = message.id;
    messageEl.dataset.date = message.created_at;
    
    messageEl.innerHTML = `
        <div class="message-avatar">
            ${message.user.name.charAt(0).toUpperCase()}
        </div>
        <div class="message-content">
            <div class="message-text">${escapeHtml(message.content)}</div>
            <div class="message-time">
                ${message.user.name} • ${new Date(message.created_at).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}
            </div>
        </div>
    `;
    
    container.appendChild(messageEl);
    
    // Scroll to bottom
    container.scrollTop = container.scrollHeight;
}

// Escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Search messages
function searchMessages() {
    alert('Búsqueda de mensajes en desarrollo');
}

// Toggle info
function toggleInfo() {
    alert('Panel de información en desarrollo');
}

// Attach file
function attachFile() {
    alert('Adjuntar archivos en desarrollo');
}

// Insert emoji
function insertEmoji() {
    alert('Selector de emojis en desarrollo');
}

// Auto-scroll to bottom on load
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('messagesContainer');
    container.scrollTop = container.scrollHeight;
    
    // Focus input
    document.getElementById('messageInput').focus();
});

// Poll for new messages (temporary until WebSockets)
let lastMessageId = {{ $messages->last()->id ?? 0 }};

setInterval(function() {
    fetch(`{{ route('messages.new', $project) }}?last_message_id=${lastMessageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(message => {
                    // Only add if not from current user
                    if (message.user.id !== {{ Auth::id() }}) {
                        addMessageToUI(message, false);
                    }
                    lastMessageId = Math.max(lastMessageId, message.id);
                });
            }
        })
        .catch(error => console.error('Error fetching messages:', error));
}, 3000); // Check every 3 seconds
</script>
@endpush