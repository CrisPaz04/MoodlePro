@extends('layouts.app')

@section('title', 'Biblioteca de Recursos - MoodlePro')

@push('styles')
<style>
    .library-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 2rem 2rem;
    }

    .library-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .header-stats {
        display: flex;
        gap: 2rem;
        margin-top: 1.5rem;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: rgba(255, 255, 255, 0.1);
        padding: 0.75rem 1.5rem;
        border-radius: 2rem;
        backdrop-filter: blur(10px);
    }

    .stat-item i {
        font-size: 1.5rem;
    }

    .controls-section {
        background: white;
        padding: 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        margin-bottom: 2rem;
    }

    .controls-row {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-box {
        position: relative;
        flex: 1;
        min-width: 250px;
    }

    .search-box input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 3rem;
        border: 2px solid #e3e6f0;
        border-radius: 0.5rem;
        transition: all 0.3s;
    }

    .search-box input:focus {
        border-color: #4e73df;
        outline: none;
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #858796;
    }

    .filter-dropdown {
        position: relative;
    }

    .filter-btn {
        background: white;
        border: 2px solid #e3e6f0;
        padding: 0.75rem 1.25rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.3s;
    }

    .filter-btn:hover {
        border-color: #4e73df;
    }

    .upload-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.3s;
    }

    .upload-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .view-toggle {
        display: flex;
        background: #e3e6f0;
        border-radius: 0.5rem;
        padding: 0.25rem;
    }

    .view-toggle button {
        background: transparent;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: all 0.3s;
        color: #858796;
    }

    .view-toggle button.active {
        background: white;
        color: #4e73df;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .category-pills {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
    }

    .category-pill {
        padding: 0.5rem 1rem;
        background: white;
        border: 2px solid #e3e6f0;
        border-radius: 2rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .category-pill.active {
        background: #4e73df;
        color: white;
        border-color: #4e73df;
    }

    .category-pill span {
        background: rgba(0, 0, 0, 0.1);
        padding: 0.125rem 0.5rem;
        border-radius: 1rem;
        font-size: 0.75rem;
    }

    .resources-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .resource-card {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        overflow: hidden;
        transition: all 0.3s;
        cursor: pointer;
    }

    .resource-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.15);
    }

    .resource-preview {
        height: 200px;
        background: #f8f9fc;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .resource-icon {
        font-size: 4rem;
        color: #d1d3e2;
    }

    .resource-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .resource-type-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .resource-info {
        padding: 1.5rem;
    }

    .resource-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #2e3440;
        margin-bottom: 0.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .resource-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.875rem;
        color: #858796;
        margin-bottom: 1rem;
    }

    .resource-meta-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .resource-description {
        color: #5a5c69;
        font-size: 0.875rem;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .resource-rating {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .stars {
        display: flex;
        gap: 0.125rem;
    }

    .star {
        color: #f6c23e;
    }

    .star.empty {
        color: #d1d3e2;
    }

    .resource-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid #e3e6f0;
    }

    .resource-author {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .author-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e3e6f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: 600;
        color: #5a5c69;
    }

    .resource-actions {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        padding: 0.5rem;
        background: none;
        border: none;
        color: #858796;
        cursor: pointer;
        transition: all 0.3s;
    }

    .action-btn:hover {
        color: #4e73df;
        transform: scale(1.1);
    }

    /* List View Styles */
    .resources-list .resource-card {
        display: flex;
        align-items: center;
        padding: 1.5rem;
    }

    .resources-list .resource-preview {
        width: 120px;
        height: 120px;
        flex-shrink: 0;
        margin-right: 1.5rem;
    }

    .resources-list .resource-icon {
        font-size: 3rem;
    }

    .resources-list .resource-info {
        flex: 1;
        padding: 0;
    }

    /* Upload Modal */
    .upload-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1050;
        align-items: center;
        justify-content: center;
    }

    .upload-modal.show {
        display: flex;
    }

    .upload-content {
        background: white;
        width: 90%;
        max-width: 600px;
        border-radius: 0.5rem;
        overflow: hidden;
        animation: slideIn 0.3s ease;
    }

    .upload-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .upload-body {
        padding: 2rem;
    }

    .upload-zone {
        border: 2px dashed #d1d3e2;
        border-radius: 0.5rem;
        padding: 3rem;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
    }

    .upload-zone.dragover {
        border-color: #4e73df;
        background: #f8f9fc;
    }

    .upload-zone i {
        font-size: 3rem;
        color: #d1d3e2;
        margin-bottom: 1rem;
    }

    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        color: #858796;
    }

    .empty-state i {
        font-size: 5rem;
        color: #d1d3e2;
        margin-bottom: 2rem;
    }

    @keyframes slideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @media (max-width: 768px) {
        .library-header h1 {
            font-size: 2rem;
        }

        .controls-row {
            flex-direction: column;
        }

        .search-box {
            width: 100%;
        }

        .resources-grid {
            grid-template-columns: 1fr;
        }

        .resources-list .resource-card {
            flex-direction: column;
            text-align: center;
        }

        .resources-list .resource-preview {
            margin: 0 0 1rem 0;
        }
    }
