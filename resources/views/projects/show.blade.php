@extends('layouts.app')

@section('title', $project->title . ' - MoodlePro')

@push('styles')
<style>
    /* Project Header Styles */
    .project-header-section {
        background-color: #fff;
        padding: 2rem;
        margin-bottom: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .project-info {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1.5rem;
    }

    .project-title-section h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2e3440;
        margin: 0 0 0.5rem 0;
    }

    .project-meta {
        display: flex;
        gap: 2rem;
        color: #858796;
        font-size: 0.875rem;
    }

    .project-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .project-status-badge {
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
    }

    .status-planning {
        background-color: rgba(246, 194, 62, 0.2);
        color: #993d00;
    }

    .status-active {
        background-color: rgba(28, 200, 138, 0.2);
        color: #0f6848;
    }

    .status-completed {
        background-color: rgba(78, 115, 223, 0.2);
        color: #2e59d9;
    }

    /* Tabs */
    .project-tabs {
        border-bottom: 2px solid #e3e6f0;
        margin-bottom: 2rem;
    }

    .nav-tabs {
        border: none;
    }

    .nav-tabs .nav-link {
        border: none;
        color: #858796;
        padding: 1rem 1.5rem;
        font-weight: 500;
        border-bottom: 3px solid transparent;
        transition: all 0.3s;
    }

    .nav-tabs .nav-link:hover {
        color: #4e73df;
        border-bottom-color: #e3e6f0;
    }

    .nav-tabs .nav-link.active {
        color: #4e73df;
        border-bottom-color: #4e73df;
        background: none;
    }

    /* Kanban Board Styles */
    .kanban-board {
        display: flex;
        gap: 1.5rem;
        overflow-x: auto;
        padding-bottom: 1rem;
        min-height: 600px;
    }

    .kanban-column {
        background-color: #f8f9fc;
        border-radius: 0.5rem;
        padding: 1rem;
        min-width: 320px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .kanban-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e3e6f0;
    }

    .kanban-title {
        font-size: 1rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #5a5c69;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .task-count {
        background-color: #e3e6f0;
        color: #5a5c69;
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .kanban-tasks {
        flex: 1;
        overflow-y: auto;
        padding-right: 0.5rem;
    }

    /* Task Card Styles */
    .task-card {
        background-color: #fff;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        cursor: move;
        transition: all 0.3s;
        position: relative;
        border-left: 4px solid transparent;
    }

    .task-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .task-card.dragging {
        opacity: 0.5;
        transform: rotate(5deg);
    }

    .task-card.drag-over {
        border: 2px dashed #4e73df;
        background-color: #f8f9fc;
    }

    /* Priority Indicators */
    .task-card.priority-high {
        border-left-color: #e74a3b;
    }

    .task-card.priority-medium {
        border-left-color: #f6c23e;
    }

    .task-card.priority-low {
        border-left-color: #1cc88a;
    }

    .task-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 0.5rem;
    }

    .task-title {
        font-weight: 600;
        color: #2e3440;
        margin: 0;
        font-size: 0.9375rem;
        flex: 1;
        padding-right: 0.5rem;
    }

    .task-priority {
        font-size: 0.75rem;
        padding: 0.125rem 0.5rem;
        border-radius: 1rem;
        font-weight: 600;
        text-transform: capitalize;
        white-space: nowrap;
    }

    .priority-high {
        background-color: rgba(231, 74, 59, 0.2);
        color: #a71d2a;
    }

    .priority-medium {
        background-color: rgba(246, 194, 62, 0.2);
        color: #993d00;
    }

    .priority-low {
        background-color: rgba(28, 200, 138, 0.2);
        color: #0f6848;
    }

    .task-description {
        font-size: 0.8125rem;
        color: #858796;
        margin-bottom: 0.75rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .task-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.75rem;
        color: #858796;
    }

    .task-assignee {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .assignee-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        object-fit: cover;
    }

    .task-due-date {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .task-due-date.overdue {
        color: #e74a3b;
        font-weight: 600;
    }

    /* Add Task Button */
    .add-task-btn {
        background-color: transparent;
        border: 2px dashed #d1d3e2;
        color: #858796;
        padding: 0.75rem;
        border-radius: 0.5rem;
        cursor: pointer;
        text-align: center;
        margin-top: 0.5rem;
        transition: all 0.3s;
        font-weight: 500;
        width: 100%;
    }

    .add-task-btn:hover {
        background-color: #fff;
        border-color: #4e73df;
        color: #4e73df;
    }

    /* Task Actions */
    .task-actions {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .task-card:hover .task-actions {
        opacity: 1;
    }

    .task-action-btn {
        background: none;
        border: none;
        color: #858796;
        padding: 0.25rem;
        cursor: pointer;
        font-size: 0.875rem;
        transition: color 0.3s;
    }

    .task-action-btn:hover {
        color: #4e73df;
    }

    /* Empty State */
    .empty-column {
        text-align: center;
        padding: 3rem 1rem;
        color: #b7b9cc;
    }

    .empty-column i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .kanban-board {
            flex-direction: column;
        }
        
        .kanban-column {
            min-width: 100%;
            margin-bottom: 1rem;
        }

        .project-info {
            flex-direction: column;
            gap: 1rem;
        }

        .project-meta {
            flex-wrap: wrap;
            gap: 1rem;
        }
    }

    /* Loading State */
    .task-skeleton {
        background-color: #f1f3f4;
        border-radius: 0.5rem;
        height: 120px;
        margin-bottom: 0.75rem;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.6; }
        100% { opacity: 1; }
    }
