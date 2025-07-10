@extends('layouts.auth')

@section('title', 'Registrarse - MoodlePro')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <i class="fas fa-graduation-cap auth-logo"></i>
        <h1 class="auth-title">MoodlePro</h1>
        <p class="auth-subtitle">Crea tu cuenta gratuita</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nombre Completo</label>
            <input id="name" 
                   type="text" 
                   class="form-control @error('name') is-invalid @enderror" 
                   name="name" 
                   value="{{ old('name') }}" 
                   required 
                   autocomplete="name" 
                   autofocus
                   placeholder="Juan Pérez">

            @error('name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input id="email" 
                   type="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required 
                   autocomplete="email"
                   placeholder="tu@correo.com">

            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input id="password" 
                   type="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   name="password" 
                   required 
                   autocomplete="new-password"
                   placeholder="Mínimo 8 caracteres">

            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password-confirm" class="form-label">Confirmar Contraseña</label>
            <input id="password-confirm" 
                   type="password" 
                   class="form-control" 
                   name="password_confirmation" 
                   required 
                   autocomplete="new-password"
                   placeholder="Repite tu contraseña">
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>
                Crear Cuenta
            </button>
        </div>

        <div class="auth-links">
            <div class="mt-3">
                ¿Ya tienes una cuenta? 
                <a href="{{ route('login') }}" class="fw-bold">
                    Inicia sesión aquí
                </a>
            </div>
        </div>
    </form>
</div>
@endsection