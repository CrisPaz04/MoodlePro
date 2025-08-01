@extends('layouts.app')

@section('title', 'Dashboard - MoodlePro')

@push('styles')
<style>
    /* Dashboard Specific Styles */
    .stats-card {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        transition: all 0.3s;
        height: 100%;
    }

    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.2);
    }

    .stats-card .card-body {
        padding: 1.5rem;
    }

    .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        flex-shrink: 0;
    }

    .stats-info h4 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        color: #2e3440;
    }

    .stats-label {
        font-size: 0.875rem;
        color: #858796;
        margin: 0;
        font-weight: 600;
        text-transform: uppercase;
    }

    .progress-thin {
        height: 6px;
        border-radius: 3px;
        margin-top: 0.5rem;
    }

    .project-card {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        transition: all 0.3s;
        cursor: pointer;
        height: 100%;
    }

    .project-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.2);
    }

    .project-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
    }

    .project-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #2e3440;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .project-status {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-active {
        background-color: rgba(28, 200, 138, 0.2);
        color: #0f6848;
    }

    .status-planning {
        background-color: rgba(246, 194, 62, 0.2);
        color: #993d00;
    }

    .status-completed {
        background-color: rgba(78, 115, 223, 0.2);
        color: #2e59d9;
    }

    .project-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.875rem;
        color: #858796;
        margin-bottom: 1rem;
    }

    .project-meta-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .task-item {
        padding: 1rem;
        border-radius: 0.5rem;
        background-color: #f8f9fc;
        margin-bottom: 0.75rem;
        transition: all 0.3s;
        cursor: pointer;
    }

    .task-item:hover {
        background-color: #eaecf4;
        transform: translateX(5px);
    }

    .task-priority {
        width: 4px;
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
        border-radius: 0.5rem 0 0 0.5rem;
    }

    .priority-high {
        background-color: #e74a3b;
    }

    .priority-medium {
        background-color: #f6c23e;
    }

    .priority-low {
        background-color: #1cc88a;
    }

    .task-title {
        font-weight: 600;
        color: #2e3440;
        margin-bottom: 0.25rem;
    }

    .task-project {
        font-size: 0.875rem;
        color: #858796;
    }

    .task-due {
        font-size: 0.75rem;
        color: #858796;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .task-overdue {
        color: #e74a3b;
        font-weight: 600;
    }

    .activity-item {
        display: flex;
        align-items: start;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid #e3e6f0;
        transition: all 0.3s;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-item:hover {
        background-color: #f8f9fc;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .activity-content {
        flex: 1;
    }

    .activity-title {
        font-weight: 600;
        color: #2e3440;
        margin-bottom: 0.25rem;
        font-size: 0.875rem;
    }

    .activity-description {
        font-size: 0.8125rem;
        color: #858796;
        margin-bottom: 0.25rem;
    }

    .activity-time {
        font-size: 0.75rem;
        color: #b7b9cc;
    }

    .chart-container {
        position: relative;
        height: 300px;
        margin-top: 1rem;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #858796;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    .section-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2e3440;
        margin: 0;
    }

    .section-action {
        font-size: 0.875rem;
        color: #4e73df;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s;
    }

    .section-action:hover {
        color: #2e59d9;
        text-decoration: underline;
    }
</style>
@endpush

@section('content')
<div class="page-heading">
    <h1 class="page-title">¡Bienvenido de vuelta, {{ Auth::user()->name }}! 👋</h1>
    <p class="page-subtitle">Aquí está tu resumen de actividad académica de hoy</p>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <!-- Total Projects -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-primary bg-gradient">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <p class="stats-label">Mis Proyectos</p>
                        <h4>{{ $stats['total_projects'] }}</h4>
                        <div class="progress progress-thin">
                            <div class="progress-bar bg-primary" style="width: {{ $stats['completion_rate'] }}%"></div>
                        </div>
                        <small class="text-muted">{{ $stats['completion_rate'] }}% completados</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Projects -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-success bg-gradient">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <p class="stats-label">Proyectos Activos</p>
                        <h4>{{ $stats['active_projects'] }}</h4>
                        <div class="progress progress-thin">
                            <div class="progress-bar bg-success" style="width: {{ $stats['active_projects'] > 0 ? ($stats['active_projects'] / $stats['total_projects']) * 100 : 0 }}%"></div>
                        </div>
                        <small class="text-muted">En progreso</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Tasks -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-info bg-gradient">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <p class="stats-label">Tareas Pendientes</p>
                        <h4>{{ $stats['todo_tasks'] + $stats['in_progress_tasks'] }}</h4>
                        <div class="d-flex gap-2 mt-2">
                            <small class="text-warning">{{ $stats['todo_tasks'] }} por hacer</small>
                            <small class="text-info">{{ $stats['in_progress_tasks'] }} en progreso</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Tasks -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-warning bg-gradient">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <p class="stats-label">Tareas Vencidas</p>
                        <h4>{{ $stats['overdue_tasks'] }}</h4>
                        @if($stats['overdue_tasks'] > 0)
                            <small class="text-danger">Requieren atención inmediata</small>
                        @else
                            <small class="text-success">¡Estás al día!</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column -->
    <div class="col-xl-8">
        <!-- Task Progress Chart -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="m-0 font-weight-bold text-primary">Progreso de Tareas por Proyecto</h5>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="updateChart('task_progress')">Progreso de Tareas</a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateChart('productivity')">Productividad</a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateChart('workload')">Carga de Trabajo</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Projects -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <h5 class="section-title m-0">Proyectos Recientes</h5>
                    <a href="{{ route('projects.index') }}" class="section-action">Ver todos →</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse($recentProjects as $project)
                        <div class="col-md-6">
                            <div class="project-card" onclick="window.location='{{ route('projects.show', $project) }}'">
                                <div class="card-body">
                                    <div class="project-header">
                                        <h6 class="project-title">{{ $project->title }}</h6>
                                        <span class="project-status status-{{ $project->status }}">
                                            {{ ucfirst($project->status) }}
                                        </span>
                                    </div>
                                    
                                    <div class="project-meta">
                                        <div class="project-meta-item">
                                            <i class="fas fa-calendar"></i>
                                            {{ $project->deadline->format('d M') }}
                                        </div>
                                        <div class="project-meta-item">
                                            <i class="fas fa-tasks"></i>
                                            {{ $project->tasks->count() }} tareas
                                        </div>
                                    </div>
                                    
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $project->task_completion }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $project->task_completion }}% completado</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="fas fa-folder-open"></i>
                                <p>No tienes proyectos recientes</p>
                                <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-2"></i>Crear Proyecto
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-xl-4">
        <!-- Upcoming Deadlines -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="m-0 font-weight-bold text-primary">Próximos Vencimientos</h5>
            </div>
            <div class="card-body p-0">
                @forelse($upcomingDeadlines as $task)
                    <div class="task-item position-relative">
                        <div class="task-priority priority-{{ $task->priority }}"></div>
                        <div class="ps-3">
                            <div class="task-title">{{ $task->title }}</div>
                            <div class="task-project">{{ $task->project->title }}</div>
                            <div class="task-due {{ $task->due_date->isPast() ? 'task-overdue' : '' }}">
                                <i class="fas fa-clock"></i>
                                @if($task->due_date->isToday())
                                    Hoy
                                @elseif($task->due_date->isTomorrow())
                                    Mañana
                                @else
                                    {{ $task->due_date->diffForHumans() }}
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-calendar-check"></i>
                        <p>No hay tareas próximas a vencer</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="m-0 font-weight-bold text-primary">Actividad Reciente</h5>
            </div>
            <div class="card-body p-0" id="activityFeed">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Variables globales
