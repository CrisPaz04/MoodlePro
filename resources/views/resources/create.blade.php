@extends('layouts.app')

@section('title', 'Subir Recurso - MoodlePro')

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
                        <li class="breadcrumb-item active">Subir Recurso</li>
                    </ol>
                </nav>
                <h1 class="dashboard-title">
                    <i class="fas fa-cloud-upload-alt me-3"></i>
                    Subir Nuevo Recurso
                </h1>
                <p class="dashboard-subtitle">
                    Comparte material de estudio con tu equipo
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Main Upload Form -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('resources.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        
                        <!-- Upload Zone -->
                        <div class="upload-section mb-4">
                            <div class="upload-zone" id="uploadZone">
                                <div class="upload-content">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <h4 class="upload-title">Arrastra y suelta tu archivo aquí</h4>
                                    <p class="upload-subtitle">o haz clic para seleccionar</p>
                                    <div class="upload-specs">
                                        <span>Tamaño máximo: 50MB</span>
                                        <span>•</span>
                                        <span>Formatos: PDF, DOC, PPT, MP4, ZIP, etc.</span>
                                    </div>
                                </div>
                                <input type="file" name="file" id="fileInput" required accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.avi,.zip,.rar,.jpg,.png">
                            </div>
                            
                            <!-- File Preview -->
                            <div class="file-preview" id="filePreview" style="display: none;">
                                <div class="file-info">
                                    <div class="file-icon">
                                        <i class="fas fa-file"></i>
                                    </div>
                                    <div class="file-details">
                                        <div class="file-name"></div>
                                        <div class="file-size"></div>
                                    </div>
                                    <button type="button" class="remove-file" onclick="removeFile()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="upload-progress" style="display: none;">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resource Information -->
                        <div class="resource-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label required">
                                            <i class="fas fa-heading me-2"></i>Título del Recurso
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('title') is-invalid @enderror" 
                                               id="title" 
                                               name="title" 
                                               value="{{ old('title') }}" 
                                               required 
                                               placeholder="Ej: Guía de Programación en Laravel">
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category" class="form-label required">
                                            <i class="fas fa-tags me-2"></i>Categoría
                                        </label>
                                        <select class="form-select @error('category') is-invalid @enderror" 
                                                id="category" 
                                                name="category" 
                                                required>
                                            <option value="">Selecciona una categoría</option>
                                            <option value="document" {{ old('category') == 'document' ? 'selected' : '' }}>
                                                📄 Documento
                                            </option>
                                            <option value="presentation" {{ old('category') == 'presentation' ? 'selected' : '' }}>
                                                📊 Presentación
                                            </option>
                                            <option value="video" {{ old('category') == 'video' ? 'selected' : '' }}>
                                                🎥 Video
                                            </option>
                                            <option value="code" {{ old('category') == 'code' ? 'selected' : '' }}>
                                                💻 Código
                                            </option>
                                            <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>
                                                📁 Otro
                                            </option>
                                        </select>
                                        @error('category')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left me-2"></i>Descripción
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="4" 
                                          placeholder="Describe brevemente el contenido del recurso y para qué puede ser útil...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Una buena descripción ayuda a otros usuarios a encontrar y entender tu recurso
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="project_id" class="form-label">
                                    <i class="fas fa-folder me-2"></i>Proyecto Relacionado
                                </label>
                                <select class="form-select @error('project_id') is-invalid @enderror" 
                                        id="project_id" 
                                        name="project_id">
                                    <option value="">Sin proyecto específico</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                            {{ $project->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Opcional: Asocia este recurso con un proyecto específico
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('resources.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Cancelar
                                </a>
                                
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary" onclick="saveDraft()">
                                        <i class="fas fa-save me-2"></i>Guardar Borrador
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-cloud-upload-alt me-2"></i>Subir Recurso
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Upload Tips -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Consejos para subir recursos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="tip-item">
                                <div class="tip-icon">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                                <div class="tip-content">
                                    <h6>Nombres descriptivos</h6>
                                    <p>Usa títulos claros que describan el contenido del archivo</p>
                                </div>
                            </div>
                            <div class="tip-item">
                                <div class="tip-icon">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                                <div class="tip-content">
                                    <h6>Categorías correctas</h6>
                                    <p>Selecciona la categoría adecuada para facilitar la búsqueda</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="tip-item">
                                <div class="tip-icon">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                                <div class="tip-content">
                                    <h6>Archivos optimizados</h6>
                                    <p>Reduce el tamaño de archivos grandes para descargas más rápidas</p>
                                </div>
                            </div>
                            <div class="tip-item">
                                <div class="tip-icon">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                                <div class="tip-content">
                                    <h6>Descripciones útiles</h6>
                                    <p>Incluye información sobre el contenido y su propósito</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.upload-zone {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    background: #f9fafb;
    padding: 3rem 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.upload-zone:hover,
.upload-zone.dragover {
    border-color: #4f46e5;
    background: #f0f9ff;
}

.upload-zone input[type="file"] {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.upload-content {
    pointer-events: none;
}

.upload-icon {
    font-size: 3rem;
    color: #6b7280;
    margin-bottom: 1rem;
}

.upload-zone:hover .upload-icon {
    color: #4f46e5;
}

.upload-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.upload-subtitle {
    color: #6b7280;
    margin-bottom: 1rem;
}

.upload-specs {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #9ca3af;
}

.file-preview {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.file-icon {
    width: 48px;
    height: 48px;
    background: #f3f4f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #6b7280;
}

.file-details {
    flex: 1;
}

.file-name {
    font-weight: 600;
    color: #374151;
}

.file-size {
    font-size: 0.875rem;
    color: #6b7280;
}

.remove-file {
    background: none;
    border: none;
    color: #dc2626;
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.remove-file:hover {
    background: #fef2f2;
}

.form-label.required::after {
    content: " *";
    color: #dc2626;
}

.tip-item {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.tip-icon {
    flex-shrink: 0;
}

.tip-content h6 {
    margin-bottom: 0.25rem;
    color: #374151;
}

.tip-content p {
    margin-bottom: 0;
    color: #6b7280;
    font-size: 0.875rem;
}

.form-actions {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
    .upload-zone {
        padding: 2rem 1rem;
    }
    
    .upload-specs {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .form-actions .d-flex {
        flex-direction: column;
        gap: 1rem;
    }
    
    .form-actions .d-flex:last-child {
        flex-direction: row;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    const filePreview = document.getElementById('filePreview');
    const submitBtn = document.getElementById('submitBtn');
    
    // Click to upload
    uploadZone.addEventListener('click', () => {
        fileInput.click();
    });
    
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
            fileInput.files = files;
            handleFileSelect(files[0]);
        }
    });
    
    // File input change
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });
    
    function handleFileSelect(file) {
        // Validate file size (50MB)
        if (file.size > 50 * 1024 * 1024) {
            alert('El archivo es demasiado grande. Máximo 50MB.');
            return;
        }
        
        // Show file preview
        document.querySelector('.file-name').textContent = file.name;
        document.querySelector('.file-size').textContent = formatFileSize(file.size);
        
        // Update file icon based on type
        const icon = getFileIcon(file.name);
        document.querySelector('.file-icon i').className = icon;
        
        // Show preview, hide upload zone
        uploadZone.style.display = 'none';
        filePreview.style.display = 'block';
        
        // Auto-fill title if empty
        const titleInput = document.getElementById('title');
        if (!titleInput.value) {
            titleInput.value = file.name.replace(/\.[^/.]+$/, "");
        }
    }
    
    // Form submission with loading state
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Subiendo...';
        submitBtn.disabled = true;
    });
});

function removeFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('uploadZone').style.display = 'block';
    document.getElementById('filePreview').style.display = 'none';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const icons = {
        'pdf': 'fas fa-file-pdf text-danger',
        'doc': 'fas fa-file-word text-primary',
        'docx': 'fas fa-file-word text-primary',
        'ppt': 'fas fa-file-powerpoint text-warning',
        'pptx': 'fas fa-file-powerpoint text-warning',
        'xls': 'fas fa-file-excel text-success',
        'xlsx': 'fas fa-file-excel text-success',
        'mp4': 'fas fa-file-video text-info',
        'avi': 'fas fa-file-video text-info',
        'mp3': 'fas fa-file-audio text-purple',
        'zip': 'fas fa-file-archive text-secondary',
        'rar': 'fas fa-file-archive text-secondary',
        'jpg': 'fas fa-file-image text-primary',
        'png': 'fas fa-file-image text-primary',
        'gif': 'fas fa-file-image text-primary'
    };
    
    return icons[ext] || 'fas fa-file text-secondary';
}

function saveDraft() {
    // Save form data to localStorage
    const formData = new FormData(document.getElementById('uploadForm'));
    const data = {};
    for (let [key, value] of formData.entries()) {
        if (key !== 'file') {
            data[key] = value;
        }
    }
    localStorage.setItem('resourceDraft', JSON.stringify(data));
    
    // Show feedback
    const btn = event.target;
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check me-2"></i>Guardado';
    btn.classList.add('btn-success');
    btn.classList.remove('btn-outline-primary');
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-primary');
    }, 2000);
}

// Load draft on page load
document.addEventListener('DOMContentLoaded', function() {
    const draft = localStorage.getItem('resourceDraft');
    if (draft) {
        const data = JSON.parse(draft);
        Object.keys(data).forEach(key => {
            const input = document.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = data[key];
            }
        });
    }
});
</script>
@endsection