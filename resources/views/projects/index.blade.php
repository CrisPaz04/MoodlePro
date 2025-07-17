@extends('layouts.app')

@section('title', 'Mis Proyectos - MoodlePro')

@push('styles')
<style>
    .projects-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 2rem 2rem;
    }

    .projects-header h1 {
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

    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .filters-section {
        background: white;
        padding: 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        margin-bottom: 2rem;
    }

    .filter-group {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 0.5rem 1rem;
        border: 2px solid #e3e6f0;
        background: white;
        color: #5a5c69;
        border-radius: 2rem;
        font-weight: 500;
        transition: all 0.3s;
        cursor: pointer;
    }

    .filter-btn.active,
    .filter-btn:hover {
        background: var(--bs-primary);
        color: white;
        border-color: var(--bs-primary);
    }

    .search-box {
        position: relative;
        flex: 1;
        min-width: 250px;
    }

    .search-box input {
        width: 100%;
        padding: 0.5rem 1rem 0.5rem 2.5rem;
        border: 2px solid #e3e6f0;
        border-radius: 2rem;
        transition: all 0.3s;
    }

    .search-box input:focus {
        border-color: var(--bs-primary);
        outline: none;
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #858796;
    }

    .create-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s;
    }

    .create-btn:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
    }

    .project-card {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        transition: all 0.3s;
        overflow: hidden;
        position: relative;
    }

    .project-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.15);
    }

    .project-status-indicator {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
    }

    .status-planning { background: #f6c23e; }
    .status-active { background: #1cc88a; }
    .status-completed { background: #4e73df; }
    .status-cancelled { background: #e74a3b; }

    .project-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e3e6f0;
    }

    .project-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2e3440;
        margin-bottom: 0.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .project-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.875rem;
        color: #858796;
    }

    .project-meta-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .project-body {
        padding: 1.5rem;
    }

    .project-description {
        color: #5a5c69;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 3.5rem;
    }

    .project-progress {
        margin-bottom: 1rem;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.875rem;
        color: #5a5c69;
        margin-bottom: 0.5rem;
    }

    .progress {
        height: 8px;
        border-radius: 4px;
        background-color: #e3e6f0;
    }

    .project-team {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .team-members {
        display: flex;
        margin-right: auto;
    }

    .team-member {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e3e6f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
        color: #5a5c69;
        margin-left: -8px;
        border: 2px solid white;
    }

    .team-member:first-child {
        margin-left: 0;
    }

    .team-member.more {
        background: #4e73df;
        color: white;
    }

    .project-actions {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        padding: 0.5rem;
        border: none;
        background: none;
        color: #858796;
        cursor: pointer;
        transition: all 0.3s;
    }

    .action-btn:hover {
        color: #4e73df;
        transform: scale(1.1);
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

    .empty-state h3 {
        font-size: 1.5rem;
        color: #5a5c69;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .projects-header h1 {
            font-size: 2rem;
        }

        .header-stats {
            flex-wrap: wrap;
            gap: 1rem;
        }

        .filter-group {
            flex-direction: column;
            width: 100%;
        }

        .search-box {
            width: 100%;
        }

        .projects-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<!-- Projects Header -->
<div class="projects-header">
    <div class="container">
        <h1>Mis Proyectos</h1>
        <p class="lead mb-0">Gestiona y colabora en todos tus proyectos académicos</p>
        <div class="header-stats">
            <div class="stat-item">
                <i class="fas fa-folder"></i>
                <div>
                    <div class="stat-number">{{ $stats['total'] ?? 0 }}</div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
            <div class="stat-item">
                <i class="fas fa-play-circle"></i>
                <div>
                    <div class="stat-number">{{ $stats['active'] ?? 0 }}</div>
                    <div class="stat-label">Activos</div>
                </div>
            </div>
            <div class="stat-item">
                <i class="fas fa-check-circle"></i>
                <div>
                    <div class="stat-number">{{ $stats['completed'] ?? 0 }}</div>
                    <div class="stat-label">Completados</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Filters Section -->
    <div class="filters-section">
        <div class="filter-group">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar proyectos..." id="searchInput">
            </div>
            <button class="filter-btn active" data-filter="all">Todos</button>
            <button class="filter-btn" data-filter="active">Activos</button>
            <button class="filter-btn" data-filter="planning">Planificación</button>
            <button class="filter-btn" data-filter="completed">Completados</button>
            <a href="{{ route('projects.create') }}" class="create-btn ms-auto">
                <i class="fas fa-plus"></i>
                Nuevo Proyecto
            </a>
        </div>
    </div>

    <!-- Projects Grid -->
    @if($projects->count() > 0)
        <div class="projects-grid">
            @foreach($projects as $project)
                <div class="project-card" data-status="{{ $project->status }}">
                    <div class="project-status-indicator status-{{ $project->status }}"></div>
                    
                    <div class="project-header">
                        <h3 class="project-title">{{ $project->title }}</h3>
                        <div class="project-meta">
                            <div class="project-meta-item">
                                <i class="fas fa-calendar"></i>
                                {{ $project->deadline->format('d M, Y') }}
                            </div>
                            <div class="project-meta-item">
                                <i class="fas fa-tasks"></i>
                                {{ $project->tasks->count() }} tareas
                            </div>
                        </div>
                    </div>
                    
                    <div class="project-body">
                        <p class="project-description">
                            {{ $project->description ?: 'Sin descripción disponible' }}
                        </p>
                        
                        <div class="project-progress">
                            <div class="progress-label">
                                <span>Progreso</span>
                                <span>{{ $project->progress ?? 0 }}%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $project->progress ?? 0 }}%"
                                     aria-valuenow="{{ $project->progress ?? 0 }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                        
                        <div class="project-team">
                            <div class="team-members">
                                @foreach($project->members->take(3) as $member)
                                    <div class="team-member" title="{{ $member->name }}">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                @endforeach
                                @if($project->members->count() > 3)
                                    <div class="team-member more">
                                        +{{ $project->members->count() - 3 }}
                                    </div>
                                @endif
                            </div>
                            
                            <div class="project-actions">
                                <a href="{{ route('projects.show', $project) }}" class="action-btn" title="Ver proyecto">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($project->creator_id === auth()->id())
                                    <a href="{{ route('projects.edit', $project) }}" class="action-btn" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="action-btn" title="Eliminar" 
                                            onclick="confirmDelete({{ $project->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No tienes proyectos aún</h3>
            <p>¡Crea tu primer proyecto y comienza a colaborar!</p>
            <a href="{{ route('projects.create') }}" class="create-btn">
                <i class="fas fa-plus"></i>
                Crear Proyecto
            </a>
        </div>
    @endif
</div>

<!-- Delete Form (Hidden) -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const projects = document.querySelectorAll('.project-card');
    
    projects.forEach(project => {
        const title = project.querySelector('.project-title').textContent.toLowerCase();
        const description = project.querySelector('.project-description').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            project.style.display = '';
        } else {
            project.style.display = 'none';
        }
    });
});

// Filter functionality
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Update active state
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        const projects = document.querySelectorAll('.project-card');
        
        projects.forEach(project => {
            if (filter === 'all' || project.dataset.status === filter) {
                project.style.display = '';
            } else {
                project.style.display = 'none';
            }
        });
    });
});

// Delete confirmation
function confirmDelete(projectId) {
    if (confirm('¿Estás seguro de que quieres eliminar este proyecto? Esta acción no se puede deshacer.')) {
        const form = document.getElementById('delete-form');
        form.action = `/projects/${projectId}`;
        form.submit();
    }
}
</script>
@endpush