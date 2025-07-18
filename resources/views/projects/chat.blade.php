@extends('layouts.app')

@section('title', 'Chat - ' . $project->title)

@push('styles')
<style>
    .chat-container {
        display: flex;
        height: calc(100vh - 80px);
        background: #f8f9fc;
    }

    /* Sidebar */
    .chat-sidebar {
        width: 320px;
        background: white;
        border-right: 1px solid #e3e6f0;
        display: flex;
        flex-direction: column;
    }

    .project-header {
        padding: 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .project-header h3 {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .project-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .project-meta-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .members-section {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
    }

    .section-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #858796;
        text-transform: uppercase;
        margin-bottom: 1rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e3e6f0;
    }

    .member-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.3s;
        margin-bottom: 0.5rem;
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
        font-weight: 600;
        color: #5a5c69;
        position: relative;
    }

    .member-info {
        flex: 1;
    }

    .member-name {
        font-weight: 600;
        color: #2e3440;
        margin-bottom: 0.125rem;
    }

    .member-status {
        font-size: 0.75rem;
        color: #858796;
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
        padding: 0.5rem 1rem;
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

    /* Typing Indicator */
    .typing-indicator {
        display: none;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem;
        color: #858796;
        font-size: 0.875rem;
    }

    .typing-indicator.show {
        display: flex;
    }

    .typing-dots {
        display: flex;
        gap: 0.25rem;
    }

    .dot {
        width: 6px;
        height: 6px;
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

    /* Message Input */
    .message-input-container {
        padding: 1.5rem;
        background: white;
        border-top: 1px solid #e3e6f0;
    }

    .message-input-wrapper {
        display: flex;
        gap: 1rem;
        align-items: flex-end;
    }

    .input-group {
        flex: 1;
        background: #f8f9fc;
        border-radius: 1.5rem;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .message-input {
        flex: 1;
        background: none;
        border: none;
        outline: none;
        resize: none;
        font-family: inherit;
        max-height: 100px;
    }

    .input-actions {
        display: flex;
        gap: 0.5rem;
    }

    .input-btn {
        background: none;
        border: none;
        color: #858796;
        cursor: pointer;
        padding: 0.5rem;
        transition: all 0.3s;
    }

    .input-btn:hover {
        color: #4e73df;
    }

    .send-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 2rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .send-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .send-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Empty State */
    .empty-chat {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #858796;
        text-align: center;
        padding: 2rem;
    }

    .empty-chat i {
        font-size: 5rem;
        color: #d1d3e2;
        margin-bottom: 2rem;
    }

    /* Animations */
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

    @keyframes typing {
        0%, 60%, 100% {
            transform: translateY(0);
        }
        30% {
            transform: translateY(-10px);
        }
    }

    /* Mobile */
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
<div class="chat-container">
    <!-- Sidebar -->
    <div class="chat-sidebar">
        <div class="project-header">
            <h3>{{ $project->title }}</h3>
            <div class="project-meta">
                <div class="project-meta-item">
                    <i class="fas fa-users"></i>
                    {{ $project->members->count() }} miembros
                </div>
                <div class="project-meta-item">
                    <i class="fas fa-tasks"></i>
                    {{ $project->tasks->count() }} tareas
                </div>
            </div>
        </div>

        <div class="members-section">
            <h4 class="section-title">Miembros del Equipo</h4>
            @foreach($project->members as $member)
                <div class="member-item" data-member-id="{{ $member->id }}">
                    <div class="member-avatar">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                        @if($member->isOnline ?? false)
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
                        </div>
                    </div>
                    <button type="submit" class="send-btn" id="sendBtn">
                        <i class="fas fa-paper-plane"></i>
                        Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-resize textarea
function autoResize(element) {
    element.style.height = 'auto';
    element.style.height = element.scrollHeight + 'px';
}

// Handle enter key
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
    
    // Add message to UI immediately
    addMessageToUI({
        id: Date.now(),
        content: message,
        user: {
            id: {{ auth()->id() }},
            name: '{{ auth()->user()->name }}'
        },
        created_at: new Date()
    }, true);
    
    // Clear input
    input.value = '';
    autoResize(input);
    
    // Send to server
    fetch('{{ route("messages.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            project_id: {{ $project->id }},
            content: message
        })
    })
    .then(response => response.json())
    .then(data => {
        // Enable send button
        document.getElementById('sendBtn').disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al enviar el mensaje');
        document.getElementById('sendBtn').disabled = false;
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
            <div class="message-text">${message.content}</div>
            <div class="message-time">
                ${message.user.name} • ${new Date(message.created_at).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}
            </div>
        </div>
    `;
    
    container.appendChild(messageEl);
    
    // Scroll to bottom
    container.scrollTop = container.scrollHeight;
}

// Search messages
function searchMessages() {
    // Implementar búsqueda
    alert('Búsqueda de mensajes en desarrollo');
}

// Toggle info
function toggleInfo() {
    // Implementar panel de información
    alert('Panel de información en desarrollo');
}

// Attach file
function attachFile() {
    // Implementar adjuntar archivos
    alert('Adjuntar archivos en desarrollo');
}

// Insert emoji
function insertEmoji() {
    // Implementar selector de emojis
    alert('Selector de emojis en desarrollo');
}

// Auto-scroll to bottom on load
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('messagesContainer');
    container.scrollTop = container.scrollHeight;
});

// Simulate typing indicator (remove in production)
let typingTimer;
document.getElementById('messageInput').addEventListener('input', function() {
    clearTimeout(typingTimer);
    
    if (this.value.trim()) {
        // Show typing indicator after delay
        typingTimer = setTimeout(() => {
            // Send typing status to server
        }, 500);
    }
});
</script>
@endpush