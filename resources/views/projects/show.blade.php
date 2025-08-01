{{-- Ruta: resources/views/projects/show.blade.php --}}
@extends('layouts.app')

@section('title', $project->title)

@push('styles')
<style>
    /* Project Header */
    .project-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 0.5rem;
        margin-bottom: 2rem;
    }

    .project-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .project-meta {
        display: flex;
        gap: 2rem;
        margin-top: 1rem;
        flex-wrap: wrap;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        opacity: 0.9;
    }

    /* Project Members Section */
    .project-members-section {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid #dee2e6;
        margin-bottom: 2rem;
    }

    .members-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }

    .member-card {
        display: flex;
        align-items: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
        position: relative;
    }

    .member-card:hover {
        background: #e9ecef;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .member-avatar {
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .avatar-img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .avatar-placeholder {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.2rem;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .member-info {
        flex: 1;
    }

    .member-name {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.25rem;
        font-size: 1rem;
    }

    .member-email {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .member-role {
        margin-bottom: 0.25rem;
    }

    .role-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        margin-right: 0.5rem;
        margin-bottom: 0.25rem;
    }

    .role-badge.coordinator {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .role-badge.member {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #b8daff;
    }

    .role-badge.creator {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .member-joined {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .member-badge-self {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
    }

    .no-members-message {
        grid-column: 1 / -1;
        text-align: center;
        padding: 2rem;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        background: #f8f9fa;
    }

    .no-members-message i {
        font-size: 2rem;
        display: block;
    }

    /* Tabs */
    .nav-tabs {
        border-bottom: 2px solid #e3e6f0;
        margin-bottom: 2rem;
    }

    .nav-tabs .nav-link {
        color: #858796;
        border: none;
        padding: 1rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s;
    }

    .nav-tabs .nav-link:hover {
        color: #4e73df;
        border-bottom: 2px solid #4e73df;
    }

    .nav-tabs .nav-link.active {
        color: #4e73df;
        border-bottom: 2px solid #4e73df;
        background: none;
    }

    /* Kanban Board */
    .kanban-board {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        min-height: 500px;
    }

    .kanban-column {
        background: #f8f9fc;
        border-radius: 0.5rem;
        padding: 1rem;
        min-height: 400px;
    }

    .column-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e3e6f0;
    }

    .column-title {
        font-weight: 600;
        color: #5a5c69;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .task-count {
        background: #e3e6f0;
        color: #5a5c69;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .tasks-container {
        min-height: 300px;
        transition: background-color 0.3s;
    }

    .tasks-container.drag-over {
        background-color: #e3e6f0;
        border: 2px dashed #4e73df;
        border-radius: 0.5rem;
    }

    /* Task Cards */
    .task-card {
        background: white;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        cursor: move;
        transition: all 0.3s;
        position: relative;
    }

    .task-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .task-card:hover .task-actions {
        opacity: 1;
    }

    .task-card.dragging {
        opacity: 0.5;
        transform: rotate(5deg);
    }

    .task-content {
        cursor: pointer;
    }

    .task-content:hover .task-title {
        color: #4e73df;
        text-decoration: underline;
    }

    .task-actions {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        opacity: 0;
        transition: opacity 0.3s;
        display: flex;
        gap: 0.25rem;
    }

    .task-actions button {
        background: #f8f9fc;
        border: none;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        color: #5a5c69;
        cursor: pointer;
        transition: all 0.2s;
    }

    .task-actions button:hover {
        background: #e3e6f0;
        color: #2e3440;
    }

    .task-actions button.delete-btn:hover {
        background: #ffe6e6;
        color: #e74a3b;
    }

    .task-priority {
        display: inline-block;
        width: 4px;
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
        border-radius: 0.5rem 0 0 0.5rem;
    }

    .priority-high { background: #e74a3b; }
    .priority-medium { background: #f6c23e; }
    .priority-low { background: #1cc88a; }

    .task-content {
        position: relative;
        padding-left: 0.5rem;
    }

    .task-title {
        font-weight: 600;
        color: #2e3440;
        margin-bottom: 0.5rem;
    }

    .task-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0.75rem;
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
        background: #4e73df;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .task-due-date {
        font-size: 0.75rem;
        color: #858796;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .task-due-date.overdue {
        color: #e74a3b;
    }

    /* Add Task Button */
    .add-task-btn {
        width: 100%;
        padding: 0.75rem;
        border: 2px dashed #d1d3e2;
        background: transparent;
        color: #858796;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .add-task-btn:hover {
        border-color: #4e73df;
        color: #4e73df;
        background: #f8f9fc;
    }

    /* Alert Messages */
    .alert {
        border-radius: 0.5rem;
        border: none;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
    }

    .alert-success {
        background-color: #d1f2eb;
        color: #0c5f4c;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    @media (max-width: 768px) {
        .members-grid {
            grid-template-columns: 1fr;
        }
        
        .member-card {
            padding: 0.75rem;
        }
        
        .member-avatar {
            margin-right: 0.75rem;
        }
        
        .avatar-img,
        .avatar-placeholder {
            width: 40px;
            height: 40px;
        }
        
        .avatar-placeholder {
            font-size: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Project Header -->
    <div class="project-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1>{{ $project->title }}</h1>
                <p class="mb-0 opacity-75">{{ $project->description }}</p>
                <div class="project-meta">
                    <div class="meta-item">
                        <i class="fas fa-user-circle"></i>
                        <span>Creado por {{ $project->creator->name }}</span>
                    </div>
                    @if($project->start_date || $project->deadline)
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>
                                @if($project->start_date && $project->deadline)
                                    {{ $project->start_date->format('d M Y') }} - {{ $project->deadline->format('d M Y') }}
                                @elseif($project->start_date)
                                    Inicio: {{ $project->start_date->format('d M Y') }}
                                @elseif($project->deadline)
                                    Fin: {{ $project->deadline->format('d M Y') }}
                                @endif
                            </span>
                        </div>
                    @endif
                    <div class="meta-item">
                        <i class="fas fa-users"></i>
                        <span>{{ $project->members->count() }} miembros</span>
                    </div>
                </div>
            </div>
            @if(Auth::id() === $project->creator_id)
                <a href="{{ route('projects.edit', $project) }}" class="btn btn-light">
                    <i class="fas fa-edit me-2"></i>Editar
                </a>
            @endif
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tasks">
                <i class="fas fa-tasks me-2"></i>Tareas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#members">
                <i class="fas fa-users me-2"></i>Miembros
            </a>
        </li>
        <li class="nav-item">
    <a class="nav-link" href="{{ route('projects.chat', $project) }}">
        <i class="fas fa-comments me-2"></i>Chat
    </a>
</li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#files">
                <i class="fas fa-folder me-2"></i>Archivos
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Tasks Tab (Kanban Board) -->
        <div class="tab-pane fade show active" id="tasks">
            <div class="kanban-board">
                <!-- To Do Column -->
                <div class="kanban-column">
                    <div class="column-header">
                        <h5 class="column-title">
                            <i class="fas fa-circle text-secondary"></i>
                            Por Hacer
                        </h5>
                        <span class="task-count">{{ $project->tasks->where('status', 'todo')->count() }}</span>
                    </div>
                    <div class="tasks-container" data-status="todo">
                        @foreach($project->tasks->where('status', 'todo')->sortBy('order') as $task)
                            <div class="task-card" draggable="true" data-task-id="{{ $task->id }}">
                                <div class="task-priority priority-{{ $task->priority }}"></div>
                                <div class="task-actions">
                                    <button onclick="editTask({{ $task->id }})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteTask({{ $task->id }})" class="delete-btn" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="task-content" onclick="viewTask({{ $task->id }})">
                                    <h6 class="task-title">{{ $task->title }}</h6>
                                    @if($task->description)
                                        <p class="text-muted small mb-2">{{ Str::limit($task->description, 100) }}</p>
                                    @endif
                                    <div class="task-meta">
                                        @if($task->assignedUser)
                                            <div class="task-assignee">
                                                <div class="assignee-avatar">
                                                    {{ strtoupper(substr($task->assignedUser->name, 0, 1)) }}
                                                </div>
                                                <span class="small">{{ $task->assignedUser->name }}</span>
                                            </div>
                                        @else
                                            <span class="small text-muted">Sin asignar</span>
                                        @endif
                                        @if($task->due_date)
                                            <div class="task-due-date {{ $task->due_date->isPast() ? 'overdue' : '' }}">
                                                <i class="fas fa-calendar-alt"></i>
                                                {{ $task->due_date->format('d M') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="add-task-btn" onclick="showCreateTaskModal('todo')">
                        <i class="fas fa-plus"></i>
                        Agregar tarea
                    </button>
                </div>

                <!-- In Progress Column -->
                <div class="kanban-column">
                    <div class="column-header">
                        <h5 class="column-title">
                            <i class="fas fa-circle text-primary"></i>
                            En Progreso
                        </h5>
                        <span class="task-count">{{ $project->tasks->where('status', 'in_progress')->count() }}</span>
                    </div>
                    <div class="tasks-container" data-status="in_progress">
                        @foreach($project->tasks->where('status', 'in_progress')->sortBy('order') as $task)
                            <div class="task-card" draggable="true" data-task-id="{{ $task->id }}">
                                <div class="task-priority priority-{{ $task->priority }}"></div>
                                <div class="task-actions">
                                    <button onclick="editTask({{ $task->id }})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteTask({{ $task->id }})" class="delete-btn" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="task-content" onclick="viewTask({{ $task->id }})">
                                    <h6 class="task-title">{{ $task->title }}</h6>
                                    @if($task->description)
                                        <p class="text-muted small mb-2">{{ Str::limit($task->description, 100) }}</p>
                                    @endif
                                    <div class="task-meta">
                                        @if($task->assignedUser)
                                            <div class="task-assignee">
                                                <div class="assignee-avatar">
                                                    {{ strtoupper(substr($task->assignedUser->name, 0, 1)) }}
                                                </div>
                                                <span class="small">{{ $task->assignedUser->name }}</span>
                                            </div>
                                        @else
                                            <span class="small text-muted">Sin asignar</span>
                                        @endif
                                        @if($task->due_date)
                                            <div class="task-due-date {{ $task->due_date->isPast() ? 'overdue' : '' }}">
                                                <i class="fas fa-calendar-alt"></i>
                                                {{ $task->due_date->format('d M') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="add-task-btn" onclick="showCreateTaskModal('in_progress')">
                        <i class="fas fa-plus"></i>
                        Agregar tarea
                    </button>
                </div>

                <!-- Done Column -->
                <div class="kanban-column">
                    <div class="column-header">
                        <h5 class="column-title">
                            <i class="fas fa-circle text-success"></i>
                            Completado
                        </h5>
                        <span class="task-count">{{ $project->tasks->where('status', 'done')->count() }}</span>
                    </div>
                    <div class="tasks-container" data-status="done">
                        @foreach($project->tasks->where('status', 'done')->sortBy('order') as $task)
                            <div class="task-card" draggable="true" data-task-id="{{ $task->id }}">
                                <div class="task-priority priority-{{ $task->priority }}"></div>
                                <div class="task-content">
                                    <h6 class="task-title">{{ $task->title }}</h6>
                                    @if($task->description)
                                        <p class="text-muted small mb-2">{{ Str::limit($task->description, 100) }}</p>
                                    @endif
                                    <div class="task-meta">
                                        @if($task->assignedUser)
                                            <div class="task-assignee">
                                                <div class="assignee-avatar">
                                                    {{ strtoupper(substr($task->assignedUser->name, 0, 1)) }}
                                                </div>
                                                <span class="small">{{ $task->assignedUser->name }}</span>
                                            </div>
                                        @else
                                            <span class="small text-muted">Sin asignar</span>
                                        @endif
                                        @if($task->due_date)
                                            <div class="task-due-date {{ $task->due_date->isPast() ? 'overdue' : '' }}">
                                                <i class="fas fa-calendar-alt"></i>
                                                {{ $task->due_date->format('d M') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="add-task-btn" onclick="showCreateTaskModal('done')">
                        <i class="fas fa-plus"></i>
                        Agregar tarea
                    </button>
                </div>
            </div>
        </div>

        <!-- Members Tab -->
        <div class="tab-pane fade" id="members">
    <div class="project-members-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-users text-primary"></i>
                Miembros del Equipo ({{ $project->members->count() }})
            </h5>
            
            @if($project->creator_id === Auth::id() || 
                ($project->members->where('id', Auth::id())->first() && 
                 $project->members->where('id', Auth::id())->first()->pivot->role === 'coordinator'))
                <a href="{{ route('projects.members', $project) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-user-cog"></i> Gestionar Miembros
                </a>
            @endif
        </div>
        
        <div class="members-grid">
            @foreach($project->members as $member)
                <div class="member-card">
                    <div class="member-avatar">
                        @if($member->avatar)
                            <img src="{{ asset('storage/' . $member->avatar) }}" alt="{{ $member->name }}" class="avatar-img">
                        @else
                            <div class="avatar-placeholder">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    
                    <div class="member-info">
                        <div class="member-name">{{ $member->name }}</div>
                        <div class="member-email">{{ $member->email }}</div>
                        <div class="member-role">
                            @if($member->pivot->role === 'coordinator')
                                <span class="role-badge coordinator">
                                    <i class="fas fa-crown"></i> Coordinador
                                </span>
                            @else
                                <span class="role-badge member">
                                    <i class="fas fa-user"></i> Miembro
                                </span>
                            @endif
                            
                            @if($member->id === $project->creator_id)
                                <span class="role-badge creator">
                                    <i class="fas fa-star"></i> Creador
                                </span>
                            @endif
                        </div>
                        <div class="member-joined">
                            Se unió: {{ Carbon\Carbon::parse($member->pivot->joined_at)->format('d/m/Y') }}
                        </div>
                    </div>
                    
                    @if($member->id === Auth::id())
                        <div class="member-badge-self">
                            <span class="badge bg-success">Tú</span>
                        </div>
                    @endif
                </div>
            @endforeach
            
            @if($project->members->count() === 0)
                <div class="no-members-message">
                    <i class="fas fa-user-plus text-muted mb-2"></i>
                    <p class="text-muted mb-0">No hay miembros adicionales en este proyecto</p>
                    @if($project->creator_id === Auth::id())
                        <a href="{{ route('projects.members', $project) }}" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-plus"></i> Agregar Miembros
                        </a>
                    @endif
                </div>
            @endif
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
                    <div id="taskErrors" class="alert alert-danger d-none"></div>
                    
                    <div class="mb-3">
                        <label for="taskTitle" class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="taskTitle" name="title" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="taskDescription" class="form-label">Descripción</label>
                        <textarea class="form-control" id="taskDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskPriority" class="form-label">Prioridad <span class="text-danger">*</span></label>
                                <select class="form-select" id="taskPriority" name="priority" required>
                                    <option value="low">Baja</option>
                                    <option value="medium" selected>Media</option>
                                    <option value="high">Alta</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskDueDate" class="form-label">Fecha de entrega</label>
                                <input type="date" class="form-control" id="taskDueDate" name="due_date" 
                                       min="{{ date('Y-m-d') }}">
                                <div class="invalid-feedback"></div>
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
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="createTaskBtn">
                        <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                        Crear Tarea
                    </button>
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
const taskModal = new bootstrap.Modal(document.getElementById('createTaskModal'));

// Función para mostrar el modal de crear tarea
function showCreateTaskModal(status) {
    // Limpiar el formulario
    document.getElementById('createTaskForm').reset();
    document.getElementById('taskStatus').value = status;
    
    // Limpiar errores previos
    document.getElementById('taskErrors').classList.add('d-none');
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    // Mostrar el modal
    taskModal.show();
}

// Manejar el envío del formulario de crear tarea
document.getElementById('createTaskForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = document.getElementById('createTaskBtn');
    const spinner = submitBtn.querySelector('.spinner-border');
    const errorDiv = document.getElementById('taskErrors');
    
    // Limpiar errores previos
    errorDiv.classList.add('d-none');
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    // Mostrar loading
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });
        
        const responseText = await response.text();
        console.log('Response status:', response.status);
        console.log('Response text:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Error parsing JSON:', parseError);
            console.error('Raw response:', responseText);
            errorDiv.textContent = 'Error del servidor. La respuesta no es válida.';
            errorDiv.classList.remove('d-none');
            return;
        }
        
        if (response.ok && data.success) {
            // Éxito: recargar la página
            window.location.reload();
        } else {
            // Manejar errores de validación
            console.error('Error response:', data);
            
            if (data.errors) {
                let errorMessages = [];
                
                // Errores de campos específicos
                for (const [field, messages] of Object.entries(data.errors)) {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input && field !== 'general') {
                        input.classList.add('is-invalid');
                        const feedback = input.parentElement.querySelector('.invalid-feedback');
                        if (feedback) {
                            feedback.textContent = messages[0];
                        }
                    }
                    
                    // Agregar al mensaje general
                    if (field === 'general' || !input) {
                        errorMessages.push(...messages);
                    }
                }
                
                // Mostrar errores generales
                if (errorMessages.length > 0) {
                    errorDiv.innerHTML = errorMessages.join('<br>');
                    errorDiv.classList.remove('d-none');
                }
            } else {
                // Si no hay estructura de errores, mostrar el mensaje o un genérico
                errorDiv.textContent = data.message || 'Error al crear la tarea';
                errorDiv.classList.remove('d-none');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        errorDiv.textContent = 'Error de conexión. Por favor intenta nuevamente.';
        errorDiv.classList.remove('d-none');
    } finally {
        // Ocultar loading
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
    }
});

// Inicializar Sortable.js para cada columna
document.addEventListener('DOMContentLoaded', function() {
    const columns = document.querySelectorAll('.tasks-container');
    
    columns.forEach(column => {
        new Sortable(column, {
            group: 'tasks',
            animation: 150,
            ghostClass: 'task-ghost',
            dragClass: 'dragging',
            onStart: function(evt) {
                draggedTask = evt.item;
            },
            onEnd: async function(evt) {
                const taskId = evt.item.dataset.taskId;
                const newStatus = evt.to.dataset.status;
                const newOrder = evt.newIndex;
                
                // Actualizar el estado de la tarea
                try {
                    const response = await fetch(`/api/tasks/${taskId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            status: newStatus,
                            order: newOrder
                        })
                    });
                    
                    if (!response.ok) {
                        throw new Error('Error al actualizar la tarea');
                    }
                    
                    // Actualizar contadores
                    updateTaskCounts();
                    
                } catch (error) {
                    console.error('Error:', error);
                    // Revertir el cambio si hay error
                    evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                    alert('Error al actualizar la tarea. Por favor intenta nuevamente.');
                }
            }
        });
    });
});

// Función para actualizar los contadores de tareas
function updateTaskCounts() {
    document.querySelectorAll('.kanban-column').forEach(column => {
        const status = column.querySelector('.tasks-container').dataset.status;
        const count = column.querySelectorAll('.task-card').length;
        column.querySelector('.task-count').textContent = count;
    });
}

// Función para ver tarea individual
function viewTask(taskId) {
    window.location.href = `/tasks/${taskId}`;
}

// Función para editar tarea
function editTask(taskId) {
    event.stopPropagation(); // Evitar que se active el click del contenedor
    window.location.href = `/tasks/${taskId}/edit`;
}

// Función para eliminar tarea
function deleteTask(taskId) {
    event.stopPropagation(); // Evitar que se active el click del contenedor
    if (confirm('¿Estás seguro de que deseas eliminar esta tarea?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/tasks/${taskId}`;
        form.innerHTML = `
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Drag and drop visual feedback
document.querySelectorAll('.tasks-container').forEach(container => {
    container.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('drag-over');
    });
    
    container.addEventListener('dragleave', function() {
        this.classList.remove('drag-over');
    });
    
    container.addEventListener('drop', function() {
        this.classList.remove('drag-over');
    });
});
</script>
@endpush