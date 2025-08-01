@extends('layouts.app')

@section('title', 'Gestionar Miembros - ' . $project->title)

@push('styles')
<style>
.members-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 2rem;
}

.page-header {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #dee2e6;
}

.project-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.project-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.project-details h1 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.8rem;
    font-weight: 700;
}

.project-details p {
    margin: 0;
    color: #6c757d;
}

.actions-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #dee2e6;
}

.add-member-form {
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 1rem;
    align-items: end;
}

.members-list-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #dee2e6;
}

.member-item {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 1rem;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.member-item:hover {
    background: #e9ecef;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.member-item:last-child {
    margin-bottom: 0;
}

.member-avatar {
    margin-right: 1.5rem;
    flex-shrink: 0;
}

.avatar-img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.avatar-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.5rem;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.member-info {
    flex: 1;
}

.member-name {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.member-email {
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.member-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.role-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.role-coordinator {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.role-member {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #b8daff;
}

.creator-badge {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.joined-date {
    color: #6c757d;
    font-size: 0.9rem;
}

.member-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.btn-remove {
    background: #dc3545;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-remove:hover {
    background: #c82333;
    transform: translateY(-1px);
}

.btn-remove:disabled {
    background: #6c757d;
    cursor: not-allowed;
    transform: none;
}

.no-members-message {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

.no-members-message i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #dee2e6;
}

.search-section {
    margin-bottom: 2rem;
}

.search-input {
    position: relative;
}

.search-input input {
    padding-left: 2.5rem;
}

.search-input i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

@media (max-width: 768px) {
    .members-container {
        padding: 1rem;
    }
    
    .add-member-form {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .member-item {
        padding: 1rem;
    }
    
    .member-avatar {
        margin-right: 1rem;
    }
    
    .avatar-img,
    .avatar-placeholder {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .member-meta {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .member-actions {
        margin-top: 1rem;
        width: 100%;
        justify-content: flex-end;
    }
}
</style>
@endpush

@section('content')
<div class="members-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="project-info">
                <div class="project-icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="project-details">
                    <h1>{{ $project->title }}</h1>
                    <p>Gestión de Miembros del Equipo</p>
                </div>
            </div>
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Proyecto
            </a>
        </div>
        
        <div class="row text-center">
            <div class="col-md-4">
                <div class="stat-item">
                    <h3 class="text-primary">{{ $members->count() }}</h3>
                    <p class="text-muted mb-0">Miembros Totales</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <h3 class="text-warning">{{ $members->where('pivot.role', 'coordinator')->count() }}</h3>
                    <p class="text-muted mb-0">Coordinadores</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <h3 class="text-info">{{ $members->where('pivot.role', 'member')->count() }}</h3>
                    <p class="text-muted mb-0">Miembros</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Add Member Section -->
    <div class="actions-section">
        <h4 class="mb-3">
            <i class="fas fa-user-plus text-primary"></i>
            Agregar Nuevo Miembro
        </h4>
        
        <form action="{{ route('projects.addMember', $project) }}" method="POST">
            @csrf
            <div class="add-member-form">
                <div class="form-group">
                    <label for="user_id" class="form-label">Seleccionar Usuario</label>
                    <select class="form-select @error('user_id') is-invalid @enderror" 
                            id="user_id" 
                            name="user_id" 
                            required>
                        <option value="">Selecciona un usuario...</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    
                    @if($availableUsers->count() === 0)
                        <div class="form-text text-warning">
                            <i class="fas fa-info-circle"></i>
                            No hay usuarios disponibles para agregar. Todos los usuarios registrados ya son miembros del proyecto.
                        </div>
                    @endif
                </div>
                
                <div class="form-group">
                    <label for="role" class="form-label">Rol</label>
                    <select class="form-select @error('role') is-invalid @enderror" 
                            id="role" 
                            name="role" 
                            required>
                        <option value="member">Miembro</option>
                        <option value="coordinator">Coordinador</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <button type="submit" 
                            class="btn btn-primary"
                            {{ $availableUsers->count() === 0 ? 'disabled' : '' }}>
                        <i class="fas fa-plus"></i>
                        Agregar Miembro
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Search Section -->
    @if($members->count() > 5)
        <div class="search-section">
            <div class="search-input">
                <input type="text" 
                       class="form-control" 
                       id="memberSearch" 
                       placeholder="Buscar miembros por nombre o email...">
                <i class="fas fa-search"></i>
            </div>
        </div>
    @endif

    <!-- Members List -->
    <div class="members-list-section">
        <h4 class="mb-3">
            <i class="fas fa-users text-primary"></i>
            Miembros Actuales ({{ $members->count() }})
        </h4>
        
        @if($members->count() > 0)
            <div id="membersList">
                @foreach($members as $member)
                    <div class="member-item" data-member-name="{{ strtolower($member->name) }}" data-member-email="{{ strtolower($member->email) }}">
                        <div class="member-avatar">
                            @if($member->avatar)
                                <img src="{{ asset('storage/' . $member->avatar) }}" alt="{{ $member->name }}" class="avatar-img">
                            @else
                                <div class="avatar-placeholder">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        
                        <div class="member-info">
                            <div class="member-name">
                                {{ $member->name }}
                                @if($member->id === Auth::id())
                                    <span class="badge bg-success ms-2">Tú</span>
                                @endif
                            </div>
                            <div class="member-email">{{ $member->email }}</div>
                            <div class="member-meta">
                                <span class="role-badge {{ $member->pivot->role === 'coordinator' ? 'role-coordinator' : 'role-member' }}">
                                    <i class="fas {{ $member->pivot->role === 'coordinator' ? 'fa-crown' : 'fa-user' }}"></i>
                                    {{ $member->pivot->role === 'coordinator' ? 'Coordinador' : 'Miembro' }}
                                </span>
                                
                                @if($member->id === $project->creator_id)
                                    <span class="role-badge creator-badge">
                                        <i class="fas fa-star"></i>
                                        Creador
                                    </span>
                                @endif
                                
                                <span class="joined-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    Se unió: {{ Carbon\Carbon::parse($member->pivot->joined_at)->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="member-actions">
                            @if($member->id !== $project->creator_id && $member->id !== Auth::id())
                                <form action="{{ route('projects.removeMember', [$project, $member]) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('¿Estás seguro de que quieres remover a {{ $member->name }} del proyecto?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-remove">
                                        <i class="fas fa-trash"></i>
                                        Remover
                                    </button>
                                </form>
                            @elseif($member->id === $project->creator_id)
                                <button class="btn-remove" disabled title="No se puede remover al creador del proyecto">
                                    <i class="fas fa-lock"></i>
                                    Protegido
                                </button>
                            @else
                                <button class="btn btn-outline-secondary" disabled>
                                    <i class="fas fa-user-check"></i>
                                    Eres tú
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="no-members-message">
                <i class="fas fa-users"></i>
                <h5>No hay miembros en este proyecto</h5>
                <p>Agrega miembros para comenzar la colaboración</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Search functionality
document.getElementById('memberSearch')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const memberItems = document.querySelectorAll('.member-item');
    
    memberItems.forEach(item => {
        const name = item.getAttribute('data-member-name');
        const email = item.getAttribute('data-member-email');
        
        if (name.includes(searchTerm) || email.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
});

// Auto-hide alerts
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (alert.classList.contains('show')) {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.click();
            }
        }
    });
}, 5000);
</script>
@endpush