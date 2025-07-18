@extends('layouts.app')

@section('title', 'Editar Proyecto - MoodlePro')

@push('styles')
<style>
    .edit-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 2rem 2rem;
    }

    .edit-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
    }

    .breadcrumb-item {
        color: rgba(255, 255, 255, 0.8);
    }

    .breadcrumb-item.active {
        color: white;
    }

    .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
    }

    .edit-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .form-section {
        background: white;
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2e3440;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e3e6f0;
    }

    .form-label {
        font-weight: 600;
        color: #5a5c69;
        margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
        border: 2px solid #e3e6f0;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        transition: all 0.3s;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .date-inputs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .status-option {
        display: none;
    }

    .status-option + label {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        border: 2px solid #e3e6f0;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.3s;
        margin-right: 1rem;
        margin-bottom: 1rem;
    }

    .status-option:checked + label {
        border-color: #4e73df;
        background: #4e73df;
        color: white;
    }

    .status-option + label i {
        margin-right: 0.5rem;
    }

    .members-section {
        margin-top: 1.5rem;
    }

    .current-members {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .member-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        background: #f8f9fc;
        border-radius: 0.5rem;
        border: 1px solid #e3e6f0;
    }

    .member-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #4e73df;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .member-info {
        flex: 1;
    }

    .member-name {
        font-weight: 600;
        color: #2e3440;
        margin-bottom: 0.25rem;
    }

    .member-role {
        font-size: 0.875rem;
        color: #858796;
    }

    .remove-member {
        background: none;
        border: none;
        color: #e74a3b;
        cursor: pointer;
        padding: 0.5rem;
        transition: all 0.3s;
    }

    .remove-member:hover {
        transform: scale(1.1);
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
    }

    .btn-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .btn-cancel {
        background: #858796;
        color: white;
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-cancel:hover {
        background: #6c757d;
        color: white;
        transform: translateY(-2px);
    }

    .danger-zone {
        background: #fff5f5;
        border: 2px solid #fee;
        padding: 2rem;
        border-radius: 0.5rem;
        margin-top: 2rem;
    }

    .danger-zone h3 {
        color: #e74a3b;
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }

    .btn-delete {
        background: #e74a3b;
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-delete:hover {
        background: #c82333;
        transform: translateY(-2px);
    }

    .helper-text {
        font-size: 0.875rem;
        color: #858796;
        margin-top: 0.5rem;
    }

    .required {
        color: #e74a3b;
    }

    .loading-spinner {
        display: none;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #4e73df;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-left: 0.5rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @media (max-width: 768px) {
        .date-inputs {
            grid-template-columns: 1fr;
        }

        .status-option + label {
            display: block;
            margin-right: 0;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-save,
        .btn-cancel {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<!-- Edit Header -->
<div class="edit-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Proyectos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
        <h1>Editar Proyecto</h1>
        <p class="lead mb-0">Actualiza la información de tu proyecto</p>
    </div>
</div>

<div class="container">
    <div class="edit-container">
        <form action="{{ route('projects.update', $project) }}" method="POST" id="editForm">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="form-section">
                <h2 class="section-title">Información Básica</h2>
                
                <div class="mb-3">
                    <label for="title" class="form-label">
                        Nombre del Proyecto <span class="required">*</span>
                    </label>
                    <input type="text" 
                           class="form-control @error('title') is-invalid @enderror" 
                           id="title" 
                           name="title" 
                           value="{{ old('title', $project->title) }}"
                           required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description">{{ old('description', $project->description) }}</textarea>
                    <div class="helper-text">Proporciona detalles sobre los objetivos y alcance del proyecto</div>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="date-inputs">
                    <div>
                        <label for="start_date" class="form-label">
                            Fecha de Inicio <span class="required">*</span>
                        </label>
                        <input type="date" 
                               class="form-control @error('start_date') is-invalid @enderror" 
                               id="start_date" 
                               name="start_date"
                               value="{{ old('start_date', $project->start_date->format('Y-m-d')) }}"
                               required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="deadline" class="form-label">
                            Fecha de Entrega <span class="required">*</span>
                        </label>
                        <input type="date" 
                               class="form-control @error('deadline') is-invalid @enderror" 
                               id="deadline" 
                               name="deadline"
                               value="{{ old('deadline', $project->deadline->format('Y-m-d')) }}"
                               required>
                        @error('deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Project Status -->
            <div class="form-section">
                <h2 class="section-title">Estado del Proyecto</h2>
                
                <div class="status-options">
                    <input type="radio" 
                           name="status" 
                           value="planning" 
                           id="status-planning" 
                           class="status-option"
                           {{ $project->status == 'planning' ? 'checked' : '' }}>
                    <label for="status-planning">
                        <i class="fas fa-clipboard-list"></i> Planificación
                    </label>
                    
                    <input type="radio" 
                           name="status" 
                           value="active" 
                           id="status-active" 
                           class="status-option"
                           {{ $project->status == 'active' ? 'checked' : '' }}>
                    <label for="status-active">
                        <i class="fas fa-play-circle"></i> Activo
                    </label>
                    
                    <input type="radio" 
                           name="status" 
                           value="completed" 
                           id="status-completed" 
                           class="status-option"
                           {{ $project->status == 'completed' ? 'checked' : '' }}>
                    <label for="status-completed">
                        <i class="fas fa-check-circle"></i> Completado
                    </label>
                    
                    <input type="radio" 
                           name="status" 
                           value="cancelled" 
                           id="status-cancelled" 
                           class="status-option"
                           {{ $project->status == 'cancelled' ? 'checked' : '' }}>
                    <label for="status-cancelled">
                        <i class="fas fa-times-circle"></i> Cancelado
                    </label>
                </div>
            </div>
            
            <!-- Team Members -->
            <div class="form-section">
                <h2 class="section-title">Miembros del Equipo</h2>
                
                <div class="members-section">
                    <div class="current-members">
                        @foreach($project->members as $member)
                            <div class="member-card" data-member-id="{{ $member->id }}">
                                <div class="member-avatar">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <div class="member-info">
                                    <div class="member-name">{{ $member->name }}</div>
                                    <div class="member-role">
                                        {{ $member->pivot->role == 'coordinator' ? 'Coordinador' : 'Miembro' }}
                                    </div>
                                </div>
                                @if($member->id != $project->creator_id)
                                    <button type="button" class="remove-member" onclick="removeMember({{ $member->id }})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    
                    <a href="{{ route('projects.members', $project) }}" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus me-2"></i>Gestionar Miembros
                    </a>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('projects.show', $project) }}" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-save">
                    Guardar Cambios
                    <span class="loading-spinner"></span>
                </button>
            </div>
        </form>
        
        <!-- Danger Zone -->
        @if($project->creator_id === auth()->id())
            <div class="danger-zone">
                <h3><i class="fas fa-exclamation-triangle me-2"></i>Zona de Peligro</h3>
                <p>Una vez que elimines el proyecto, no hay vuelta atrás. Por favor, estás seguro.</p>
                <button type="button" class="btn-delete" onclick="confirmDelete()">
                    Eliminar Proyecto Permanentemente
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="delete-form" action="{{ route('projects.destroy', $project) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
// Form submission
document.getElementById('editForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('.btn-save');
    const spinner = this.querySelector('.loading-spinner');
    
    submitBtn.disabled = true;
    spinner.style.display = 'inline-block';
});

// Remove member
function removeMember(memberId) {
    if (confirm('¿Estás seguro de quitar a este miembro del proyecto?')) {
        // Aquí iría la lógica para remover al miembro via AJAX
        document.querySelector(`[data-member-id="${memberId}"]`).remove();
    }
}

// Delete project
function confirmDelete() {
    if (confirm('¿Estás seguro de que quieres eliminar este proyecto? Esta acción no se puede deshacer.')) {
        if (confirm('Esta es tu última oportunidad. ¿Realmente quieres eliminar el proyecto "{{ $project->title }}"?')) {
            document.getElementById('delete-form').submit();
        }
    }
}

// Date validation
document.getElementById('deadline').addEventListener('change', function() {
    const startDate = document.getElementById('start_date').value;
    const deadline = this.value;
    
    if (startDate && deadline && new Date(deadline) < new Date(startDate)) {
        alert('La fecha de entrega no puede ser anterior a la fecha de inicio');
        this.value = '';
    }
});
</script>
@endpush