</style>
@endpush

@section('content')
<!-- Library Header -->
<div class="library-header">
    <div class="container">
        <h1>Biblioteca de Recursos</h1>
        <p class="lead mb-0">Comparte y accede a materiales de estudio</p>
        <div class="header-stats">
            <div class="stat-item">
                <i class="fas fa-file-alt"></i>
                <div>
                    <div class="stat-number">{{ $stats['total'] ?? 0 }}</div>
                    <div class="stat-label">Recursos</div>
                </div>
            </div>
            <div class="stat-item">
                <i class="fas fa-download"></i>
                <div>
                    <div class="stat-number">{{ $stats['downloads'] ?? 0 }}</div>
                    <div class="stat-label">Descargas</div>
                </div>
            </div>
            <div class="stat-item">
                <i class="fas fa-star"></i>
                <div>
                    <div class="stat-number">{{ $stats['rating'] ?? '0.0' }}</div>
                    <div class="stat-label">Calificación</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Controls Section -->
    <div class="controls-section">
        <div class="controls-row">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar recursos..." id="searchInput">
            </div>
            
            <div class="filter-dropdown">
                <button class="filter-btn">
                    <i class="fas fa-filter"></i>
                    Filtros
                </button>
            </div>
            
            <button class="upload-btn" onclick="showUploadModal()">
                <i class="fas fa-cloud-upload-alt"></i>
                Subir Recurso
            </button>
            
            <div class="view-toggle">
                <button class="active" onclick="setView('grid')">
                    <i class="fas fa-th"></i>
                </button>
                <button onclick="setView('list')">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Category Pills -->
    <div class="category-pills">
        <button class="category-pill active" data-category="all">
            Todos <span>{{ $resources->count() }}</span>
        </button>
        <button class="category-pill" data-category="document">
            <i class="fas fa-file-pdf"></i>
            Documentos <span>{{ $resources->where('category', 'document')->count() }}</span>
        </button>
        <button class="category-pill" data-category="presentation">
            <i class="fas fa-file-powerpoint"></i>
            Presentaciones <span>{{ $resources->where('category', 'presentation')->count() }}</span>
        </button>
        <button class="category-pill" data-category="video">
            <i class="fas fa-video"></i>
            Videos <span>{{ $resources->where('category', 'video')->count() }}</span>
        </button>
        <button class="category-pill" data-category="code">
            <i class="fas fa-code"></i>
            Código <span>{{ $resources->where('category', 'code')->count() }}</span>
        </button>
        <button class="category-pill" data-category="other">
            <i class="fas fa-folder"></i>
            Otros <span>{{ $resources->where('category', 'other')->count() }}</span>
        </button>
    </div>

    <!-- Resources Grid/List -->
    @if($resources->count() > 0)
        <div class="resources-grid" id="resourcesContainer">
            @foreach($resources as $resource)
                <div class="resource-card" data-category="{{ $resource->category }}">
                    <div class="resource-preview">
                        @if($resource->thumbnail)
                            <img src="{{ $resource->thumbnail }}" alt="{{ $resource->title }}" class="resource-image">
                        @else
                            <i class="fas fa-{{ $resource->icon ?? 'file' }} resource-icon"></i>
                        @endif
                        <span class="resource-type-badge">{{ strtoupper($resource->file_type) }}</span>
                    </div>
                    
                    <div class="resource-info">
                        <h3 class="resource-title">{{ $resource->title }}</h3>
                        
                        <div class="resource-meta">
                            <div class="resource-meta-item">
                                <i class="fas fa-calendar"></i>
                                {{ $resource->created_at->format('d M') }}
                            </div>
                            <div class="resource-meta-item">
                                <i class="fas fa-file"></i>
                                {{ $resource->file_size }}
                            </div>
                            <div class="resource-meta-item">
                                <i class="fas fa-download"></i>
                                {{ $resource->downloads_count ?? 0 }}
                            </div>
                        </div>
                        
                        <p class="resource-description">
                            {{ $resource->description ?: 'Sin descripción disponible' }}
                        </p>
                        
                        <div class="resource-rating">
                            <div class="stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= ($resource->rating ?? 0) ? '' : 'empty' }}"></i>
                                @endfor
                            </div>
                            <span>({{ $resource->ratings_count ?? 0 }} votos)</span>
                        </div>
                        
                        <div class="resource-footer">
                            <div class="resource-author">
                                <div class="author-avatar">
                                    {{ strtoupper(substr($resource->uploader->name ?? 'U', 0, 1)) }}
                                </div>
                                <span>{{ $resource->uploader->name ?? 'Usuario' }}</span>
                            </div>
                            
                            <div class="resource-actions">
                                <button class="action-btn" title="Descargar">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="action-btn" title="Favorito">
                                    <i class="far fa-heart"></i>
                                </button>
                                <button class="action-btn" title="Compartir">
                                    <i class="fas fa-share"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No hay recursos disponibles</h3>
            <p>Sé el primero en compartir material de estudio</p>
            <button class="upload-btn" onclick="showUploadModal()">
                <i class="fas fa-cloud-upload-alt"></i>
                Subir Recurso
            </button>
        </div>
    @endif
