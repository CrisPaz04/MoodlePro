@extends('layouts.app')

@section('title', 'Crear Nuevo Proyecto - MoodlePro')

@push('styles')
<style>
    .create-project-container {
        display: flex;
        gap: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .form-section, .summary-section {
        background: white;
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .form-section {
        flex: 1.5;
    }

    .summary-section {
        flex: 1;
        position: sticky;
        top: 2rem;
        height: fit-content;
    }

    .section-header {
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2e3440;
        margin-bottom: 0.5rem;
    }

    .section-subtitle {
        color: #858796;
        font-size: 0.875rem;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #5a5c69;
        font-size: 0.875rem;
    }

    .form-label .required {
        color: #e74a3b;
        margin-left: 0.25rem;
    }

    .form-control, .form-select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d3e2;
        border-radius: 0.35rem;
        font-size: 0.875rem;
        transition: all 0.3s;
        background-color: #fff;
    }

    .form-control:focus, .form-select:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        outline: none;
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .form-text {
        font-size: 0.75rem;
        color: #858796;
        margin-top: 0.25rem;
    }

    /* Date Inputs Row */
    .date-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    /* Project Type Cards */
    .project-types {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .type-card {
        border: 2px solid #e3e6f0;
        border-radius: 0.5rem;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background-color: #fff;
    }

    .type-card:hover {
        border-color: #4e73df;
        transform: translateY(-2px);
    }

    .type-card.selected {
        border-color: #4e73df;
        background-color: rgba(78, 115, 223, 0.1);
    }

    .type-card input[type="radio"] {
        display: none;
    }

    .type-icon {
        font-size: 2rem;
        color: #4e73df;
        margin-bottom: 0.5rem;
    }

    .type-name {
        font-weight: 600;
        color: #2e3440;
        font-size: 0.875rem;
    }

    /* Team Members */
    .members-input-group {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .members-input-group .form-control {
        flex: 1;
    }

    .btn-add-member {
        padding: 0.75rem 1rem;
        background-color: #4e73df;
        color: white;
        border: none;
        border-radius: 0.35rem;
        cursor: pointer;
        transition: all 0.3s;
        white-space: nowrap;
    }

    .btn-add-member:hover {
        background-color: #2e59d9;
    }

    .members-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .member-tag {
        background-color: #e3e6f0;
        color: #5a5c69;
        padding: 0.375rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .member-tag .remove-member {
        cursor: pointer;
        color: #858796;
        transition: color 0.3s;
    }

    .member-tag .remove-member:hover {
        color: #e74a3b;
    }

    /* Summary Section */
    .summary-content {
        padding: 1.5rem;
        background-color: #f8f9fc;
        border-radius: 0.5rem;
    }

    .summary-item {
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e3e6f0;
    }

    .summary-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .summary-label {
        font-size: 0.75rem;
        color: #858796;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .summary-value {
        color: #2e3440;
        font-weight: 500;
    }

    .summary-empty {
        color: #b7b9cc;
        font-style: italic;
    }

    /* Action Buttons */
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e3e6f0;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 0.35rem;
        font-weight: 500;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-secondary {
        background-color: #858796;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #6c6e7e;
    }

    .btn-primary {
        background-color: #4e73df;
        color: white;
    }

    .btn-primary:hover {
        background-color: #2e59d9;
        transform: translateY(-1px);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .create-project-container {
            flex-direction: column;
        }

        .summary-section {
            position: static;
        }

        .date-row {
            grid-template-columns: 1fr;
        }
    }

    /* Loading State */
    .btn-loading {
        position: relative;
        pointer-events: none;
        opacity: 0.7;
    }

    .btn-loading::after {
        content: "";
        position: absolute;
        width: 16px;
        height: 16px;
        margin: auto;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        border: 2px solid transparent;
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
<div class="create-project-container">
    <!-- Form Section -->
    <div class="form-section">
        <div class="section-header">
            <h1 class="section-title">Crear Nuevo Proyecto</h1>
            <p class="section-subtitle">Complete los detalles para iniciar tu proyecto académico</p>
        </div>

        <form method="POST" action="{{ route('projects.store') }}" id="createProjectForm">
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
                       placeholder="Ej: Sistema de Gestión Académica"
                       value="{{ old('title') }}"
                       required>
                <div class="form-text">Ingrese un título descriptivo para tu proyecto</div>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Project Type -->
            <div class="form-group">
                <label class="form-label">Tipo de Proyecto</label>
                <div class="project-types">
                    <label class="type-card">
                        <input type="radio" name="project_type" value="thesis" checked>
                        <i class="fas fa-graduation-cap type-icon"></i>
                        <div class="type-name">Tesis</div>
                    </label>
                    <label class="type-card">
                        <input type="radio" name="project_type" value="capstone">
                        <i class="fas fa-project-diagram type-icon"></i>
                        <div class="type-name">Proyecto Final</div>
                    </label>
                    <label class="type-card">
                        <input type="radio" name="project_type" value="research">
                        <i class="fas fa-microscope type-icon"></i>
                        <div class="type-name">Investigación</div>
                    </label>
                    <label class="type-card">
                        <input type="radio" name="project_type" value="group">
                        <i class="fas fa-users type-icon"></i>
                        <div class="type-name">Trabajo Grupal</div>
                    </label>
                </div>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description" class="form-label">
                    Descripción
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
                <div class="form-text">Puedes agregar miembros ahora o después de crear el proyecto</div>
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
                <div class="summary-label">Tipo de Proyecto</div>
                <div class="summary-value" id="summaryType">Tesis</div>
            </div>

            <div class="summary-item">
                <div class="summary-label">Descripción</div>
                <div class="summary-value" id="summaryDescription">
                    <span class="summary-empty">Sin descripción</span>
                </div>
            </div>

            <div class="summary-item">
                <div class="summary-label">Duración</div>
                <div class="summary-value" id="summaryDuration">
                    <span class="summary-empty">Selecciona las fechas</span>
                </div>
            </div>

            <div class="summary-item">
                <div class="summary-label">Fecha de Inicio</div>
                <div class="summary-value" id="summaryStartDate">{{ date('d/m/Y') }}</div>
            </div>

            <div class="summary-item">
                <div class="summary-label">Fecha de Entrega</div>
                <div class="summary-value" id="summaryDeadline">
                    <span class="summary-empty">Sin definir</span>
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
// Array to store team members
let teamMembers = [];

// Project type names
const projectTypeNames = {
    'thesis': 'Tesis',
    'capstone': 'Proyecto Final',
    'research': 'Investigación',
    'group': 'Trabajo Grupal'
};

// Update summary in real-time
document.addEventListener('DOMContentLoaded', function() {
    // Title update
    document.getElementById('title').addEventListener('input', function() {
        const value = this.value.trim();
        const summaryTitle = document.getElementById('summaryTitle');
        summaryTitle.innerHTML = value || '<span class="summary-empty">Sin título aún</span>';
    });

    // Description update
    document.getElementById('description').addEventListener('input', function() {
        const value = this.value.trim();
        const summaryDesc = document.getElementById('summaryDescription');
        summaryDesc.innerHTML = value || '<span class="summary-empty">Sin descripción</span>';
    });

    // Project type update
    document.querySelectorAll('input[name="project_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Update selected card style
            document.querySelectorAll('.type-card').forEach(card => {
                card.classList.remove('selected');
            });
            this.closest('.type-card').classList.add('selected');
            
            // Update summary
            document.getElementById('summaryType').textContent = projectTypeNames[this.value];
        });
    });

    // Set initial selected type
    document.querySelector('input[name="project_type"]:checked').closest('.type-card').classList.add('selected');

    // Date updates
    document.getElementById('start_date').addEventListener('change', updateDates);
    document.getElementById('deadline').addEventListener('change', updateDates);

    // Initialize dates
    updateDates();
});

// Update dates in summary
function updateDates() {
    const startDate = document.getElementById('start_date').value;
    const deadline = document.getElementById('deadline').value;
    
    if (startDate) {
        const formattedStart = new Date(startDate).toLocaleDateString('es-ES');
        document.getElementById('summaryStartDate').textContent = formattedStart;
        
        // Update min date for deadline
        document.getElementById('deadline').min = startDate;
    }
    
    if (deadline) {
        const formattedDeadline = new Date(deadline).toLocaleDateString('es-ES');
        document.getElementById('summaryDeadline').textContent = formattedDeadline;
    }
    
    // Calculate duration
    if (startDate && deadline) {
        const start = new Date(startDate);
        const end = new Date(deadline);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        let duration = '';
        if (diffDays < 7) {
            duration = `${diffDays} días`;
        } else if (diffDays < 30) {
            duration = `${Math.ceil(diffDays / 7)} semanas`;
        } else {
            duration = `${Math.ceil(diffDays / 30)} meses`;
        }
        
        document.getElementById('summaryDuration').textContent = duration;
    }
}

// Add team member
function addMember() {
    const emailInput = document.getElementById('memberEmail');
    const email = emailInput.value.trim();
    
    if (!email) return;
    
    // Validate email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Por favor ingresa un correo electrónico válido');
        return;
    }
    
    // Check if already added
    if (teamMembers.includes(email)) {
        alert('Este miembro ya fue agregado');
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
                ${email}
                <i class="fas fa-times remove-member" onclick="removeMember('${email}')"></i>
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
});

// Handle enter key in member email input
document.getElementById('memberEmail').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        addMember();
    }
});
</script>
@endpush