let mainChart;
let currentChartType = 'task_progress';

// Inicializar dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadMainChart('task_progress');
    loadRecentActivity();
    
    // Actualizar actividad cada 30 segundos
    setInterval(loadRecentActivity, 30000);
});

// Cargar gráfico principal
function loadMainChart(type) {
    fetch(`/api/dashboard/chart/${type}`)
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('mainChart').getContext('2d');
            
            // Destruir gráfico anterior si existe
            if (mainChart) {
                mainChart.destroy();
            }
            
            // Configuración según tipo de gráfico
            let chartConfig = {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    },
                    scales: {
                        x: {
                            stacked: type === 'task_progress',
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            stacked: type === 'task_progress',
                            beginAtZero: true
                        }
                    }
                }
            };
            
            // Ajustar tipo de gráfico según los datos
            if (type === 'productivity') {
                chartConfig.type = 'line';
                chartConfig.options.scales.x.stacked = false;
                chartConfig.options.scales.y.stacked = false;
            }
            
            mainChart = new Chart(ctx, chartConfig);
        })
        .catch(error => console.error('Error loading chart:', error));
}

// Actualizar gráfico
function updateChart(type) {
    currentChartType = type;
    loadMainChart(type);
}

// Cargar actividad reciente
function loadRecentActivity() {
    fetch('{{ route('api.dashboard.activity') }}?limit=10')
        .then(response => response.json())
        .then(activities => {
            const feedContainer = document.getElementById('activityFeed');
            
            if (activities.length === 0) {
                feedContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <p>No hay actividad reciente</p>
                    </div>
                `;
                return;
            }
            
            feedContainer.innerHTML = activities.map(activity => {
                const iconColors = {
                    task_created: 'bg-primary',
                    message_sent: 'bg-info',
                    resource_uploaded: 'bg-success',
                    project_updated: 'bg-warning'
                };
                
                return `
                    <div class="activity-item">
                        <div class="activity-icon ${iconColors[activity.type] || 'bg-secondary'} bg-gradient text-white">
                            <i class="fas fa-${activity.icon}"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">${activity.title}</div>
                            <div class="activity-description">${activity.description}</div>
                            <div class="activity-time">
                                <i class="fas fa-clock"></i>
                                ${formatTimeAgo(new Date(activity.date))}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        })
        .catch(error => {
            console.error('Error loading activity:', error);
            document.getElementById('activityFeed').innerHTML = `
                <div class="alert alert-danger m-3">
                    No tienes actividad reciente.
                </div>
            `;
        });
}

// Formatear tiempo relativo
function formatTimeAgo(date) {
    const seconds = Math.floor((new Date() - date) / 1000);
    
    const intervals = {
        año: 31536000,
        mes: 2592000,
        semana: 604800,
        día: 86400,
        hora: 3600,
        minuto: 60
    };
    
    for (const [unit, secondsInUnit] of Object.entries(intervals)) {
        const interval = Math.floor(seconds / secondsInUnit);
        if (interval >= 1) {
            return `hace ${interval} ${unit}${interval !== 1 ? 's' : ''}`;
        }
    }
    
    return 'hace un momento';
}

// Comentado: Auto-actualizar estadísticas cada minuto
// Si necesitas esta funcionalidad, agrega la ruta en routes/web.php
/*
setInterval(() => {
    fetch('{{ route('api.dashboard.chart', 'stats') }}')
        .then(response => response.json())
        .then(stats => {
            // Actualizar números en las tarjetas
            document.querySelectorAll('[data-stat]').forEach(element => {
                const stat = element.dataset.stat;
                if (stats[stat] !== undefined) {
                    element.textContent = stats[stat];
                }
            });
        })
        .catch(error => console.error('Error updating stats:', error));
}, 60000);
*/
</script>
@endpush