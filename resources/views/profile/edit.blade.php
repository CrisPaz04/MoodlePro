@extends('layouts.app')

@section('title', 'Editar Perfil - MoodlePro')

@section('content')
<div class="dashboard-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('profile.show') }}">Perfil</a>
                        </li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </nav>
                <h1 class="dashboard-title">
                    <i class="fas fa-user-edit me-3"></i>
                    Editar Perfil
                </h1>
                <p class="dashboard-subtitle">
                    Actualiza tu información personal y académica
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
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

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
                @csrf
                @method('PUT')

                <!-- Información Personal -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>Información Personal
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label required">
                                        <i class="fas fa-user me-2"></i>Nombre Completo
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $user->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label required">
                                        <i class="fas fa-envelope me-2"></i>Correo Electrónico
                                    </label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $user->email) }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-2"></i>Teléfono
                                    </label>
                                    <input type="tel" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $user->phone) }}"
                                           placeholder="+504 9999-9999">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="birth_date" class="form-label">
                                        <i class="fas fa-calendar me-2"></i>Fecha de Nacimiento
                                    </label>
                                    <input type="date" 
                                           class="form-control @error('birth_date') is-invalid @enderror" 
                                           id="birth_date" 
                                           name="birth_date" 
                                           value="{{ old('birth_date', $user->birth_date) }}">
                                    @error('birth_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="bio" class="form-label">
                                <i class="fas fa-quote-left me-2"></i>Biografía
                            </label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" 
                                      id="bio" 
                                      name="bio" 
                                      rows="3"
                                      placeholder="Cuéntanos un poco sobre ti...">{{ old('bio', $user->bio) }}</textarea>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Información Académica -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-graduation-cap me-2"></i>Información Académica
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="institution" class="form-label">
                                        <i class="fas fa-university me-2"></i>Universidad/Institución
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('institution') is-invalid @enderror" 
                                           id="institution" 
                                           name="institution" 
                                           value="{{ old('institution', $user->institution) }}"
                                           placeholder="Universidad Nacional Autónoma de Honduras">
                                    @error('institution')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="career" class="form-label">
                                        <i class="fas fa-book me-2"></i>Carrera
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('career') is-invalid @enderror" 
                                           id="career" 
                                           name="career" 
                                           value="{{ old('career', $user->career) }}"
                                           placeholder="Ingeniería en Sistemas">
                                    @error('career')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="semester" class="form-label">
                                        <i class="fas fa-calendar-alt me-2"></i>Semestre/Año Actual
                                    </label>
                                    <select class="form-select @error('semester') is-invalid @enderror" 
                                            id="semester" 
                                            name="semester">
                                        <option value="">Selecciona tu semestre</option>
                                        @for($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}" {{ old('semester', $user->semester) == $i ? 'selected' : '' }}>
                                                {{ $i }}° Semestre
                                            </option>
                                        @endfor
                                    </select>
                                    @error('semester')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">
                                        <i class="fas fa-id-card me-2"></i>Número de Cuenta
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('student_id') is-invalid @enderror" 
                                           id="student_id" 
                                           name="student_id" 
                                           value="{{ old('student_id', $user->student_id) }}"
                                           placeholder="20XX XXXX XXXX">
                                    @error('student_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="languages" class="form-label">
                                <i class="fas fa-language me-2"></i>Idiomas
                            </label>
                            <input type="text" 
                                   class="form-control @error('languages') is-invalid @enderror" 
                                   id="languages" 
                                   name="languages" 
                                   value="{{ old('languages', $user->languages) }}"
                                   placeholder="Español, Inglés, Francés">
                            <small class="form-text text-muted">Separa los idiomas con comas</small>
                            @error('languages')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Cambiar Contraseña -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-key me-2"></i>Cambiar Contraseña
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Deja estos campos vacíos si no deseas cambiar tu contraseña
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Contraseña Actual
                                    </label>
                                    <input type="password" 
                                           class="form-control @error('current_password') is-invalid @enderror" 
                                           id="current_password" 
                                           name="current_password">
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Nueva Contraseña
                                    </label>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Confirmar Nueva Contraseña
                                    </label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preferencias -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-cog me-2"></i>Preferencias
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Notificaciones por Email</label>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="email_notifications" 
                                               name="email_notifications" 
                                               value="1"
                                               {{ old('email_notifications', $user->email_notifications ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email_notifications">
                                            Recibir notificaciones por correo electrónico
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Visibilidad del Perfil</label>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="public_profile" 
                                               name="public_profile" 
                                               value="1"
                                               {{ old('public_profile', $user->public_profile ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="public_profile">
                                            Hacer mi perfil visible para otros usuarios
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <a href="{{ route('profile.show') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Cancelar
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="previewChanges()">
                            <i class="fas fa-eye me-2"></i>Vista Previa
                        </button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.form-label.required::after {
    content: " *";
    color: #dc2626;
}

.card {
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom: none;
}

.card-header h5 {
    margin: 0;
    font-weight: 600;
}

.form-control:focus,
.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
}

.form-text {
    font-size: 0.875rem;
    color: #6b7280;
}

.alert-info {
    background-color: #f0f9ff;
    border-color: #0ea5e9;
    color: #0369a1;
}

@media (max-width: 768px) {
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .btn {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profileForm');
    const saveBtn = document.getElementById('saveBtn');
    
    if (!form || !saveBtn) {
        console.error('Form or save button not found');
        return;
    }
    
    // Validación en tiempo real
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
    });
    
    // Confirmación antes de salir con cambios sin guardar
    let formChanged = false;
    inputs.forEach(input => {
        input.addEventListener('change', () => formChanged = true);
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    
    // Loading state al enviar
    form.addEventListener('submit', function(e) {
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
        saveBtn.disabled = true;
        formChanged = false;
    });
});

function previewChanges() {
    const form = document.getElementById('profileForm');
    if (!form) return;
    
    const formData = new FormData(form);
    
    let preview = 'Vista previa de cambios:\n\n';
    for (let [key, value] of formData.entries()) {
        if (value && !key.includes('password') && !key.includes('_token') && !key.includes('_method')) {
            // Formatear el nombre del campo
            let fieldName = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            preview += `${fieldName}: ${value}\n`;
        }
    }
    
    alert(preview);
}
</script>
@endsection