</style>
@endpush

@section('content')
<!-- Project Header -->
<div class="project-header-section">
    <div class="project-info">
        <div class="project-title-section">
            <h1>{{ $project->title }}</h1>
            <div class="project-meta">
                <div class="project-meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Inicio: {{ $project->start_date->format('d M Y') }}</span>
                </div>
                <div class="project-meta-item">
                    <i class="fas fa-clock"></i>
                    <span>Entrega: {{ $project->deadline->format('d M Y') }}</span>
                </div>
                <div class="project-meta-item">
                    <i class="fas fa-users"></i>
                    <span>{{ $project->members->count() }} miembros</span>
                </div>
            </div>
        </div>
        <div>
            <span class="project-status-badge status-{{ $project->status }}">
                {{ ucfirst($project->status) }}
            </span>
        </div>
    </div>

    @if($project->description)
    <p class="text-muted mb-0">{{ $project->description }}</p>
    @endif
</div>

<!-- Tabs -->
<ul class="nav nav-tabs project-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#kanban">
            <i class="fas fa-columns me-2"></i>Tablero Kanban
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#members">
            <i class="fas fa-users me-2"></i>Miembros
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#files">
            <i class="fas fa-file-alt me-2"></i>Archivos
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('projects.chat', $project) }}">
            <i class="fas fa-comments me-2"></i>Chat
        </a>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    <!-- Kanban Board Tab -->
    <div class="tab-pane fade show active" id="kanban">
        <div class="kanban-board">
            <!-- TO DO Column -->
            <div class="kanban-column" data-status="todo">
                <div class="kanban-header">
                    <h3 class="kanban-title">
                        <i class="fas fa-circle-notch text-warning"></i>
                        POR HACER
                    </h3>
                    <span class="task-count">{{ $tasksByStatus['todo']->count() }}</span>
                </div>
                <div class="kanban-tasks" data-status="todo">
                    @forelse($tasksByStatus['todo'] as $task)
                        @include('projects.partials.task-card', ['task' => $task])
                    @empty
                        <div class="empty-column">
                            <i class="fas fa-tasks"></i>
                            <p>No hay tareas pendientes</p>
                        </div>
                    @endforelse
                </div>
                <button class="add-task-btn" onclick="openCreateTaskModal('todo')">
                    <i class="fas fa-plus me-2"></i>Agregar tarea
                </button>
            </div>

            <!-- IN PROGRESS Column -->
            <div class="kanban-column" data-status="in_progress">
                <div class="kanban-header">
                    <h3 class="kanban-title">
                        <i class="fas fa-spinner text-info"></i>
                        EN PROGRESO
                    </h3>
                    <span class="task-count">{{ $tasksByStatus['in_progress']->count() }}</span>
                </div>
                <div class="kanban-tasks" data-status="in_progress">
                    @forelse($tasksByStatus['in_progress'] as $task)
                        @include('projects.partials.task-card', ['task' => $task])
                    @empty
                        <div class="empty-column">
                            <i class="fas fa-hourglass-half"></i>
                            <p>No hay tareas en progreso</p>
                        </div>
                    @endforelse
                </div>
                <button class="add-task-btn" onclick="openCreateTaskModal('in_progress')">
                    <i class="fas fa-plus me-2"></i>Agregar tarea
                </button>
            </div>

            <!-- DONE Column -->
            <div class="kanban-column" data-status="done">
                <div class="kanban-header">
                    <h3 class="kanban-title">
                        <i class="fas fa-check-circle text-success"></i>
                        COMPLETADAS
                    </h3>
                    <span class="task-count">{{ $tasksByStatus['done']->count() }}</span>
                </div>
                <div class="kanban-tasks" data-status="done">
                    @forelse($tasksByStatus['done'] as $task)
                        @include('projects.partials.task-card', ['task' => $task])
                    @empty
                        <div class="empty-column">
                            <i class="fas fa-trophy"></i>
                            <p>No hay tareas completadas</p>
                        </div>
                    @endforelse
                </div>
                <button class="add-task-btn" onclick="openCreateTaskModal('done')">
                    <i class="fas fa-plus me-2"></i>Agregar tarea
                </button>
            </div>
        </div>
    </div>

    <!-- Members Tab -->
    <div class="tab-pane fade" id="members">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Miembros del Proyecto</h5>
                <div class="row">
                    @foreach($project->members as $member)
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&background=4e73df&color=fff" 
                                 alt="{{ $member->name }}" 
                                 class="rounded-circle me-3"
                                 style="width: 48px; height: 48px;">
                            <div>
                                <h6 class="mb-0">{{ $member->name }}</h6>
                                <small class="text-muted">{{ ucfirst($member->pivot->role) }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Files Tab -->
    <div class="tab-pane fade" id="files">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Archivos del Proyecto</h5>
                <p class="text-muted">Los archivos del proyecto aparecerán aquí</p>
            </div>
        </div>
    </div>
</div>

<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createTaskForm" method="POST" action="{{ route('tasks.store') }}">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <input type="hidden" name="status" id="taskStatus" value="todo">
                
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Tarea</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="taskTitle" class="form-label">Título</label>
                        <input type="text" class="form-control" id="taskTitle" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="taskDescription" class="form-label">Descripción</label>
                        <textarea class="form-control" id="taskDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskPriority" class="form-label">Prioridad</label>
                                <select class="form-select" id="taskPriority" name="priority">
                                    <option value="low">Baja</option>
                                    <option value="medium" selected>Media</option>
                                    <option value="high">Alta</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskDueDate" class="form-label">Fecha de entrega</label>
                                <input type="date" class="form-control" id="taskDueDate" name="due_date">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="taskAssignee" class="form-label">Asignar a</label>
                        <select class="form-select" id="taskAssignee" name="assigned_to">
                            <option value="">Sin asignar</option>
                            @foreach($project->members as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Tarea</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
// Variables globales
let draggedTask = null;
const projectId = {{ $project->id }};

// Inicializar Sortable.js para cada columna
document.addEventListener('DOMContentLoaded', function() {
    const columns = document.querySelectorAll('.kanban-tasks');
    
    columns.forEach(column => {
        new Sortable(column, {
            group: 'shared',
            animation: 150,
            ghostClass: 'task-skeleton',
            dragClass: 'dragging',
            onStart: function(evt) {
                draggedTask = evt.item;
                evt.item.classList.add('dragging');
            },
            onEnd: function(evt) {
                evt.item.classList.remove('dragging');
                
                const taskId = evt.item.dataset.taskId;
                const newStatus = evt.to.dataset.status;
                const newOrder = evt.newIndex;
                
                // Actualizar en el servidor
                updateTaskStatus(taskId, newStatus, newOrder);
                
                // Actualizar contadores
                updateTaskCounts();
            }
        });
    });
});

// Actualizar estado de tarea
function updateTaskStatus(taskId, status, order) {
    fetch(`/api/tasks/${taskId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            status: status,
            order: order
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Tarea actualizada', 'success');
        } else {
            showNotification('Error al actualizar la tarea', 'error');
            // Recargar página en caso de error
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

// Actualizar contadores de tareas
function updateTaskCounts() {
    const columns = ['todo', 'in_progress', 'done'];
    
    columns.forEach(status => {
        const tasks = document.querySelectorAll(`[data-status="${status}"] .task-card`);
        const counter = document.querySelector(`[data-status="${status}"] .task-count`);
        if (counter) {
            counter.textContent = tasks.length;
        }
    });
}

// Abrir modal de crear tarea
function openCreateTaskModal(status = 'todo') {
    document.getElementById('taskStatus').value = status;
    const modal = new bootstrap.Modal(document.getElementById('createTaskModal'));
    modal.show();
}

// Editar tarea
function editTask(taskId) {
    window.location.href = `/tasks/${taskId}/edit`;
}

// Eliminar tarea
function deleteTask(taskId) {
    if (confirm('¿Estás seguro de eliminar esta tarea?')) {
        fetch(`/tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (response.ok) {
                document.querySelector(`[data-task-id="${taskId}"]`).remove();
                updateTaskCounts();
                showNotification('Tarea eliminada', 'success');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al eliminar la tarea', 'error');
        });
    }
}

// Mostrar notificación
function showNotification(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 'alert-info';
    
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Manejar envío del formulario de crear tarea
document.getElementById('createTaskForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recargar la página para mostrar la nueva tarea
            location.reload();
        } else {
            showNotification('Error al crear la tarea', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
});
</script>
@endpush