</div>

<!-- Upload Modal -->
<div class="upload-modal" id="uploadModal">
    <div class="upload-content">
        <div class="upload-header">
            <h3>Subir Nuevo Recurso</h3>
            <button onclick="hideUploadModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="upload-body">
            <form action="{{ route('resources.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="upload-zone" onclick="document.getElementById('fileInput').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <h4>Arrastra y suelta archivos aquí</h4>
                    <p>o haz clic para seleccionar</p>
                    <input type="file" id="fileInput" name="file" style="display: none;" required>
                </div>
                
                <div class="mt-4">
                    <div class="mb-3">
                        <label class="form-label">Título del Recurso</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select class="form-select" name="category" required>
                            <option value="document">Documento</option>
                            <option value="presentation">Presentación</option>
                            <option value="video">Video</option>
                            <option value="code">Código</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Proyecto Relacionado</label>
                        <select class="form-select" name="project_id">
                            <option value="">Ninguno</option>
                            @foreach($projects ?? [] as $project)
                                <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-upload me-2"></i>Subir Recurso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const resources = document.querySelectorAll('.resource-card');
    
    resources.forEach(resource => {
        const title = resource.querySelector('.resource-title').textContent.toLowerCase();
        const description = resource.querySelector('.resource-description').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            resource.style.display = '';
        } else {
            resource.style.display = 'none';
        }
    });
});

// Category filter
document.querySelectorAll('.category-pill').forEach(pill => {
    pill.addEventListener('click', function() {
        document.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        
        const category = this.dataset.category;
        const resources = document.querySelectorAll('.resource-card');
        
        resources.forEach(resource => {
            if (category === 'all' || resource.dataset.category === category) {
                resource.style.display = '';
            } else {
                resource.style.display = 'none';
            }
        });
    });
});

// View toggle
function setView(view) {
    const container = document.getElementById('resourcesContainer');
    const buttons = document.querySelectorAll('.view-toggle button');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.closest('button').classList.add('active');
    
    if (view === 'list') {
        container.classList.add('resources-list');
    } else {
        container.classList.remove('resources-list');
    }
}

// Upload modal
function showUploadModal() {
    document.getElementById('uploadModal').classList.add('show');
}

function hideUploadModal() {
    document.getElementById('uploadModal').classList.remove('show');
}

// Drag and drop
const uploadZone = document.querySelector('.upload-zone');

uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('dragover');
});

uploadZone.addEventListener('dragleave', () => {
    uploadZone.classList.remove('dragover');
});

uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('fileInput').files = files;
        uploadZone.innerHTML = `
            <i class="fas fa-file"></i>
            <h4>${files[0].name}</h4>
            <p>${(files[0].size / 1024 / 1024).toFixed(2)} MB</p>
        `;
    }
});

// File input change
document.getElementById('fileInput').addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
        const file = e.target.files[0];
        document.querySelector('.upload-zone').innerHTML = `
            <i class="fas fa-file"></i>
            <h4>${file.name}</h4>
            <p>${(file.size / 1024 / 1024).toFixed(2)} MB</p>
        `;
    }
});
</script>
@endpush