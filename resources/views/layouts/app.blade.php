<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MoodlePro')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #2e3440;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Figtree', sans-serif;
        }

        /* Sidebar Styles */
        .sidebar {
            background-color: #fff;
            border-right: 1px solid #dee2e6;
            min-height: 100vh;
            padding: 1.5rem 1rem 4rem;
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            z-index: 100;
            overflow-y: auto;
            transition: all 0.3s;
        }

        .sidebar-collapsed {
            width: 80px;
        }

        .sidebar-collapsed .sidebar-brand-text,
        .sidebar-collapsed .nav-section-title span,
        .sidebar-collapsed .nav-link span,
        .sidebar-collapsed .sidebar-footer-content {
            display: none;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            text-decoration: none;
            color: var(--primary-color);
        }

        .sidebar-brand-icon {
            font-size: 2rem;
            margin-right: 0.5rem;
        }

        .sidebar-brand-text {
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: 1px;
        }

        .sidebar .nav-link {
            color: #495057;
            padding: 0.75rem 1rem;
            font-weight: 500;
            border-radius: 0.35rem;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            color: var(--primary-color);
            background-color: rgba(78, 115, 223, 0.1);
        }

        .sidebar .nav-link.active {
            color: #fff;
            background-color: var(--primary-color);
        }

        .sidebar .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        .nav-section-title {
            font-weight: 600;
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 2rem;
            margin-bottom: 0.75rem;
            padding-left: 1rem;
            display: flex;
            align-items: center;
        }

        .nav-section-title i {
            margin-right: 0.5rem;
            width: 20px;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            transition: all 0.3s;
        }

        .main-content-expanded {
            margin-left: 80px;
        }

        /* Top Bar */
        .topbar {
            background-color: #fff;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--secondary-color);
            cursor: pointer;
        }

        .search-bar {
            position: relative;
            width: 300px;
        }

        .search-bar i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
        }

        .search-bar input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            font-size: 0.875rem;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .topbar-icon-btn {
            background: none;
            border: none;
            padding: 0.5rem;
            border-radius: 0.35rem;
            color: var(--secondary-color);
            cursor: pointer;
            position: relative;
            transition: all 0.3s;
        }

        .topbar-icon-btn:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background-color: var(--danger-color);
            color: white;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem;
            border-radius: 0.35rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .user-info:hover {
            background-color: var(--light-color);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .user-name {
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
            font-size: 0.875rem;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            position: absolute;
            bottom: 1rem;
            left: 1rem;
            right: 1rem;
        }

        .storage-info {
            margin-bottom: 1rem;
        }

        .storage-progress {
            height: 8px;
            border-radius: 4px;
            background-color: #e9ecef;
            margin-bottom: 0.5rem;
        }

        .storage-text {
            font-size: 0.75rem;
            color: var(--secondary-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .search-bar {
                width: 200px;
            }
        }

        /* Additional Styles */
        .content-wrapper {
            padding: 0 1.5rem 2rem;
        }

        .page-heading {
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
        }

        .page-subtitle {
            color: var(--secondary-color);
            margin: 0.25rem 0 0;
        }

        /* Dropdown Styles */
        .dropdown-menu {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border-radius: 0.35rem;
            padding: 0.5rem;
        }

        .dropdown-item {
            border-radius: 0.25rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            transition: all 0.3s;
        }

        .dropdown-item:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }

        .dropdown-divider {
            margin: 0.5rem 0;
        }
    </style>

    @stack('styles')
</head>
<body>
    <div>
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <a href="{{ route('dashboard') }}" class="sidebar-brand">
                <i class="fas fa-graduation-cap sidebar-brand-icon"></i>
                <span class="sidebar-brand-text">MoodlePro</span>
            </a>

            <nav class="nav flex-column">
                <!-- Dashboard Section -->
                <div class="nav-section-title">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Principal</span>
                </div>
                
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Projects Section -->
                <div class="nav-section-title">
                    <i class="fas fa-folder"></i>
                    <span>Proyectos</span>
                </div>
                
                <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                    <i class="fas fa-project-diagram"></i>
                    <span>Mis Proyectos</span>
                </a>
                
                <a href="{{ route('projects.create') }}" class="nav-link">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nuevo Proyecto</span>
                </a>

                <!-- Tasks Section -->
                <div class="nav-section-title">
                    <i class="fas fa-tasks"></i>
                    <span>Tareas</span>
                </div>
                
                <a href="{{ route('tasks.index') }}" class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                    <i class="fas fa-list-check"></i>
                    <span>Mis Tareas</span>
                    @if(isset($pendingTasksCount) && $pendingTasksCount > 0)
                        <span class="badge bg-warning ms-auto">{{ $pendingTasksCount }}</span>
                    @endif
                </a>

                <!-- Resources Section -->
                <div class="nav-section-title">
                    <i class="fas fa-book"></i>
                    <span>Recursos</span>
                </div>
                
                <a href="{{ route('resources.index') }}" class="nav-link {{ request()->routeIs('resources.*') ? 'active' : '' }}">
                    <i class="fas fa-file-alt"></i>
                    <span>Biblioteca</span>
                </a>

                <!-- Communication Section -->
                <div class="nav-section-title">
                    <i class="fas fa-comments"></i>
                    <span>Comunicación</span>
                </div>
                
                <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <i class="fas fa-bell"></i>
                    <span>Notificaciones</span>
                    @if(isset($unreadNotifications) && $unreadNotifications > 0)
                        <span class="badge bg-danger ms-auto">{{ $unreadNotifications }}</span>
                    @endif
                </a>
                
                <!-- Profile Section -->
                <div class="nav-section-title">
                    <i class="fas fa-user"></i>
                    <span>Personal</span>
                </div>
                
                <a href="{{ route('profile.show') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="fas fa-user-circle"></i>
                    <span>Mi Perfil</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Top Bar -->
            <div class="topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    </div>

                <div class="topbar-right">
                    <!-- Notifications -->
                    <div class="dropdown">
                        <button class="topbar-icon-btn" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            @if(isset($unreadNotifications) && $unreadNotifications > 0)
                                <span class="notification-badge">{{ $unreadNotifications }}</span>
                            @endif
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Notificaciones</h6></li>
                            <li><a class="dropdown-item" href="{{ route('notifications.index') }}">
                                <i class="fas fa-tasks text-primary me-2"></i>
                                Nueva tarea asignada
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('notifications.index') }}">Ver todas las notificaciones</a></li>
                        </ul>
                    </div>

                    <!-- Messages -->
                    <div class="dropdown">
                        <button class="topbar-icon-btn" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-envelope"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Mensajes</h6></li>
                            <li><a class="dropdown-item" href="#">No hay mensajes nuevos</a></li>
                        </ul>
                    </div>

                    <!-- User Menu -->
                    <div class="dropdown">
                        <div class="user-info" data-bs-toggle="dropdown">
                            <div class="user-avatar">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="user-name">{{ auth()->user()->name ?? 'Usuario' }}</p>
                            </div>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                                <i class="fas fa-user me-2"></i>Mi Perfil
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-cog me-2"></i>Configuración
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <div class="content-wrapper">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (opcional, para AJAX) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom Scripts -->
    <script>
        // Toggle Sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('sidebar-collapsed');
            document.getElementById('mainContent').classList.toggle('main-content-expanded');
        });

        // Mobile Sidebar Toggle
        if (window.innerWidth <= 768) {
            document.getElementById('sidebarToggle').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('show');
            });
        }

        // Active link highlighting
        document.querySelectorAll('.nav-link').forEach(link => {
            if (link.href === window.location.href) {
                link.classList.add('active');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>