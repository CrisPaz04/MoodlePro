@extends('layouts.app')

@section('title', 'Mis Tareas - MoodlePro')

@section('content')
<div class="dashboard-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="dashboard-title">
                    <i class="fas fa-tasks me-3"></i>
                    Mis Tareas
                </h1>
                <p class="dashboard-subtitle">
                    Gestiona todas tus tareas asignadas en un solo lugar
                </p>
            </div>
            <div class="col-auto">
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="filterTasks('all')">
                        <i class="fas fa-list me-2"></i>Todas
                    </button>
                    <button class="btn btn-outline-warning" onclick="filterTasks('pending')">
                        <i class="fas fa-clock me-2"></i>Pendientes
                    </button>
                    <button class="btn btn-outline-success" onclick="filterTasks('completed')">
                        <i class="fas fa-check me-2"></i>Completadas
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card bg-primary">
                <div class="stats-content">
                    <div class="stats-number">{{ $tasks->count() }}</div>
                    <div class="stats-label">Total</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card bg-warning">
                <div class="stats-content">
                    <div class="stats-number">{{ $tasks->whereIn('status', ['todo', 'in_progress'])->count() }}</div>
                    <div class="stats-label">Pendientes</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card bg-success">
                <div class="stats-content">
                    <div class="stats-number">{{ $tasks->where('status', 'done')->count() }}</div>
                    <div class="stats-label">Completadas</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card bg-danger">
                <div class="stats-content">
                    <div class="stats-number">{{ $tasks->where('due_date', '<', now())->where('status', '!=', 'done')->count() }}</div>
                    <div class="stats-label">Vencidas</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" class="form-control" id="searchTasks" placeholder="Buscar tareas...">
            </div>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterProject">
                <option value="">Todos los proyectos</option>
                @foreach($tasks->pluck('project')->unique('id') as $project)
                    <option value="{{ $project->id }}">{{ $project->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterPriority">
                <option value="">Todas las prioridades</option>
                <option value="high">Alta</option>
                <option value="medium">Media</option>
                <option value="low">Baja</option>
            </select>
        </div>
    </div>

    <!-- Lista de Tareas -->
    <div class="row">
        <div class="col-12">
            @if($tasks->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3>No tienes tareas asignadas</h3>
                    <p class="text-muted">Las tareas que te asignen aparecerán aquí</p>
                    <a href="{{ route('projects.index') }}" class="btn btn-primary">
                        <i class="fas fa-project-diagram me-2"></i>Ver Proyectos
                    </a>
                </div>
            @else
                <div class="tasks-grid" id="tasksContainer">
                    @foreach($tasks as $task)
                        <div class="task-card" 
                             data-status="{{ $task->status }}" 
                             data-priority="{{ $task->priority }}" 
                             data-project="{{ $task->project_id }}"
                             data-title="{{ strtolower($task->title) }}">
                            
                            <!-- Task Header -->
                            <div class="task-header">
                                <div class="task-status">
                                    @if($task->status == 'todo')
                                        <span class="badge bg-secondary">Por hacer</span>
                                    @elseif($task->status == 'in_progress')
                                        <span class="badge bg-primary">En progreso</span>
                                    @else
                                        <span class="badge bg-success">Completada</span>
                                    @endif
                                </div>
                                
                                <div class="task-priority">
                                    @if($task->priority == 'high')
                                        <span class="priority-badge high">
                                            <i class="fas fa-arrow-up"></i> Alta
                                        </span>
                                    @elseif($task->priority == 'medium')
                                        <span class="priority-badge medium">
                                            <i class="fas fa-minus"></i> Media
                                        </span>
                                    @else
                                        <span class="priority-badge low">
                                            <i class="fas fa-arrow-down"></i> Baja
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Task Content -->
                            <div class="task-content">
                                <h5 class="task-title">{{ $task->title }}</h5>
                                
                                @if($task->description)
                                    <p class="task-description">{{ Str::limit($task->description, 100) }}</p>
                                @endif

                                <div class="task-meta">
                                    <div class="task-project">
                                        <i class="fas fa-folder me-1"></i>
                                        <span>{{ $task->project->title }}</span>
                                    </div>
                                    
                                    @if($task->due_date)
                                        <div class="task-date {{ $task->due_date < now() && $task->status != 'done' ? 'overdue' : '' }}">
                                            <i class="fas fa-calendar me-1"></i>
                                            <span>{{ $task->due_date->format('d M Y') }}</span>
                                        </div>
                                    @endif
                                </div>

                                @if($task->creator)
                                    <div class="task-creator">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($task->creator->name) }}&background=4e73df&color=fff" 
                                             alt="{{ $task->creator->name }}" 
                                             class="creator-avatar">
                                        <span class="creator-name">Asignada por {{ $task->creator->name }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Task Actions -->
                            <div class="task-actions">
                                <a href="{{ route('projects.show', $task->project) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Ver Proyecto
                                </a>
                                
                                @if($task->status != 'done')
                                    <button class="btn btn-sm btn-success" onclick="markTaskComplete({{ $task->id }})">
                                        <i class="fas fa-check me-1"></i>Completar
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.tasks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
}

.task-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}

