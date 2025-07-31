@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Proyectos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.show', $task->project) }}">{{ $task->project->title }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">Editar Tarea</h4>
                            <small class="text-muted">
                                <i class="fas fa-folder me-1"></i>
                                {{ $task->project->title }}
                            </small>
                        </div>
                        <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Mostrar errores de validación -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Por favor corrige los siguientes errores:</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Formulario de edición -->
                    <form id="editTaskForm" method="POST" action="{{ route('tasks.update', $task) }}">
                        @csrf
                        @method('PUT')

                        <!-- Título de la tarea -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-semibold">
                                Título de la tarea <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $task->title) }}" 
                                   required
                                   placeholder="Ej: Diseñar nueva interfaz de usuario">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">Descripción</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="Describe los detalles de la tarea...">{{ old('description', $task->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Proporciona detalles sobre qué se debe hacer, requisitos específicos, etc.
                            </div>
                        </div>

                        <!-- Fila de campos -->
                        <div class="row mb-4">
                            <!-- Estado -->
                            <div class="col-md-4">
                                <label for="status" class="form-label fw-semibold">
                                    Estado <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" 
                                        name="status" 
                                        required>
                                    <option value="todo" {{ old('status', $task->status) === 'todo' ? 'selected' : '' }}>
                                        <i class="fas fa-circle me-1"></i>Por hacer
                                    </option>
                                    <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>
                                        <i class="fas fa-play me-1"></i>En progreso
                                    </option>
                                    <option value="done" {{ old('status', $task->status) === 'done' ? 'selected' : '' }}>
                                        <i class="fas fa-check me-1"></i>Completado
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Prioridad -->
                            <div class="col-md-4">
                                <label for="priority" class="form-label fw-semibold">
                                    Prioridad <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" 
                                        name="priority" 
                                        required>
                                    <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>
                                        🟢 Baja
                                    </option>
                                    <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>
                                        🟡 Media
                                    </option>
                                    <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>
                                        🔴 Alta
                                    </option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha de vencimiento -->
                            <div class="col-md-4">
                                <label for="due_date" class="form-label fw-semibold">Fecha de vencimiento</label>
                                <input type="date" 
                                       class="form-control @error('due_date') is-invalid @enderror" 
                                       id="due_date" 
                                       name="due_date" 
                                       value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}"
                                       min="{{ date('Y-m-d') }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-calendar me-1"></i>
                                    Opcional: fecha límite para completar la tarea
                                </div>
                            </div>
                        </div>

                        <!-- Asignación -->
                        <div class="mb-4">
                            <label for="assigned_to" class="form-label fw-semibold">Asignar a</label>
                            <select class="form-select @error('assigned_to') is-invalid @enderror" 
                                    id="assigned_to" 
                                    name="assigned_to">
                                <option value="">Sin asignar</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" 
                                            {{ old('assigned_to', $task->assigned_to) == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                        @if($member->id === $task->project->creator_id)
                                            (Coordinador)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-user me-1"></i>
                                Solo se pueden asignar miembros del proyecto
                            </div>
                        </div>

                        <!-- Información actual -->
                        <div class="bg-light p-3 rounded mb-4">
                            <h6 class="mb-3">
                                <i class="fas fa-info-circle me-2"></i>Información actual
                            </h6>
                            <div class="row small">
                                <div class="col-md-6">
                                    <strong>Creado por:</strong> {{ $task->creator->name }}<br>
                                    <strong>Fecha de creación:</strong> {{ $task->created_at->format('d/m/Y H:i') }}
                                </div>
                                <div class="col-md-6">
                                    @if($task->updated_at != $task->created_at)
                                        <strong>Última actualización:</strong> {{ $task->updated_at->format('d/m/Y H:i') }}<br>
                                        <strong>Hace:</strong> {{ $task->updated_at->diffForHumans() }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('tasks.show', $task) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-1"></i>Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar con información adicional -->
        <div class="col-lg-4">
            <!-- Vista previa del proyecto -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-folder-open me-2"></i>Proyecto: {{ $task->project->title }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($task->project->description)
                        <p class="text-muted small mb-3">{{ Str::limit($task->project->description, 150) }}</p>
                    @endif
                    
                    <div class="row small mb-3">
                        <div class="col-6">
                            <strong>Estado:</strong><br>
                            <span class="badge bg-{{ $task->project->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($task->project->status) }}
                            </span>
                        </div>
                        <div class="col-6">
                            <strong>Miembros:</strong><br>
                            {{ $task->project->members->count() }}
                        </div>
                    </div>

                    @if($task->project->deadline)
                        <div class="small mb-3">
                            <strong>Vencimiento del proyecto:</strong><br>
                            {{ $task->project->deadline->format('d/m/Y') }}
                            ({{ $task->project->deadline->diffForHumans() }})
                        </div>
                    @endif

                    <a href="{{ route('projects.show', $task->project) }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-external-link-alt me-1"></i>Ver proyecto completo
                    </a>
                </div>
            </div>

            <!-- Miembros del proyecto -->
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-users me-2"></i>Miembros disponibles
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($members->take(5) as $member)
                        <div class="d-flex align-items-center mb-2">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&background={{ $member->id === $task->project->creator_id ? '4e73df' : '28a745' }}&color=fff&size=24" 
                                 alt="{{ $member->name }}" 
                                 class="rounded-circle me-2"
                                 style="width: 24px; height: 24px;">
                            <div class="flex-grow-1">
                                <div class="small fw-medium">{{ $member->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ ucfirst($member->pivot->role) }}
                                </div>
                            </div>
                            @if($member->id === $task->assigned_to)
                                <span class="badge bg-success">Asignado</span>
                            @endif
                        </div>
                    @endforeach
                    
                    @if($members->count() > 5)
                        <div class="text-center mt-2">
                            <small class="text-muted">Y {{ $members->count() - 5 }} miembros más...</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Manejar envío del formulario con AJAX
document.getElementById('editTaskForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    // Mostrar estado de carga
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';
    
    // Limpiar errores anteriores
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Tarea actualizada exitosamente', 'success');
            
            // Redirigir a la vista de la tarea después de un breve delay
            setTimeout(() => {
                window.location.href = data.redirect || '{{ route("tasks.show", $task) }}';
            }, 1000);
        } else {
            // Mostrar errores de validación
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = data.errors[field][0];
                        input.parentNode.appendChild(feedback);
                    }
                });
                showNotification('Por favor corrige los errores en el formulario', 'error');
            } else {
                showNotification(data.message || 'Error al actualizar la tarea', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        showNotification('Error de conexión. Verifica tu conexión a internet.', 'error');
    })
    .finally(() => {
        // Restaurar botón
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

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
        <div style="white-space: pre-line;">${message}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Validación de fecha mínima
document.getElementById('due_date').addEventListener('change', function() {
    const selectedDate = new Date(this.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        this.setCustomValidity('La fecha de vencimiento no puede ser anterior a hoy');
        showNotification('La fecha de vencimiento no puede ser anterior a hoy', 'error');
        this.value = '';
    } else {
        this.setCustomValidity('');
    }
});
</script>
@endpush
@endsection