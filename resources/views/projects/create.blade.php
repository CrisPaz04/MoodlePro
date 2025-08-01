@extends('layouts.app')

@section('title', 'Crear Nuevo Proyecto')

@push('styles')
<style>
.create-project-container {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.form-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.section-header {
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.section-subtitle {
    color: #6c757d;
    margin: 0;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    display: block;
}

.required {
    color: #e74c3c;
}

.date-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.members-input-group {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.btn-add-member {
    background: #3498db;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-add-member:hover {
    background: #2980b9;
}

.members-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.member-tag {
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.remove-member {
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.remove-member:hover {
    opacity: 1;
    color: #e74c3c;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #dee2e6;
}

.btn-loading {
    position: relative;
}

.btn-loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    margin: auto;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.summary-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: fit-content;
    position: sticky;
    top: 2rem;
}

.summary-item {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f8f9fa;
}

.summary-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.summary-label {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.summary-value {
    color: #2c3e50;
    font-weight: 500;
}

.summary-empty {
    color: #adb5bd;
    font-style: italic;
}

.error-alert {
    background: #f8d7da;
    color: #721c24;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
    border: 1px solid #f5c6cb;
}

.success-alert {
    background: #d4edda;
    color: #155724;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
    border: 1px solid #c3e6cb;
}

@media (max-width: 768px) {
    .create-project-container {
        grid-template-columns: 1fr;
        padding: 1rem;
    }
    
    .date-row {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@section('content')
<div class="create-project-container">
    <!-- Main Form Section -->
    <div class="form-section">
        <div class="section-header">
            <h1 class="section-title">Crear Nuevo Proyecto</h1>
            <p class="section-subtitle">Inicia un nuevo proyecto académico y colabora con tu equipo</p>
        </div>

        <!-- Messages -->
        @if(session('error'))
            <div class="error-alert">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="success-alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Form -->
        <form id="createProjectForm" action="{{ route('projects.store') }}" method="POST">
            @csrf

            <!-- Project Title -->
            <div class="form-group">
                <label for="title" class="form-label">
                    Título del Proyecto <span class="required">*</span>
                </label>
                <input type="text" 
                       class="form-control @error('title') is-invalid @enderror" 
                       id="title" 
                       name="title" 
                       value="{{ old('title') }}"
                       placeholder="Ej: Desarrollo de App Móvil para Gestión Estudiantil"
                       maxlength="255"
                       required>
                <div class="form-text">Máximo 255 caracteres</div>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Project Description -->
            <div class="form-group">
                <label for="description" class="form-label">
                    Descripción del Proyecto
                </label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" 
                          name="description" 
                          placeholder="Describe brevemente los objetivos y alcance del proyecto..."
                          rows="4">{{ old('description') }}</textarea>
                <div class="form-text">Opcional: Proporciona detalles adicionales sobre el proyecto</div>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Dates -->
            <div class="date-row">
                <div class="form-group">
                    <label for="start_date" class="form-label">
                        Fecha de Inicio <span class="required">*</span>
                    </label>
                    <input type="date" 
                           class="form-control @error('start_date') is-invalid @enderror" 
                           id="start_date" 
                           name="start_date"
                           value="{{ old('start_date', date('Y-m-d')) }}"
                           required>
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="deadline" class="form-label">
                        Fecha de Entrega <span class="required">*</span>
                    </label>
                    <input type="date" 
                           class="form-control @error('deadline') is-invalid @enderror" 
                           id="deadline" 
                           name="deadline"
                           value="{{ old('deadline') }}"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           required>
                    @error('deadline')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Team Members -->
            <div class="form-group">
                <label class="form-label">Miembros del Equipo</label>
                <div class="members-input-group">
                    <input type="email" 
                           class="form-control" 
                           id="memberEmail" 
                           placeholder="correo@ejemplo.com">
                    <button type="button" class="btn-add-member" onclick="addMember()">
                        <i class="fas fa-plus"></i> Agregar
                    </button>
                </div>
                <div class="members-list" id="membersList">
                    <!-- Members will be added here dynamically -->
                </div>
                <div class="form-text">
                    Puedes agregar miembros ahora o después de crear el proyecto.<br>
                    <strong>Importante:</strong> Solo se agregarán usuarios que ya estén registrados en el sistema.
                </div>
                
                <!-- Error alert for members -->
                <div id="membersError" class="error-alert" style="display: none; margin-top: 0.5rem;">
                    <i class="fas fa-exclamation-triangle"></i> <span id="membersErrorText"></span>
                </div>
            </div>

            <!-- Hidden input for members -->
            <input type="hidden" name="members" id="membersInput" value="">

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-check"></i> Crear Proyecto
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="section-header">
            <h2 class="section-title">Resumen del Proyecto</h2>
            <p class="section-subtitle">Vista previa de tu proyecto</p>
        </div>

        <div class="summary-content">
            <div class="summary-item">
                <div class="summary-label">Título</div>
                <div class="summary-value" id="summaryTitle">
                    <span class="summary-empty">Sin título aún</span>
                </div>
            </div>

            <div class="summary-item">
                <div class="summary-label">Descripción</div>
                <div class="summary-value" id="summaryDescription">
                    <span class="summary-empty">Sin descripción</span>
                </div>
            </div>

            <div class="summary-item">
                <div class="summary-label">Fecha de Inicio</div>
                <div class="summary-value" id="summaryStartDate">
                    <span class="summary-empty">No definida</span>
                </div>
            </div>

            <div class="summary-item">
                <div class="summary-label">Fecha de Entrega</div>
                <div class="summary-value" id="summaryDeadline">
                    <span class="summary-empty">No definida</span>
                </div>
            </div>

            <div class="summary-item">
                <div class="summary-label">Miembros del Equipo</div>
                <div class="summary-value" id="summaryMembers">
                    <span class="summary-empty">Solo tú</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Team members array
let teamMembers = [];

// Real-time form updates for summary
document.getElementById('title').addEventListener('input', function() {
    const summaryTitle = document.getElementById('summaryTitle');
    if (this.value.trim()) {
        summaryTitle.textContent = this.value;
    } else {
        summaryTitle.innerHTML = '<span class="summary-empty">Sin título aún</span>';
    }
});

document.getElementById('description').addEventListener('input', function() {
    const summaryDescription = document.getElementById('summaryDescription');
    if (this.value.trim()) {
        summaryDescription.textContent = this.value.substring(0, 100) + (this.value.length > 100 ? '...' : '');
    } else {
        summaryDescription.innerHTML = '<span class="summary-empty">Sin descripción</span>';
    }
});

document.getElementById('start_date').addEventListener('change', function() {
    const summaryStartDate = document.getElementById('summaryStartDate');
    if (this.value) {
        const date = new Date(this.value);
        summaryStartDate.textContent = date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } else {
        summaryStartDate.innerHTML = '<span class="summary-empty">No definida</span>';
    }
});

document.getElementById('deadline').addEventListener('change', function() {
    const summaryDeadline = document.getElementById('summaryDeadline');
    if (this.value) {
        const date = new Date(this.value);
        summaryDeadline.textContent = date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } else {
        summaryDeadline.innerHTML = '<span class="summary-empty">No definida</span>';
    }
});

// Date validation
document.getElementById('deadline').addEventListener('change', function() {
    const startDate = document.getElementById('start_date').value;
    const deadline = this.value;
    
    if (startDate && deadline && new Date(deadline) < new Date(startDate)) {
        alert('La fecha de entrega no puede ser anterior a la fecha de inicio');
        this.value = '';
        document.getElementById('summaryDeadline').innerHTML = '<span class="summary-empty">No definida</span>';
    }
});

// Add team member function
function addMember() {
    const emailInput = document.getElementById('memberEmail');
    const email = emailInput.value.trim();
    const errorDiv = document.getElementById('membersError');
    const errorText = document.getElementById('membersErrorText');
    
    // Hide previous errors
    errorDiv.style.display = 'none';
    
    // Validate email
    if (!email) {
        showMemberError('Por favor ingresa un correo electrónico');
        return;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showMemberError('Por favor ingresa un correo electrónico válido');
        return;
    }
    
    // Check if already added
    if (teamMembers.includes(email)) {
        showMemberError('Este miembro ya fue agregado');
        return;
    }
    
    // Add to array
    teamMembers.push(email);
    
    // Update UI
    updateMembersList();
    
    // Clear input
    emailInput.value = '';
    
    // Update hidden input
    document.getElementById('membersInput').value = JSON.stringify(teamMembers);
}

// Show member error
function showMemberError(message) {
    const errorDiv = document.getElementById('membersError');
    const errorText = document.getElementById('membersErrorText');
    
    errorText.textContent = message;
    errorDiv.style.display = 'block';
    
    // Hide after 5 seconds
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 5000);
}

// Remove team member
function removeMember(email) {
    teamMembers = teamMembers.filter(m => m !== email);
    updateMembersList();
    document.getElementById('membersInput').value = JSON.stringify(teamMembers);
}

// Update members list UI
function updateMembersList() {
    const membersList = document.getElementById('membersList');
    const summaryMembers = document.getElementById('summaryMembers');
    
    if (teamMembers.length === 0) {
        membersList.innerHTML = '';
        summaryMembers.innerHTML = '<span class="summary-empty">Solo tú</span>';
    } else {
        // Update form list
        membersList.innerHTML = teamMembers.map(email => `
            <div class="member-tag">
                <i class="fas fa-user"></i>
                ${email}
                <i class="fas fa-times remove-member" onclick="removeMember('${email}')" title="Remover miembro"></i>
            </div>
        `).join('');
        
        // Update summary
        summaryMembers.textContent = `Tú + ${teamMembers.length} miembro${teamMembers.length > 1 ? 's' : ''}`;
    }
}

// Handle form submission
document.getElementById('createProjectForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.classList.add('btn-loading');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando...';
});

// Handle enter key in member email input
document.getElementById('memberEmail').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        addMember();
    }
});

// Load old members if validation failed
@if(old('members'))
    try {
        teamMembers = @json(json_decode(old('members'), true)) || [];
        updateMembersList();
        document.getElementById('membersInput').value = @json(old('members'));
    } catch(e) {
        console.error('Error loading old members:', e);
    }
@endif
</script>
@endpush