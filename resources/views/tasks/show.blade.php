@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Proyectos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.show', $task->project) }}">{{ $task->project->title }}</a></li>
            <li class="breadcrumb-item active">{{ $task->title }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Información principal de la tarea -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">{{ $task->title }}</h4>
                        <small class="text-muted">
                            <i class="fas fa-folder me-1"></i>
                            {{ $task->project->title }}
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        @if($task->created_by === Auth::id() || $task->project->creator_id === Auth::id())
                            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>Editar
                            </a>
                        @endif
                        
                        @if($task->created_by === Auth::id() || $task->project->creator_id === Auth::id())
                            <button class="btn btn-outline-danger btn-sm" onclick="deleteTask({{ $task->id }})">
                                <i class="fas fa-trash me-1"></i>Eliminar
                            </button>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <!-- Estado y prioridad -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Estado</h6>
                            <span class="badge fs-6 px-3 py-2 
                                @if($task->status === 'todo') bg-secondary
                                @elseif($task->status === 'in_progress') bg-warning text-dark
                                @else bg-success
                                @endif">
                                @if($task->status === 'todo') 
                                    <i class="fas fa-circle me-1"></i>Por hacer
                                @elseif($task->status === 'in_progress') 
                                    <i class="fas fa-play me-1"></i>En progreso
                                @else 
                                    <i class="fas fa-check me-1"></i>Completado
                                @endif
                            </span>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Prioridad</h6>
                            <span class="badge fs-6 px-3 py-2 
                                @if($task->priority === 'low') bg-info
                                @elseif($task->priority === 'medium') bg-warning text-dark
                                @else bg-danger
                                @endif">
                                @if($task->priority === 'low') 
                                    <i class="fas fa-arrow-down me-1"></i>Baja
                                @elseif($task->priority === 'medium') 
                                    <i class="fas fa-minus me-1"></i>Media
                                @else 
                                    <i class="fas fa-arrow-up me-1"></i>Alta
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Descripción -->
                    @if($task->description)
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Descripción</h6>
                            <div class="bg-light p-3 rounded">
                                {!! nl2br(e($task->description)) !!}
                            </div>
                        </div>
                    @endif

                    <!-- Fechas -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Fecha de creación</h6>
                            <p class="mb-0">
                                <i class="fas fa-calendar-plus me-2 text-muted"></i>
                                {{ $task->created_at->format('d/m/Y') }}
                                <small class="text-muted">({{ $task->created_at->diffForHumans() }})</small>
                            </p>
                        </div>
                        @if($task->due_date)
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Fecha de vencimiento</h6>
                                <p class="mb-0 @if($task->isOverdue() && $task->status !== 'done') text-danger @endif">
                                    <i class="fas fa-calendar-check me-2 @if($task->isOverdue() && $task->status !== 'done') text-danger @else text-muted @endif"></i>
                                    {{ $task->due_date->format('d/m/Y') }}
                                    <small class="@if($task->isOverdue() && $task->status !== 'done') text-danger @else text-muted @endif">
                                        ({{ $task->due_date->diffForHumans() }})
                                    </small>
                                    @if($task->isOverdue() && $task->status !== 'done')
                                        <span class="badge bg-danger ms-2">Vencida</span>
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Acciones rápidas -->
                    @if($task->assigned_to === Auth::id() || $task->project->creator_id === Auth::id())
                        <div class="border-top pt-3">
                            <h6 class="text-muted mb-3">Cambiar estado</h6>
                            <div class="btn-group" role="group">
                                <button type="button" 
                                        class="btn btn-outline-secondary {{ $task->status === 'todo' ? 'active' : '' }}"
                                        onclick="updateTaskStatus('{{ $task->id }}', 'todo')">
                                    <i class="fas fa-circle me-1"></i>Por hacer
                                </button>
                                <button type="button" 
                                        class="btn btn-outline-warning {{ $task->status === 'in_progress' ? 'active' : '' }}"
                                        onclick="updateTaskStatus('{{ $task->id }}', 'in_progress')">
                                    <i class="fas fa-play me-1"></i>En progreso
                                </button>
                                <button type="button" 
                                        class="btn btn-outline-success {{ $task->status === 'done' ? 'active' : '' }}"
                                        onclick="updateTaskStatus('{{ $task->id }}', 'done')">
                                    <i class="fas fa-check me-1"></i>Completado
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar con información adicional -->
        <div class="col-lg-4">
            <!-- Asignación -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-user me-2"></i>Asignación
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Creado por</h6>
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($task->creator->name) }}&background=4e73df&color=fff&size=32" 
                                 alt="{{ $task->creator->name }}" 
                                 class="rounded-circle me-2"
                                 style="width: 32px; height: 32px;">
                            <div>
                                <div class="fw-medium">{{ $task->creator->name }}</div>
                                <small class="text-muted">{{ $task->created_at->format('d/m/Y') }}</small>
                            </div>
                        </div>
                    </div>

                    @if($task->assignedUser)
                        <div>
                            <h6 class="text-muted mb-2">Asignado a</h6>
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($task->assignedUser->name) }}&background=28a745&color=fff&size=32" 
                                     alt="{{ $task->assignedUser->name }}" 
                                     class="rounded-circle me-2"
                                     style="width: 32px; height: 32px;">
                                <div>
                                    <div class="fw-medium">{{ $task->assignedUser->name }}</div>
                                    <small class="text-muted">Responsable</small>
                                </div>
                            </div>
                        </div>
                    @else
                        <div>
                            <h6 class="text-muted mb-2">Asignado a</h6>
                            <div class="text-muted">
                                <i class="fas fa-user-slash me-2"></i>
                                Sin asignar
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Información del proyecto -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-folder-open me-2"></i>Proyecto
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="mb-2">{{ $task->project->title }}</h6>
                    @if($task->project->description)
                        <p class="text-muted small mb-3">{{ Str::limit($task->project->description, 100) }}</p>
                    @endif
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Estado del proyecto:</small>
                        <span class="badge bg-{{ $task->project->status === 'active' ? 'success' : ($task->project->status === 'completed' ? 'primary' : 'secondary') }}">
                            {{ ucfirst($task->project->status) }}
                        </span>
                    </div>
                    
                    @if($task->project->deadline)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">Vence:</small>
                            <small>{{ $task->project->deadline->format('d/m/Y') }}</small>
                        </div>
                    @endif

                    <a href="{{ route('projects.show', $task->project) }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-external-link-alt me-1"></i>Ver proyecto
                    </a>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Actividad
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h5 mb-0 text-primary">{{ $task->project->tasks()->count() }}</div>
                                <small class="text-muted">Tareas totales</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-0 text-success">{{ $task->project->tasks()->where('status', 'done')->count() }}</div>
                            <small class="text-muted">Completadas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Actualizar estado de tarea
function updateTaskStatus(taskId, status) {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Actualizando...';
    
    fetch(`/api/tasks/${taskId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Estado actualizado correctamente', 'success');
            // Recargar la página para mostrar los cambios
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error al actualizar el estado', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Eliminar tarea
function deleteTask(taskId) {
    if (confirm('¿Estás seguro de que quieres eliminar esta tarea?')) {
        fetch(`/tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Tarea eliminada correctamente', 'success');
                // Redirigir al proyecto
                setTimeout(() => {
                    window.location.href = data.redirect || '{{ route("projects.show", $task->project) }}';
                }, 1000);
            } else {
                showNotification(data.message || 'Error al eliminar la tarea', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        });
    }
}

// Mostrar notificación
function showNotification(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 'alert-info';
    
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 500px;
    `;
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>
@endpush
@endsection