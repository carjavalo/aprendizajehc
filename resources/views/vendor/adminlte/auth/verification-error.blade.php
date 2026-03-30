@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('auth_header', 'Error de Verificación')

@section('auth_body')

    <div class="text-center mb-4">
        <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
        <h4 class="text-danger">Problema con la Verificación</h4>
    </div>

    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <strong>¡Ups! Algo salió mal</strong>
        <br>
        <small>El enlace de verificación puede haber expirado o ser inválido.</small>
    </div>

    <div class="text-center mb-4">
        <p class="text-muted">
            No te preocupes, puedes solicitar un nuevo enlace de verificación o contactar con soporte.
        </p>
    </div>

    <div class="row">
        <div class="col-12 text-center">
            <div class="btn-group-vertical w-100" role="group">
                
                <!-- Solicitar nuevo enlace -->
                <form method="POST" action="{{ route('verification.send') }}" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i>
                        Solicitar Nuevo Enlace
                    </button>
                </form>

                <!-- Ir al login -->
                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-block mb-2">
                    <i class="fas fa-sign-in-alt"></i>
                    Ir al Login
                </a>

                <!-- Ir al registro -->
                <a href="{{ route('register') }}" class="btn btn-outline-info btn-block mb-2">
                    <i class="fas fa-user-plus"></i>
                    Crear Nueva Cuenta
                </a>

                <!-- Cerrar sesión -->
                <form method="POST" action="{{ route('logout') }}" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-block">
                        <i class="fas fa-sign-out-alt"></i>
                        Cerrar Sesión
                    </button>
                </form>

            </div>
        </div>
    </div>

    <hr>

    <div class="text-center">
        <small class="text-muted">
            <i class="fas fa-question-circle"></i>
            ¿Necesitas ayuda? Contacta con el administrador del sistema.
        </small>
    </div>

@stop

@section('adminlte_css')
    <style>
        .verification-error-container {
            max-width: 450px;
            margin: 0 auto;
        }
        
        .btn-group-vertical .btn {
            margin-bottom: 0.5rem;
        }
        
        .alert {
            border-radius: 8px;
        }
        
        .card {
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .login-card-body {
            padding: 2rem;
        }
        
        .fa-exclamation-triangle {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
@stop
