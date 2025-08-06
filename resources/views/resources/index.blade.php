@extends('layouts.app')

@section('title', 'Biblioteca de Recursos - MoodlePro')

@push('styles')
<style>
    .library-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3rem 0;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .library-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        animation: fadeInUp 0.6s ease-out;
    }

    .library-header .lead {
        font-size: 1.1rem;
        opacity: 0.9;
        animation: fadeInUp 0.6s ease-out 0.1s both;
    }

    .header-stats {
        display: flex;
        gap: 2rem;
        margin-top: 2rem;
        animation: fadeInUp 0.6s ease-out 0.2s both;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-item i {
        font-size: 2rem;
        opacity: 0.8;
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .controls-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-box {
        flex: 1;
        min-width: 300px;
        position: relative;
    }

    .search-box input {
        padding-left: 3rem;
        border-radius: 50px;
        border: 1px solid #e3e6f0;
        transition: all 0.3s;
    }

    .search-box input:focus {
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        border-color: #667eea;
    }

    .search-box i {
        position: absolute;
        left: 1.2rem;
        top: 50%;
        transform: translateY(-50%);
        color: #858796;
    }

    .filter-group {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .view-toggle {
        display: flex;
        background: #f8f9fc;
        border-radius: 50px;
        padding: 0.25rem;
    }

    .view-toggle button {
        border: none;
        background: transparent;
        color: #858796;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        transition: all 0.3s;
        cursor: pointer;
    }

    .view-toggle button.active {
        background: white;
        color: #667eea;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .resources-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        animation: fadeIn 0.6s ease-out;
    }

    .resource-card {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.3s;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .resource-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .resource-preview {
        height: 180px;
        background: #f8f9fc;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .resource-preview i {
        font-size: 4rem;
        color: #d1d3e2;
    }

    .resource-category {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(102, 126, 234, 0.9);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.8rem;
    }

    .resource-body {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .resource-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }

    .resource-description {
        color: #858796;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .resource-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.85rem;
        color: #858796;
    }

    .resource-author {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .resource-stats {
        display: flex;
        gap: 1rem;
    }

    .resource-stats span {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .rating {
        color: #f6c23e;
    }

    .resources-list .resource-card {
        flex-direction: row;
        margin-bottom: 1rem;
    }

    .resources-list .resource-preview {
        width: 200px;
        height: auto;
    }

    .resources-list .resource-body {
        flex: 1;
    }

    .upload-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s;
        box-shadow: 0 4px 6px rgba(102, 126, 234, 0.25);
    }

    .upload-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(102, 126, 234, 0.35);
    }

    /* Modal de subida */
    .upload-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1050;
        animation: fadeIn 0.3s;
    }

    .upload-modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .upload-content {
        background: white;
        border-radius: 0.5rem;
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideIn 0.3s;
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
                    <div class="stat-number">{{ $stats['rating'] ?? 0 }}</div>
                    <div class="stat-label">Rating Promedio</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Controls -->
    <div class="controls-row">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" placeholder="Buscar recursos..." id="searchInput">
        </div>
        
        <div class="filter-group">
            <select class="form-select" id="categoryFilter">
                <option value="all">Todas las categorías</option>
                <option value="document">Documentos</option>
                <option value="presentation">Presentaciones</option>
                <option value="video">Videos</option>
                <option value="code">Código</option>
                <option value="other">Otros</option>
            </select>
            
            <select class="form-select" id="sortFilter">
                <option value="recent">Más recientes</option>
                <option value="popular">Más populares</option>
                <option value="rated">Mejor calificados</option>
            </select>
        </div>
        
        <div class="view-toggle">
            <button class="active" onclick="setView('grid')">
                <i class="fas fa-th"></i>
            </button>
            <button onclick="setView('list')">
                <i class="fas fa-list"></i>
            </button>
        </div>
        
        <button class="upload-btn" onclick="showUploadModal()">
            <i class="fas fa-upload me-2"></i>
            Subir Recurso
        </button>
    </div>

    <!-- Resources Grid/List -->
    @if($resources->count() > 0)
        <div class="resources-grid" id="resourcesContainer">
            @foreach($resources as $resource)
                <div class="resource-card">
                    <div class="resource-preview">
                        <i class="fas fa-{{ $resource->icon ?? 'file' }}"></i>
                        <span class="resource-category">{{ ucfirst($resource->category) }}</span>
                    </div>
                    <div class="resource-body">
                        <h3 class="resource-title">{{ $resource->title }}</h3>
                        <p class="resource-description">{{ $resource->description ?? 'Sin descripción' }}</p>
                        <div class="resource-meta">
                            <div class="resource-author">
                                <i class="fas fa-user-circle"></i>
                                <span>{{ $resource->uploader->name ?? 'Usuario' }}</span>
                            </div>
                            <div class="resource-stats">
                                <span class="rating">
                                    <i class="fas fa-star"></i>
                                    {{ number_format($resource->rating ?? 0, 1) }}
                                </span>
                                <span>
                                    <i class="fas fa-download"></i>
                                    {{ $resource->downloads_count ?? 0 }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('resources.show', $resource) }}" class="btn btn-sm btn-primary">
                                Ver detalles
                            </a>
                            <a href="{{ route('resources.download', $resource) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No hay recursos disponibles</h3>
            <p>¡Sé el primero en compartir material de estudio!</p>
            <button class="btn btn-primary" onclick="showUploadModal()">
                <i class="fas fa-upload me-2"></i>
                Subir el primer recurso
            </button>
        </div>
    @endif

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $resources->links() }}
    </div>
</div>

<!-- Modal de Subida -->
<div id="uploadModal" class="upload-modal">
    <div class="upload-content">
        <div class="upload-header">
            <h3>Subir Nuevo Recurso</h3>
            <button type="button" onclick="hideUploadModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="upload-body">
            <form action="{{ route('resources.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                
                <div class="upload-zone" id="uploadZone">
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
                            <option value="">Seleccionar categoría</option>
                            <option value="document">Documento</option>
                            <option value="presentation">Presentación</option>
                            <option value="video">Video</option>
                            <option value="code">Código</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Proyecto Relacionado (opcional)</label>
                        <select class="form-select" name="project_id">
                            <option value="">Ninguno</option>
                            @foreach($projects ?? [] as $project)
                                <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" onclick="hideUploadModal()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Subir Recurso
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Variables globales
let selectedFile = null;
const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');

// View toggle
function setView(view) {
    const container = document.getElementById('resourcesContainer');
    const buttons = document.querySelectorAll('.view-toggle button');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.closest('button').classList.add('active');
    
    if (view === 'list') {
        container.classList.remove('resources-grid');
        container.classList.add('resources-list');
    } else {
        container.classList.remove('resources-list');
        container.classList.add('resources-grid');
    }
}

// Modal functions
function showUploadModal() {
    document.getElementById('uploadModal').classList.add('show');
}

function hideUploadModal() {
    document.getElementById('uploadModal').classList.remove('show');
    document.getElementById('uploadForm').reset();
    resetUploadZone();
    selectedFile = null;
}

// Reset upload zone
function resetUploadZone() {
    uploadZone.innerHTML = `
        <i class="fas fa-cloud-upload-alt"></i>
        <h4>Arrastra y suelta archivos aquí</h4>
        <p>o haz clic para seleccionar</p>
        <input type="file" id="fileInput" name="file" style="display: none;" required>
    `;
    // Re-attach event listeners
    document.getElementById('fileInput').addEventListener('change', handleFileSelect);
}

// Handle file selection
function handleFileSelect(e) {
    const files = e.target.files;
    if (files.length > 0) {
        selectedFile = files[0];
        updateUploadZone(selectedFile);
    }
}

// Update upload zone with file info
function updateUploadZone(file) {
    uploadZone.innerHTML = `
        <i class="fas fa-file"></i>
        <h4>${file.name}</h4>
        <p>${(file.size / 1024 / 1024).toFixed(2)} MB</p>
        <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="resetFileSelection()">
            Cambiar archivo
        </button>
    `;
}

// Reset file selection
function resetFileSelection() {
    selectedFile = null;
    resetUploadZone();
}

// Click handler for upload zone
uploadZone.addEventListener('click', function(e) {
    if (!selectedFile && !e.target.closest('button')) {
        fileInput.click();
    }
});

// File input change handler
fileInput.addEventListener('change', handleFileSelect);

// Drag and drop handlers
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
        selectedFile = files[0];
        updateUploadZone(selectedFile);
    }
});

// Form submit handler
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    if (selectedFile) {
        // Create a new file input with the selected file
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(selectedFile);
        
        // Find or create the file input in the form
        let formFileInput = this.querySelector('input[name="file"]');
        if (!formFileInput) {
            formFileInput = document.createElement('input');
            formFileInput.type = 'file';
            formFileInput.name = 'file';
            formFileInput.style.display = 'none';
            this.appendChild(formFileInput);
        }
        
        formFileInput.files = dataTransfer.files;
    }
});

// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    // Aquí iría la lógica de búsqueda
    console.log('Buscando:', this.value);
});

// Category filter
document.getElementById('categoryFilter').addEventListener('change', function() {
    // Aquí iría la lógica de filtrado
    console.log('Filtrando por categoría:', this.value);
});

// Sort filter
document.getElementById('sortFilter').addEventListener('change', function() {
    // Aquí iría la lógica de ordenamiento
    console.log('Ordenando por:', this.value);
});
</script>
@endpush