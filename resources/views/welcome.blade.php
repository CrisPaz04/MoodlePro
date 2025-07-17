<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MoodlePro - Sistema de Gestión Académica Moderno</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --dark-color: #2e3440;
            --light-bg: #f8f9fc;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }
        
        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.1;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        nav {
            background: transparent !important;
            padding: 1.5rem 0;
            position: absolute;
            width: 100%;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }
        
        .btn-login {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .hero-content {
            display: flex;
            align-items: center;
            min-height: 100vh;
            position: relative;
            z-index: 2;
        }
        
        .hero-text h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.8s ease;
        }
        
        .hero-text p {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            animation: fadeInUp 0.8s ease 0.2s;
            animation-fill-mode: both;
        }
        
        .hero-buttons {
            animation: fadeInUp 0.8s ease 0.4s;
            animation-fill-mode: both;
        }
        
        .btn-get-started {
            background: white;
            color: var(--primary-color);
            padding: 1rem 2.5rem;
            border-radius: 3rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            margin-right: 1rem;
        }
        
        .btn-get-started:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            color: var(--primary-color);
        }
        
        .btn-learn-more {
            background: transparent;
            color: white;
            padding: 1rem 2.5rem;
            border: 2px solid white;
            border-radius: 3rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-learn-more:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
            color: white;
        }
        
        .hero-image {
            animation: float 6s ease-in-out infinite;
        }
        
        .hero-image img {
            max-width: 100%;
            height: auto;
        }
        
        /* Features Section */
        .features-section {
            padding: 5rem 0;
            background: var(--light-bg);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1rem;
        }
        
        .section-title p {
            font-size: 1.1rem;
            color: #6c757d;
        }
        
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            height: 100%;
            transition: all 0.3s;
            border: 1px solid transparent;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        .feature-icon i {
            font-size: 2rem;
            color: white;
        }
        
        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 1rem;
        }
        
        .feature-card p {
            color: #6c757d;
            line-height: 1.7;
        }
        
        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 4rem 0;
            color: white;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 5rem 0;
            background: var(--dark-color);
            color: white;
            text-align: center;
        }
        
        .cta-section h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        /* Footer */
        footer {
            background: #1a1a1a;
            color: white;
            padding: 3rem 0 2rem;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            margin: 0 1rem;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
            100% {
                transform: translateY(0px);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-text h1 {
                font-size: 2.5rem;
            }
            
            .hero-buttons {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            
            .btn-get-started,
            .btn-learn-more {
                width: 100%;
                text-align: center;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-graduation-cap me-2"></i>MoodlePro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Características</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Acerca de</a>
                    </li>
                    @if (Route::has('login'))
                        @auth
                            <li class="nav-item">
                                <a href="{{ url('/dashboard') }}" class="btn btn-login ms-3">Dashboard</a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a href="{{ route('login') }}" class="btn btn-login ms-3">Iniciar Sesión</a>
                            </li>
                        @endauth
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-pattern"></div>
        <div class="container">
            <div class="hero-content">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="hero-text">
                            <h1>La Nueva Era de la Gestión Académica</h1>
                            <p>MoodlePro revoluciona la forma en que estudiantes y profesores colaboran. Con herramientas modernas y una interfaz intuitiva.</p>
                            <div class="hero-buttons">
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="btn-get-started">Ir al Dashboard</a>
                                @else
                                    <a href="{{ route('register') }}" class="btn-get-started">Comenzar Gratis</a>
                                    <a href="#features" class="btn-learn-more">Conocer Más</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-image">
                            <svg viewBox="0 0 500 400" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color:#fff;stop-opacity:0.8" />
                                        <stop offset="100%" style="stop-color:#fff;stop-opacity:0.3" />
                                    </linearGradient>
                                </defs>
                                <rect x="50" y="50" width="400" height="250" rx="20" fill="url(#gradient)" opacity="0.1"/>
                                <rect x="80" y="80" width="340" height="190" rx="15" fill="none" stroke="#fff" stroke-width="2" opacity="0.3"/>
                                <circle cx="120" cy="120" r="15" fill="#fff" opacity="0.8"/>
                                <line x1="150" y1="120" x2="380" y2="120" stroke="#fff" stroke-width="2" opacity="0.5"/>
                                <circle cx="120" cy="160" r="15" fill="#fff" opacity="0.8"/>
                                <line x1="150" y1="160" x2="320" y2="160" stroke="#fff" stroke-width="2" opacity="0.5"/>
                                <circle cx="120" cy="200" r="15" fill="#fff" opacity="0.8"/>
                                <line x1="150" y1="200" x2="360" y2="200" stroke="#fff" stroke-width="2" opacity="0.5"/>
                                <rect x="300" y="220" width="80" height="30" rx="15" fill="#fff" opacity="0.9"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Todo lo que Necesitas en un Solo Lugar</h2>
                <p>Herramientas diseñadas para mejorar la experiencia educativa</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h3>Gestión de Proyectos</h3>
                        <p>Organiza tus proyectos académicos con tableros Kanban intuitivos y gestión de tareas en tiempo real.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3>Chat Integrado</h3>
                        <p>Comunícate con tu equipo directamente desde la plataforma con mensajería instantánea por proyecto.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Dashboard Analítico</h3>
                        <p>Visualiza tu progreso con gráficos interactivos y métricas en tiempo real de tu rendimiento académico.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h3>Notificaciones Inteligentes</h3>
                        <p>Mantente al día con notificaciones personalizadas sobre tareas, deadlines y actividad del equipo.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h3>Biblioteca de Recursos</h3>
                        <p>Comparte y accede a materiales de estudio con un sistema de archivos organizado y calificaciones.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3>Diseño Responsive</h3>
                        <p>Accede desde cualquier dispositivo con una interfaz optimizada para móviles, tablets y escritorio.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6 mb-4 mb-md-0">
                    <div class="stat-item">
                        <div class="stat-number">40+</div>
                        <div class="stat-label">Características</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4 mb-md-0">
                    <div class="stat-item">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Moderno</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Disponible</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number">∞</div>
                        <div class="stat-label">Posibilidades</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>¿Listo para Transformar tu Experiencia Académica?</h2>
            <p>Únete a la nueva generación de estudiantes y profesores que usan MoodlePro</p>
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-get-started">Acceder al Sistema</a>
            @else
                <a href="{{ route('register') }}" class="btn-get-started">Crear Cuenta Gratuita</a>
            @endauth
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="text-center">
                <p class="mb-2">&copy; 2024 MoodlePro. Proyecto Final de Clase.</p>
                <div class="footer-links">
                    <a href="#">Términos</a>
                    <a href="#">Privacidad</a>
                    <a href="#">Contacto</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>