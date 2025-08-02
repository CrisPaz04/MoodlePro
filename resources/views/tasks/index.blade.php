@extends('layouts.app')

@section('title', 'Mis Tareas - MoodlePro')

@section('content')
<div class="tasks-page">
    <!-- Header Mejorado -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Mis Tareas</h1>
                            <p class="page-subtitle">Gestiona todas tus tareas asignadas en un solo lugar</p>
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="header-actions">
                        <button class="filter-btn active" onclick="filterTasks('all')" data-filter="all">
                            <i class="fas fa-list me-2"></i>Todas
                        </button>
                        <button class="filter-btn" onclick="filterTasks('pending')" data-filter="pending">
                            <i class="fas fa-clock me-2"></i>Pendientes
                        </button>
                        <button class="filter-btn" onclick="filterTasks('completed')" data-filter="completed">
                            <i class="fas fa-check me-2"></i>Completadas
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Estadísticas -->
        <div class="stats-section">
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card primary">
                        <div class="stat-content">
                            <div class="stat-number">{{ $tasks->count() }}</div>
                            <div class="stat-label">Total de Tareas</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card warning">
                        <div class="stat-content">
                            <div class="stat-number">{{ $tasks->whereIn('status', ['todo', 'in_progress'])->count() }}</div>
                            <div class="stat-label">Pendientes</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card success">
                        <div class="stat-content">
                            <div class="stat-number">{{ $tasks->where('status', 'done')->count() }}</div>
                            <div class="stat-label">Completadas</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card danger">
                        <div class="stat-content">
                            <div class="stat-number">{{ $tasks->where('due_date', '<', now())->where('status', '!=', 'done')->count() }}</div>
                            <div class="stat-label">Vencidas</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros Avanzados -->
        <div class="filters-section">
            <div class="filters-card">
                <div class="filters-header">
                    <h5><i class="fas fa-filter me-2"></i>Filtros</h5>
                </div>
                <div class="filters-content">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="filter-label">Buscar tareas</label>
                            <div class="search-input">
                                <input type="text" class="form-control" id="searchTasks" placeholder="Escribir para buscar...">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="filter-label">Proyecto</label>
                            <select class="form-select" id="filterProject">
                                <option value="">Todos los proyectos</option>
                                @foreach($allUserProjects as $project)
                                    <option value="{{ $project->id }}" data-task-count="{{ $tasks->where('project_id', $project->id)->count() }}">
                                        {{ $project->title }} ({{ $tasks->where('project_id', $project->id)->count() }} tareas)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="filter-label">Prioridad</label>
                            <select class="form-select" id="filterPriority">
                                <option value="">Todas las prioridades</option>
                                <option value="high">🔴 Alta</option>
                                <option value="medium">🟡 Media</option>
                                <option value="low">🟢 Baja</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="filter-label">Estado</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">Todos los estados</option>
                                <option value="todo">📋 Por hacer</option>
                                <option value="in_progress">⚡ En progreso</option>
                                <option value="done">✅ Completada</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensaje "Estás al día" -->
        <div id="upToDateMessage" class="up-to-date-message" style="display: none;">
            <div class="up-to-date-card">
                <div class="up-to-date-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>¡Estás al día!</h3>
                <p>No tienes tareas pendientes en este proyecto. ¡Excelente trabajo!</p>
                <div class="up-to-date-actions">
                    <button class="btn btn-primary" onclick="clearFilters()">
                        <i class="fas fa-list me-2"></i>Ver todas las tareas
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de Tareas -->
        <div class="tasks-section">
            @if($tasks->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3>No tienes tareas asignadas</h3>
                    <p>Las tareas que te asignen aparecerán aquí automáticamente</p>
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
                                <div class="task-status-badge">
                                    @if($task->status == 'todo')
                                        <span class="status-badge todo">📋 Por hacer</span>
                                    @elseif($task->status == 'in_progress')
                                        <span class="status-badge in-progress">⚡ En progreso</span>
                                    @else
                                        <span class="status-badge done">✅ Completada</span>
                                    @endif
                                </div>
                                <div class="task-priority">
                                    @if($task->priority == 'high')
                                        <span class="priority-badge high">🔴 Alta</span>
                                    @elseif($task->priority == 'medium')
                                        <span class="priority-badge medium">🟡 Media</span>
                                    @else
                                        <span class="priority-badge low">🟢 Baja</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Task Content -->
                            <div class="task-content">
                                <h4 class="task-title">{{ $task->title }}</h4>
                                @if($task->description)
                                    <p class="task-description">{{ Str::limit($task->description, 100) }}</p>
                                @endif
                                
                                <div class="task-meta">
                                    <div class="task-project">
                                        <i class="fas fa-folder me-1"></i>
                                        <span>{{ $task->project->title }}</span>
                                    </div>
                                    @if($task->due_date)
                                        <div class="task-due-date {{ $task->due_date < now() && $task->status != 'done' ? 'overdue' : '' }}">
                                            <i class="fas fa-calendar me-1"></i>
                                            <span>{{ $task->due_date->format('d M Y') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Task Footer -->
                            <div class="task-footer">
                                <div class="task-assignee">
                                    <div class="assignee-avatar">
                                        <span>{{ substr($task->assignedUser->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                    <span class="assignee-name">{{ $task->assignedUser->name ?? 'Sin asignar' }}</span>
                                </div>
                                
                                <div class="task-actions">
                                    @if($task->status != 'done')
                                        <button class="action-btn complete" onclick="markTaskComplete({{ $task->id }})" title="Marcar como completada">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                    <a href="{{ route('tasks.show', $task) }}" class="action-btn view" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($task->creator_id == auth()->id() || $task->assigned_to == auth()->id())
                                        <a href="{{ route('tasks.edit', $task) }}" class="action-btn edit" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<style>
/* Variables del tema morado */
:root {
    --primary-purple: #667eea;
    --secondary-purple: #764ba2;
    --light-purple: #f3f4ff;
    --dark-purple: #4c63d2;
    --success-green: #1cc88a;
    --warning-yellow: #f6c23e;
    --danger-red: #e74c3c;
    --white: #ffffff;
    --gray-50: #f8f9fc;
    --gray-100: #f1f3f6;
    --gray-200: #e3e6f0;
    --gray-300: #d1d3e2;
    --gray-400: #a2aebb;
    --gray-500: #858796;
    --gray-600: #5a5c69;
    --gray-700: #3d3d4f;
    --gray-800: #2e3440;
    --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
    --shadow-md: 0 4px 8px rgba(0,0,0,0.12);
    --shadow-lg: 0 8px 24px rgba(0,0,0,0.15);
}

.tasks-page {
    background: var(--gray-50);
    min-height: 100vh;
}

/* Header */
.page-header {
    background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
    color: white;
    padding: 2rem 0;
    margin-bottom: 2rem;
    border-radius: 0 0 20px 20px;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
}

.page-subtitle {
    margin: 0;
    opacity: 1;
    font-size: 1.1rem;
    color: white;
}

.header-actions {
    display: flex;
    gap: 0.5rem;
}

.filter-btn {
    background: rgba(255, 255, 255, 0.15);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s;
    cursor: pointer;
}

.filter-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-1px);
}

.filter-btn.active {
    background: white;
    color: var(--primary-purple);
    border-color: white;
}

/* Stats Cards */
.stats-section {
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    border-left: 4px solid;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: transform 0.3s, box-shadow 0.3s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-card.primary { border-left-color: var(--primary-purple); }
.stat-card.warning { border-left-color: var(--warning-yellow); }
.stat-card.success { border-left-color: var(--success-green); }
.stat-card.danger { border-left-color: var(--danger-red); }

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--gray-800);
    margin: 0;
}

.stat-label {
    color: var(--gray-600);
    font-weight: 500;
    margin: 0;
}

.stat-icon {
    font-size: 2rem;
    opacity: 0.3;
}

.stat-card.primary .stat-icon { color: var(--primary-purple); }
.stat-card.warning .stat-icon { color: var(--warning-yellow); }
.stat-card.success .stat-icon { color: var(--success-green); }
.stat-card.danger .stat-icon { color: var(--danger-red); }

/* Filters */
.filters-section {
    margin-bottom: 2rem;
}

.filters-card {
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.filters-header {
    background: var(--light-purple);
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}

.filters-header h5 {
    margin: 0;
    color: var(--primary-purple);
    font-weight: 600;
}

.filters-content {
    padding: 1.5rem;
}

.filter-label {
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
    display: block;
}

.search-input {
    position: relative;
}

.search-input input {
    padding-right: 2.5rem;
    border: 2px solid var(--gray-200);
    border-radius: 8px;
    transition: border-color 0.3s;
}

.search-input input:focus {
    border-color: var(--primary-purple);
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.search-input i {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-400);
}

.form-select {
    border: 2px solid var(--gray-200);
    border-radius: 8px;
    transition: border-color 0.3s;
}

.form-select:focus {
    border-color: var(--primary-purple);
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Mensaje "Estás al día" */
.up-to-date-message {
    margin-bottom: 2rem;
}

.up-to-date-card {
    background: linear-gradient(135deg, var(--success-green) 0%, #16a085 100%);
    color: white;
    padding: 3rem;
    border-radius: 16px;
    text-align: center;
    box-shadow: var(--shadow-lg);
}

.up-to-date-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.9;
}

.up-to-date-card h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.up-to-date-card p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 2rem;
}

.up-to-date-actions .btn {
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid white;
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.up-to-date-actions .btn:hover {
    background: white;
    color: var(--success-green);
}

/* Tasks Grid */
.tasks-section {
    margin-bottom: 2rem;
}

.tasks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.task-card {
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s;
    border: 2px solid transparent;
    overflow: hidden;
}

.task-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-purple);
}

.task-header {
    padding: 1rem 1.5rem 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-badge.todo {
    background: var(--gray-100);
    color: var(--gray-600);
}

.status-badge.in-progress {
    background: #e3f2fd;
    color: #1976d2;
}

.status-badge.done {
    background: #e8f5e8;
    color: var(--success-green);
}

.priority-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.priority-badge.high {
    background: #ffebee;
    color: var(--danger-red);
}

.priority-badge.medium {
    background: #fff8e1;
    color: #f57c00;
}

.priority-badge.low {
    background: #e8f5e8;
    color: var(--success-green);
}

.task-content {
    padding: 1rem 1.5rem;
}

.task-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--gray-800);
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.task-description {
    color: var(--gray-600);
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.task-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.task-project,
.task-due-date {
    display: flex;
    align-items: center;
    font-size: 0.85rem;
    color: var(--gray-500);
}

.task-due-date.overdue {
    color: var(--danger-red);
    font-weight: 600;
}

.task-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--gray-100);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.task-assignee {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.assignee-avatar {
    width: 32px;
    height: 32px;
    background: var(--primary-purple);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.85rem;
}

.assignee-name {
    font-size: 0.85rem;
    color: var(--gray-600);
    font-weight: 500;
}

.task-actions {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    text-decoration: none;
    font-size: 0.85rem;
}

.action-btn.complete {
    background: var(--success-green);
    color: white;
}

.action-btn.complete:hover {
    background: #16a085;
    transform: scale(1.1);
}

.action-btn.view {
    background: var(--gray-100);
    color: var(--gray-600);
}

.action-btn.view:hover {
    background: var(--primary-purple);
    color: white;
}

.action-btn.edit {
    background: var(--warning-yellow);
    color: white;
}

.action-btn.edit:hover {
    background: #e0a800;
    transform: scale(1.1);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 1rem;
    background: white;
    border-radius: 16px;
    box-shadow: var(--shadow-sm);
}

.empty-icon {
    font-size: 4rem;
    color: var(--gray-300);
    margin-bottom: 1.5rem;
}

.empty-state h3 {
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: var(--gray-500);
    margin-bottom: 2rem;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Responsive */
@media (max-width: 768px) {
    .tasks-grid {
        grid-template-columns: 1fr;
    }
    
    .header-actions {
        flex-direction: column;
        width: 100%;
        margin-top: 1rem;
    }
    
    .filter-btn {
        text-align: center;
    }
    
    .filters-content .row {
        row-gap: 1rem;
    }
    
    .task-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .task-footer {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .task-actions {
        align-self: stretch;
        justify-content: center;
    }
}
</style>

<script>
// Variables globales
let allTasks = @json($tasks);
let currentFilter = 'all';

// Filtrado principal de tareas
function filterTasks(status) {
    currentFilter = status;
    
    // Actualizar botones activos
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-filter="${status}"]`).classList.add('active');
    
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
    
    checkUpToDateMessage();
}

// Búsqueda de tareas
document.getElementById('searchTasks').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const cards = document.querySelectorAll('.task-card');
    
    cards.forEach(card => {
        const title = card.dataset.title;
        card.style.display = title.includes(searchTerm) ? 'block' : 'none';
    });
    
    checkUpToDateMessage();
});

// Filtro por proyecto (CON MENSAJE "ESTÁS AL DÍA")
document.getElementById('filterProject').addEventListener('change', function() {
    const projectId = this.value;
    const selectedOption = this.options[this.selectedIndex];
    const taskCount = selectedOption.dataset.taskCount || 0;
    const cards = document.querySelectorAll('.task-card');
    
    if (projectId) {
        cards.forEach(card => {
            card.style.display = card.dataset.project === projectId ? 'block' : 'none';
        });
        
        // Mostrar mensaje "Estás al día" si el proyecto no tiene tareas
        if (parseInt(taskCount) === 0) {
            showUpToDateMessage();
        } else {
            hideUpToDateMessage();
        }
    } else {
        cards.forEach(card => {
            card.style.display = 'block';
        });
        hideUpToDateMessage();
    }
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
    
    checkUpToDateMessage();
});

// Filtro por estado
document.getElementById('filterStatus').addEventListener('change', function() {
    const status = this.value;
    const cards = document.querySelectorAll('.task-card');
    
    cards.forEach(card => {
        if (!status || card.dataset.status === status) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
    
    checkUpToDateMessage();
});

// Función para mostrar mensaje "Estás al día"
function showUpToDateMessage() {
    document.getElementById('upToDateMessage').style.display = 'block';
    document.getElementById('tasksContainer').style.display = 'none';
}

// Función para ocultar mensaje "Estás al día"
function hideUpToDateMessage() {
    document.getElementById('upToDateMessage').style.display = 'none';
    document.getElementById('tasksContainer').style.display = 'grid';
}

// Verificar si mostrar mensaje basado en tareas visibles
function checkUpToDateMessage() {
    const visibleCards = Array.from(document.querySelectorAll('.task-card')).filter(card => 
        card.style.display !== 'none'
    );
    
    if (visibleCards.length === 0 && document.getElementById('filterProject').value) {
        showUpToDateMessage();
    } else {
        hideUpToDateMessage();
    }
}

// Limpiar todos los filtros
function clearFilters() {
    document.getElementById('searchTasks').value = '';
    document.getElementById('filterProject').value = '';
    document.getElementById('filterPriority').value = '';
    document.getElementById('filterStatus').value = '';
    
    // Mostrar todas las tareas
    document.querySelectorAll('.task-card').forEach(card => {
        card.style.display = 'block';
    });
    
    // Resetear filtro principal
    filterTasks('all');
    
    hideUpToDateMessage();
}

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
                // Mostrar notificación de éxito
                showNotification('¡Tarea completada exitosamente!', 'success');
                
                // Recargar página después de un momento
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Error al completar la tarea', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al completar la tarea', 'error');
        });
    }
}

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Agregar estilos
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? 'var(--success-green)' : 'var(--danger-red)'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: var(--shadow-lg);
        z-index: 9999;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Mostrar notificación
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Ocultar después de 3 segundos
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Inicializar página
document.addEventListener('DOMContentLoaded', function() {
    hideUpToDateMessage();
});
</script>
@endsection