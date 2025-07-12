@extends('layouts.app')

@section('title', 'Mi Perfil - MoodlePro')

@push('styles')
<style>
    .profile-container {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Profile Card */
    .profile-card {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        padding: 2rem;
        height: fit-content;
        position: sticky;
        top: 2rem;
    }

    .profile-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        margin-bottom: 1rem;
        border: 4px solid #e3e6f0;
    }

    .profile-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2e3440;
        margin-bottom: 0.5rem;
    }

    .profile-role {
        color: #858796;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }

    .profile-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        padding: 1.5rem 0;
        border-top: 1px solid #e3e6f0;
        border-bottom: 1px solid #e3e6f0;
        margin-bottom: 1.5rem;
    }

    .stat-item {
        text-align: center;
    }

    .stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #4e73df;
        display: block;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #858796;
        text-transform: uppercase;
    }

    .profile-actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .btn-profile {
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #e3e6f0;
        background: white;
        color: #4e73df;
        font-weight: 500;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-profile:hover {
        background: #4e73df;
        color: white;
        transform: translateY(-2px);
    }

    .btn-upgrade {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
    }

    .btn-upgrade:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }

    /* Content Sections */
    .profile-content {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .content-section {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        padding: 2rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e3e6f0;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2e3440;
        margin: 0;
    }

    .btn-edit {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #4e73df;
        background: white;
        color: #4e73df;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-edit:hover {
        background: #4e73df;
        color: white;
    }

    /* Profile Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        font-size: 0.75rem;
        color: #858796;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .info-value {
        color: #2e3440;
        font-weight: 500;
    }

    .info-value.empty {
        color: #b7b9cc;
        font-style: italic;
    }

    /* Documents Section */
    .documents-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }

    .document-card {
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s;
        cursor: pointer;
    }

    .document-card:hover {
        border-color: #4e73df;
        transform: translateY(-2px);
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    }

    .document-icon {
        width: 48px;
        height: 48px;
        background: #f8f9fc;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #4e73df;
    }

    .document-info {
        flex: 1;
    }

    .document-name {
        font-weight: 600;
        color: #2e3440;
        margin-bottom: 0.25rem;
    }

    .document-meta {
        font-size: 0.75rem;
        color: #858796;
    }

    .document-action {
        color: #4e73df;
        font-size: 1.25rem;
    }

    /* Upload Area */
    .upload-area {
        border: 2px dashed #d1d3e2;
        border-radius: 0.5rem;
        padding: 2rem;
        text-align: center;
        margin-top: 1rem;
        transition: all 0.3s;
        cursor: pointer;
    }

    .upload-area:hover {
        border-color: #4e73df;
        background: #f8f9fc;
    }

    .upload-icon {
        font-size: 3rem;
        color: #d1d3e2;
        margin-bottom: 1rem;
    }

    .upload-text {
        color: #858796;
        margin-bottom: 1rem;
    }

    .btn-upload {
        padding: 0.5rem 1.5rem;
        border-radius: 0.5rem;
        background: #4e73df;
        color: white;
        border: none;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-upload:hover {
        background: #2e59d9;
        transform: translateY(-1px);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .profile-container {
            grid-template-columns: 1fr;
        }

        .profile-card {
            position: static;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="profile-container">
    <!-- Profile Card -->
    <div class="profile-card">
        <div class="profile-header">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&size=120&background=4e73df&color=fff" 
                 alt="{{ Auth::user()->name }}" 
                 class="profile-avatar">
            <h2 class="profile-name">{{ Auth::user()->name }}</h2>
            <p class="profile-role">@ {{ Str::slug(Auth::user()->name) }}</p>
        </div>

        <div class="profile-stats">
            <div class="stat-item">
                <span class="stat-value">{{ $stats['projects'] ?? 0 }}</span>
                <span class="stat-label">Proyectos</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $stats['tasks'] ?? 0 }}</span>
                <span class="stat-label">Tareas</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $stats['completed'] ?? 0 }}</span>
                <span class="stat-label">Completadas</span>
            </div>
        </div>

        <div class="profile-actions">
            <a href="{{ route('profile.edit') }}" class="btn-profile">
                <i class="fas fa-edit"></i>
                Editar Perfil
            </a>
            <a href="#" class="btn-profile">
                <i class="fas fa-cog"></i>
                Configuración
            </a>
            <a href="#" class="btn-profile">
                <i class="fas fa-shield-alt"></i>
                Privacidad
            </a>
            <button class="btn-profile btn-upgrade">
                <i class="fas fa-crown"></i>
                Necesitas más espacio?
            </button>
        </div>
    </div>

    <!-- Profile Content -->
    <div class="profile-content">
        <!-- Personal Information -->
        <div class="content-section">
            <div class="section-header">
                <h3 class="section-title">Información Personal</h3>
                <a href="{{ route('profile.edit') }}" class="btn-edit">
                    <i class="fas fa-pencil-alt me-1"></i>
                    Editar
                </a>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nombre Completo</span>
                    <span class="info-value">{{ Auth::user()->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Correo Electrónico</span>
                    <span class="info-value">{{ Auth::user()->email }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Teléfono</span>
                    <span class="info-value {{ !isset($profile->phone) ? 'empty' : '' }}">
                        {{ $profile->phone ?? 'No especificado' }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha de Nacimiento</span>
                    <span class="info-value {{ !isset($profile->birth_date) ? 'empty' : '' }}">
                        {{ $profile->birth_date ?? 'No especificado' }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Universidad/Institución</span>
                    <span class="info-value {{ !isset($profile->institution) ? 'empty' : '' }}">
                        {{ $profile->institution ?? 'No especificado' }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Carrera</span>
                    <span class="info-value {{ !isset($profile->career) ? 'empty' : '' }}">
                        {{ $profile->career ?? 'No especificado' }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Semestre/Año</span>
                    <span class="info-value {{ !isset($profile->semester) ? 'empty' : '' }}">
                        {{ $profile->semester ?? 'No especificado' }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Idiomas</span>
                    <span class="info-value {{ !isset($profile->languages) ? 'empty' : '' }}">
                        {{ $profile->languages ?? 'No especificado' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Academic Documents -->
        <div class="content-section">
            <div class="section-header">
                <h3 class="section-title">Documentos Académicos</h3>
                <button class="btn-edit" onclick="document.getElementById('fileInput').click()">
                    <i class="fas fa-upload me-1"></i>
                    Subir
                </button>
            </div>

            <div class="documents-grid">
                @forelse($documents ?? [] as $document)
                    <div class="document-card" onclick="viewDocument('{{ $document->id }}')">
                        <div class="document-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div class="document-info">
                            <div class="document-name">{{ $document->name }}</div>
                            <div class="document-meta">{{ $document->size }} • {{ $document->uploaded_at }}</div>
                        </div>
                        <div class="document-action">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <p class="upload-text">Arrastra y suelta archivos aquí o haz clic para seleccionar</p>
                            <button class="btn-upload">Seleccionar Archivos</button>
                        </div>
                    </div>
                @endforelse
            </div>

            <input type="file" id="fileInput" style="display: none;" multiple accept=".pdf,.doc,.docx">
        </div>

        <!-- Settings & Preferences -->
        <div class="content-section">
            <div class="section-header">
                <h3 class="section-title">Preferencias</h3>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Notificaciones por Email</span>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                        <label class="form-check-label" for="emailNotifications">
                            Recibir notificaciones de proyectos y tareas
                        </label>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-label">Recordatorios</span>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="reminders" checked>
                        <label class="form-check-label" for="reminders">
                            Recordatorios de fechas de entrega
                        </label>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-label">Modo Oscuro</span>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="darkMode">
                        <label class="form-check-label" for="darkMode">
                            Activar modo oscuro
                        </label>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-label">Idioma</span>
                    <select class="form-select form-select-sm" style="width: auto;">
                        <option selected>Español</option>
                        <option>English</option>
                        <option>Português</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="content-section">
            <div class="section-header">
                <h3 class="section-title text-danger">Zona de Peligro</h3>
            </div>

            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Precaución:</strong> Estas acciones son permanentes y no se pueden deshacer.
            </div>

            <div class="d-flex gap-3">
                <button class="btn btn-outline-danger">
                    <i class="fas fa-download me-2"></i>
                    Exportar Datos
                </button>
                <button class="btn btn-danger" onclick="confirmDeleteAccount()">
                    <i class="fas fa-trash-alt me-2"></i>
                    Eliminar Cuenta
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Handle file upload
document.getElementById('fileInput').addEventListener('change', function(e) {
    const files = e.target.files;
    if (files.length > 0) {
        // Aquí iría la lógica para subir archivos
        console.log('Archivos seleccionados:', files);
        alert('Funcionalidad de carga de archivos en desarrollo');
    }
});

// View document
function viewDocument(documentId) {
    // Aquí iría la lógica para ver el documento
    console.log('Ver documento:', documentId);
}

// Confirm delete account
function confirmDeleteAccount() {
    if (confirm('¿Estás seguro de que quieres eliminar tu cuenta? Esta acción no se puede deshacer.')) {
        if (confirm('Esta es tu última oportunidad. ¿Realmente quieres eliminar tu cuenta permanentemente?')) {
            // Aquí iría la lógica para eliminar la cuenta
            console.log('Eliminar cuenta');
        }
    }
}

// Handle preferences changes
document.querySelectorAll('.form-check-input, .form-select').forEach(element => {
    element.addEventListener('change', function() {
        // Aquí iría la lógica para guardar las preferencias
        console.log('Preferencia cambiada:', this.id || this.name, this.checked || this.value);
    });
});
</script>
@endpush