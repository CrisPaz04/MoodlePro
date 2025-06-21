@extends('layouts.app')

@section('title', 'Dashboard - MoodlePro')

@section('content')
<div class="container-fluid py-4">
    <!-- Header del Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">¡Bienvenido, {{ Auth::user()->name }}! 👋</h1>
                    <p class="mb-0 text-muted">Aquí tienes un resumen de tu actividad académica</p>
                </div>
                <div>
                    <a href="{{ route('projects.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nuevo Proyecto
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Métricas -->
    <div class="row mb-4">
        <!-- Total Proyectos -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Mis Proyectos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-projects">
                                {{ $stats['total_projects'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-folder fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proyectos Activos -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Proyectos Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="active-projects">
                                {{ $stats['active_projects'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-play-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tareas Pendientes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Tareas Asignadas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-tasks">
                                {{ $stats['total_tasks'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tareas Vencidas -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Tareas Vencidas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="overdue-tasks">
                                {{ $stats['overdue_tasks'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos y Contenido Principal -->
    <div class="row">
        <!-- Progreso de Tareas -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Progreso de Tareas</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="tasksProgressChart" style="height: 320px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proyectos Recientes -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Proyectos Recientes</h6>
                    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-primary">Ver Todos</a>
                </div>
                <div class="card-body">
                    @forelse($recentProjects as $project)
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3">
                                <div class="icon-circle bg-{{ $project->status == 'active' ? 'success' : 'secondary' }}">
                                    <i class="fas fa-folder text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small text-gray-500">{{ $project->created_at->diffForHumans() }}</div>
                                <a href="{{ route('projects.show', $project) }}" class="font-weight-bold">
                                    {{ $project->title }}
                                </a>
                                <div class="small text-gray-500">
                                    Vence: {{ $project->deadline->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-folder-open fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No tienes proyectos aún</p>
                            <a href="{{ route('projects.create') }}" class="btn btn-primary">Crear mi primer proyecto</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Actividad Reciente -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actividad Reciente</h6>
                </div>
                <div class="card-body">
                    <div id="recent-activity">
                        <!-- Se carga con JavaScript -->
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Próximos Deadlines -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Próximos Deadlines</h6>
                </div>
                <div class="card-body">
                    @forelse($upcomingDeadlines as $task)
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3">
                                <div class="icon-circle bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'info') }}">
                                    <i class="fas fa-clock text-white"></i>
                                </div>
                            </div>
                            <div>
                                <div class="font-weight-bold">{{ $task->title }}</div>
                                <div class="small text-gray-500">
                                    {{ $task->project->title }} • Vence: {{ $task->due_date->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-check fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No hay deadlines próximos</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de progreso de tareas
    const ctx = document.getElementById('tasksProgressChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Completadas', 'En Progreso', 'Pendientes'],
            datasets: [{
                data: [{{ $stats['completed_tasks'] }}, {{ $stats['in_progress_tasks'] ?? 0 }}, {{ $stats['todo_tasks'] ?? 0 }}],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Cargar actividad reciente
    fetch('/dashboard/api/activity')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recent-activity');
            if (data.length === 0) {
                container.innerHTML = '<div class="text-center py-4"><i class="fas fa-history fa-3x text-gray-300 mb-3"></i><p class="text-muted">No hay actividad reciente</p></div>';
                return;
            }
            
            container.innerHTML = data.map(item => `
                <div class="d-flex align-items-center mb-3">
                    <div class="mr-3">
                        <div class="icon-circle bg-primary">
                            <i class="fas fa-${item.type === 'task' ? 'tasks' : 'comment'} text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="font-weight-bold">${item.title}</div>
                        <div class="small text-gray-500">${new Date(item.date).toLocaleDateString()}</div>
                    </div>
                </div>
            `).join('');
        })
        .catch(() => {
            document.getElementById('recent-activity').innerHTML = '<div class="text-center py-4"><p class="text-muted">Error cargando actividad</p></div>';
        });
});
</script>

<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }

.icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endsection