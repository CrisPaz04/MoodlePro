@extends('layouts.app')

@section('title', $resource->title . ' - MoodlePro')

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
                            <a href="{{ route('resources.index') }}">Biblioteca</a>
                        </li>
                        <li class="breadcrumb-item active">{{ $resource->title }}</li>
                    </ol>
                </nav>
                <h1 class="dashboard-title">
                    <i class="fas fa-{{ $resource->icon ?? 'file' }} me-3"></i>
                    {{ $resource->title }}
                </h1>
                <p class="dashboard-subtitle">
                    {{ $resource->category_label ?? ucfirst($resource->category) }} • 
                    Subido por {{ $resource->uploader->name }} • 
                    {{ $resource->created_at->format('d M, Y') }}
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- Columna Principal -->
        <div class="col-lg-8">
            <!-- Vista Previa del Archivo -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="file-preview">
                        <div class="file-icon-large">
                            <i class="fas fa-{{ $resource->icon ?? 'file' }}"></i>
                        </div>
                        <div class="file-info-large">
                            <h3>{{ $resource->file_name }}</h3>
                            <div class="file-meta">
                                <span class="file-type">{{ strtoupper($resource->file_type) }}</span>
                                <span class="file-size">{{ $resource->file_size }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones Principales -->
                    <div class="main-actions">
                        <a href="{{ route('resources.download', $resource) }}" 
                           class="btn btn-primary btn-lg"
                           onclick="trackDownload({{ $resource->id }})">
                            <i class="fas fa-download me-2"></i>
                            Descargar Archivo
                        </a>
                        <button class="btn btn-outline-danger btn-lg" 
                                onclick="toggleFavorite({{ $resource->id }})">
                            <i class="far fa-heart me-2" id="heartIcon"></i>
                            Favorito
                        </button>
                        <button class="btn btn-outline-info btn-lg" 
                                onclick="shareResource()">
                            <i class="fas fa-share me-2"></i>
                            Compartir
                        </button>
                    </div>
                </div>
            </div>

            <!-- Descripción -->
            @if($resource->description)
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-align-left me-2"></i>Descripción
                    </h5>
                </div>
                <div class="card-body">
                    <p class="description-text">{{ $resource->description }}</p>
                </div>
            </div>
            @endif

            <!-- Sistema de Calificación -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>Calificación
                    </h5>
                </div>
                <div class="card-body">
                    <div class="rating-section">
                        <div class="current-rating">
                            <div class="rating-stars-large">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= round($resource->rating ?? 0) ? 'filled' : 'empty' }}"></i>
                                @endfor
                            </div>
                            <div class="rating-info">
                                <span class="rating-value">{{ number_format($resource->rating ?? 0, 1) }}</span>
                                <span class="rating-count">({{ $resource->ratings_count ?? 0 }} calificaciones)</span>
                            </div>
                        </div>

                        <div class="rate-this">
                            <h6>Califica este recurso:</h6>
                            <div class="user-rating" id="userRating">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star rating-star" 
                                       data-rating="{{ $i }}" 
                                       onclick="rateResource({{ $i }})"></i>
                                @endfor
                            </div>
                            <small class="text-muted">Haz clic en las estrellas para calificar</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recursos Relacionados -->
            @if($relatedResources && $relatedResources->count() > 0)
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-layer-group me-2"></i>Recursos Relacionados
                    </h5>
                </div>
                <div class="card-body">
                    <div class="related-resources">
                        @foreach($relatedResources as $related)
                            <div class="related-item" onclick="window.location.href='{{ route('resources.show', $related) }}'">
                                <div class="related-icon">
                                    <i class="fas fa-{{ $related->icon ?? 'file' }}"></i>
                                </div>
                                <div class="related-info">
                                    <h6>{{ $related->title }}</h6>
                                    <p>{{ Str::limit($related->description, 60) }}</p>
                                    <div class="related-meta">
                                        <span><i class="fas fa-download"></i> {{ $related->downloads_count ?? 0 }}</span>
                                        <span><i class="fas fa-star"></i> {{ number_format($related->rating ?? 0, 1) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Barra Lateral -->
        <div class="col-lg-4">
            <!-- Información del Archivo -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Información
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">Categoría</span>
                            <span class="info-value">
                                <span class="category-badge category-{{ $resource->category }}">
                                    {{ ucfirst($resource->category) }}
                                </span>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tamaño</span>
                            <span class="info-value">{{ $resource->file_size }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tipo</span>
                            <span class="info-value">{{ strtoupper($resource->file_type) }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Descargas</span>
                            <span class="info-value">{{ $resource->downloads_count ?? 0 }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Subido</span>
                            <span class="info-value">{{ $resource->created_at->format('d M, Y') }}</span>
                        </div>
                        @if($resource->project)
                        <div class="info-item">
                            <span class="info-label">Proyecto</span>
                            <span class="info-value">
                                <a href="{{ route('projects.show', $resource->project) }}">
                                    {{ $resource->project->title }}
                                </a>
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Información del Autor -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Subido por
                    </h5>
                </div>
                <div class="card-body">
                    <div class="author-info">
                        <div class="author-avatar">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($resource->uploader->name) }}&background=4e73df&color=fff" 
                                 alt="{{ $resource->uploader->name }}" 
                                 class="avatar">
                        </div>
                        <div class="author-details">
                            <h6>{{ $resource->uploader->name }}</h6>
                            <p class="text-muted">Miembro desde {{ $resource->uploader->created_at->format('M Y') }}</p>
                            
                            <div class="author-stats">
                                <div class="stat">
                                    <span class="stat-value">{{ $resource->uploader->uploadedResources()->count() }}</span>
                                    <span class="stat-label">recursos</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value">{{ $resource->uploader->projects()->count() }}</span>
                                    <span class="stat-label">proyectos</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Adicionales -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>Acciones
                    </h5>
                </div>
                <div class="card-body">
                    <div class="action-buttons">
                        <button class="btn btn-outline-primary btn-block mb-2" onclick="reportResource()">
                            <i class="fas fa-flag me-2"></i>Reportar Contenido
                        </button>
                        <button class="btn btn-outline-secondary btn-block mb-2" onclick="copyLink()">
                            <i class="fas fa-link me-2"></i>Copiar Enlace
                        </button>
                        @if(Auth::id() === $resource->uploaded_by)
                        <a href="{{ route('resources.edit', $resource) }}" class="btn btn-outline-warning btn-block mb-2">
                            <i class="fas fa-edit me-2"></i>Editar Recurso
                        </a>
                        <button class="btn btn-outline-danger btn-block" onclick="deleteResource()">
                            <i class="fas fa-trash me-2"></i>Eliminar Recurso
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.file-preview {
    display: flex;
    align-items: center;
    padding: 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    margin-bottom: 2rem;
}

.file-icon-large {
    font-size: 4rem;
    color: #6c757d;
    margin-right: 2rem;
}

.file-info-large h3 {
    margin: 0 0 0.5rem 0;
    color: #2d3748;
}

.file-meta {
    display: flex;
    gap: 1rem;
}

.file-type, .file-size {
    background: #e2e8f0;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #4a5568;
}

.main-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.description-text {
    font-size: 1.1rem;
    line-height: 1.6;
    color: #4a5568;
    margin: 0;
}

.rating-section {
    text-align: center;
}

.current-rating {
    margin-bottom: 2rem;
}

.rating-stars-large {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.rating-stars-large .fa-star.filled {
    color: #f59e0b;
}

.rating-stars-large .fa-star.empty {
    color: #d1d5db;
}

.rating-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin-right: 0.5rem;
}

.rating-count {
    color: #6b7280;
    font-size: 1rem;
}

.user-rating {
    font-size: 1.5rem;
    margin: 1rem 0;
}

.rating-star {
    color: #d1d5db;
    cursor: pointer;
    transition: all 0.2s;
    margin: 0 0.25rem;
}

.rating-star:hover,
.rating-star.active {
    color: #f59e0b;
    transform: scale(1.1);
}

.related-resources {
    display: grid;
    gap: 1rem;
}

.related-item {
    display: flex;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.related-item:hover {
    border-color: #3b82f6;
    background: #f8fafc;
    transform: translateY(-2px);
}

.related-icon {
    width: 48px;
    height: 48px;
    background: #f3f4f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.5rem;
    color: #6b7280;
}

.related-info h6 {
    margin: 0 0 0.5rem 0;
    color: #2d3748;
}

.related-info p {
    margin: 0 0 0.5rem 0;
    color: #6b7280;
    font-size: 0.875rem;
}

.related-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.8rem;
    color: #9ca3af;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.info-label {
    font-weight: 600;
    color: #4a5568;
}

.info-value {
    color: #2d3748;
}

.category-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    font-weight: 600;
}

.category-document { background: #dbeafe; color: #1e40af; }
.category-presentation { background: #fef3c7; color: #d97706; }
.category-video { background: #dcfce7; color: #16a34a; }
.category-code { background: #f3e8ff; color: #7c3aed; }
.category-other { background: #f1f5f9; color: #475569; }

.author-info {
    display: flex;
    align-items: center;
}

.author-avatar {
    margin-right: 1rem;
}

.avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
}

.author-details h6 {
    margin: 0 0 0.25rem 0;
    color: #2d3748;
}

.author-stats {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
}

.stat {
    text-align: center;
}

.stat-value {
    display: block;
    font-weight: 700;
    color: #2d3748;
}

.stat-label {
    font-size: 0.8rem;
    color: #6b7280;
}

.action-buttons .btn {
    width: 100%;
    text-align: left;
}

@media (max-width: 768px) {
    .file-preview {
        flex-direction: column;
        text-align: center;
    }
    
    .file-icon-large {
        margin-right: 0;
        margin-bottom: 1rem;
    }
    
    .main-actions {
        flex-direction: column;
    }
    
    .author-info {
        flex-direction: column;
        text-align: center;
    }
    
    .author-avatar {
        margin-right: 0;
        margin-bottom: 1rem;
    }
}
</style>

<script>
// Calificar recurso
function rateResource(rating) {
    fetch(`/resources/{{ $resource->id }}/rate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ rating: rating })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar estrellas de calificación actual
            updateStars('.rating-stars-large', data.new_rating);
            document.querySelector('.rating-value').textContent = data.new_rating;
            document.querySelector('.rating-count').textContent = `(${data.ratings_count} calificaciones)`;
            
            // Marcar estrellas del usuario
            updateUserRating(rating);
            
            // Mostrar mensaje de éxito
            showMessage('¡Gracias por tu calificación!', 'success');
        } else {
            showMessage('Error al enviar calificación', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error al enviar calificación', 'error');
    });
}

function updateStars(selector, rating) {
    const stars = document.querySelectorAll(selector + ' .fa-star');
    stars.forEach((star, index) => {
        if (index < Math.round(rating)) {
            star.classList.remove('empty');
            star.classList.add('filled');
        } else {
            star.classList.remove('filled');
            star.classList.add('empty');
        }
    });
}

function updateUserRating(rating) {
    const userStars = document.querySelectorAll('#userRating .rating-star');
    userStars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

// Agregar a favoritos
function toggleFavorite(resourceId) {
    fetch(`/resources/${resourceId}/favorite`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const icon = document.getElementById('heartIcon');
            icon.classList.toggle('far');
            icon.classList.toggle('fas');
            showMessage(data.message, 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error al agregar a favoritos', 'error');
    });
}

// Compartir recurso
function shareResource() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $resource->title }}',
            text: 'Mira este recurso en MoodlePro',
            url: window.location.href
        });
    } else {
        copyLink();
    }
}

// Copiar enlace
function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        showMessage('Enlace copiado al portapapeles', 'success');
    });
}

// Reportar recurso
function reportResource() {
    const reason = prompt('¿Por qué quieres reportar este recurso?');
    if (reason) {
        // Aquí iría la lógica para reportar
        showMessage('Reporte enviado. Gracias por tu colaboración.', 'info');
    }
}

// Eliminar recurso
function deleteResource() {
    if (confirm('¿Estás seguro de que quieres eliminar este recurso? Esta acción no se puede deshacer.')) {
        // Redirigir al formulario de eliminación
        window.location.href = "{{ route('resources.destroy', $resource) }}";
    }
}

// Tracking de descarga
function trackDownload(resourceId) {
    // El contador se actualiza automáticamente en el backend
    showMessage('Descarga iniciada...', 'info');
}

// Mostrar mensajes
function showMessage(message, type) {
    // Crear toast notification simple
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Hover effects para calificación
document.addEventListener('DOMContentLoaded', function() {
    const ratingStars = document.querySelectorAll('.rating-star');
    
    ratingStars.forEach(star => {
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            highlightStars(rating);
        });
        
        star.addEventListener('mouseleave', function() {
            resetStars();
        });
    });
});

function highlightStars(rating) {
    const stars = document.querySelectorAll('.rating-star');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.style.color = '#f59e0b';
        } else {
            star.style.color = '#d1d5db';
        }
    });
}

function resetStars() {
    const stars = document.querySelectorAll('.rating-star');
    stars.forEach(star => {
        if (star.classList.contains('active')) {
            star.style.color = '#f59e0b';
        } else {
            star.style.color = '#d1d5db';
        }
    });
}
</script>
@endsection