.task-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.task-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.task-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #1f2937;
}

.task-description {
    color: #6b7280;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.task-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.task-project, .task-date {
    display: flex;
    align-items: center;
    font-size: 0.85rem;
    color: #6b7280;
}

.task-date.overdue {
    color: #dc2626;
    font-weight: 600;
}

.task-creator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding-top: 0.75rem;
    border-top: 1px solid #f3f4f6;
}

.creator-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
}

.creator-name {
    font-size: 0.8rem;
    color: #6b7280;
}

.task-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

.priority-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 600;
}

.priority-badge.high {
    background-color: #fef2f2;
    color: #dc2626;
}

.priority-badge.medium {
    background-color: #fef3c7;
    color: #d97706;
}

.priority-badge.low {
    background-color: #f0fdf4;
    color: #16a34a;
}

.empty-state {
    text-align: center;
    padding: 4rem 1rem;
}

.empty-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .tasks-grid {
        grid-template-columns: 1fr;
    }
    
    .dashboard-header .col-auto {
        margin-top: 1rem;
    }
    
    .dashboard-header .d-flex {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<script>
// Filtrado de tareas
function filterTasks(status) {
    const cards = document.querySelectorAll('.task-card');
    
    cards.forEach(card => {
        const taskStatus = card.dataset.status;
        
        if (status === 'all') {
            card.style.display = 'block';
        } else if (status === 'pending') {
            card.style.display = (taskStatus === 'todo' || taskStatus === 'in_progress') ? 'block' : 'none';
        } else if (status === 'completed') {
            card.style.display = taskStatus === 'done' ? 'block' : 'none';
        }
    });
}

// Búsqueda de tareas
document.getElementById('searchTasks').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const cards = document.querySelectorAll('.task-card');
    
    cards.forEach(card => {
        const title = card.dataset.title;
        card.style.display = title.includes(searchTerm) ? 'block' : 'none';
    });
});

// Filtro por proyecto
document.getElementById('filterProject').addEventListener('change', function() {
    const projectId = this.value;
    const cards = document.querySelectorAll('.task-card');
    
    cards.forEach(card => {
        if (!projectId || card.dataset.project === projectId) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

// Filtro por prioridad
document.getElementById('filterPriority').addEventListener('change', function() {
    const priority = this.value;
    const cards = document.querySelectorAll('.task-card');
    
    cards.forEach(card => {
        if (!priority || card.dataset.priority === priority) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

// Marcar tarea como completada
function markTaskComplete(taskId) {
    if (confirm('¿Estás seguro de que quieres marcar esta tarea como completada?')) {
        fetch(`/api/tasks/${taskId}/complete`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al completar la tarea');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al completar la tarea');
        });
    }
}
</script>
@endsection