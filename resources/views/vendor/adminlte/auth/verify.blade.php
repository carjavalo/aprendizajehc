@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('auth_header', __('adminlte::adminlte.verify_message'))

@section('auth_body')

    @if(session('resent'))
        <div class="alert alert-success" role="alert">
            <i class="fas fa-check-circle"></i>
            {{ __('adminlte::adminlte.verify_email_sent') }}
        </div>
    @endif

    @if(session('status') == 'verification-link-sent')
        <div class="alert alert-success" role="alert">
            <i class="fas fa-check-circle"></i>
            Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Error:</strong> {{ session('error') }}
        </div>
    @endif

    @if(session('status') && session('status') != 'verification-link-sent')
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle"></i>
            {{ session('status') }}
        </div>
    @endif

    <div class="text-center mb-3">
        <i class="fas fa-envelope-open-text fa-3x text-primary mb-3"></i>
        <h5>Verificación de Correo Electrónico</h5>
    </div>

    <div class="alert alert-info" role="alert">
        <i class="fas fa-info-circle"></i>
        <strong>{{ __('adminlte::adminlte.verify_check_your_email') }}</strong>
    </div>

    <p class="text-muted text-center mb-4">
        {{ __('adminlte::adminlte.verify_if_not_recieved') }}, puedes solicitar un nuevo enlace:
    </p>

    <div class="text-center mb-4">
        <form class="d-inline" method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i>
                {{ __('adminlte::adminlte.verify_request_another') }}
            </button>
        </form>
    </div>

    <hr>

    <div class="text-center">
        <p class="text-muted mb-3">¿Necesitas ayuda o quieres regresar?</p>

        <div class="btn-group" role="group">
            <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
                Atrás
            </a>

            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </div>

@stop

@section('adminlte_css')
    <style>
        .verify-email-container {
            max-width: 500px;
            margin: 0 auto;
        }

        .verify-icon {
            color: #007bff;
            margin-bottom: 1rem;
        }

        .btn-group .btn {
            margin: 0 5px;
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

        @media (max-width: 576px) {
            .btn-group {
                flex-direction: column;
            }

            .btn-group .btn {
                margin: 5px 0;
                width: 100%;
            }
        }
    </style>
@stop
