@extends('adminlte::auth.auth-page', ['authType' => 'register'])

@php
    $loginUrl = View::getSection('login_url') ?? config('adminlte.login_url', 'login');
    $registerUrl = View::getSection('register_url') ?? config('adminlte.register_url', 'register');

    if (config('adminlte.use_route_url', false)) {
        $loginUrl = $loginUrl ? route($loginUrl) : '';
        $registerUrl = $registerUrl ? route($registerUrl) : '';
    } else {
        $loginUrl = $loginUrl ? url($loginUrl) : '';
        $registerUrl = $registerUrl ? url($registerUrl) : '';
    }
@endphp

@section('auth_header', __('adminlte::adminlte.register_message'))

@section('auth_body')
    <form action="{{ $registerUrl }}" method="post">
        @csrf

        {{-- Name field --}}
        <div class="input-group mb-3">
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name') }}" placeholder="Nombre" autofocus>

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-user {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Apellido1 field --}}
        <div class="input-group mb-3">
            <input type="text" name="apellido1" class="form-control @error('apellido1') is-invalid @enderror"
                value="{{ old('apellido1') }}" placeholder="Primer Apellido">

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-user {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('apellido1')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Apellido2 field --}}
        <div class="input-group mb-3">
            <input type="text" name="apellido2" class="form-control @error('apellido2') is-invalid @enderror"
                value="{{ old('apellido2') }}" placeholder="Segundo Apellido (Opcional)">

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-user {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('apellido2')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Tipo de Documento field --}}
        <div class="input-group mb-3">
            <select name="tipo_documento" class="form-control @error('tipo_documento') is-invalid @enderror">
                <option value="">Seleccione tipo de documento</option>
                @foreach($availableDocumentTypes as $type)
                    <option value="{{ $type }}" {{ old('tipo_documento') == $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                @endforeach
            </select>

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-id-card {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('tipo_documento')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Número de Documento field --}}
        <div class="input-group mb-3">
            <input type="text" name="numero_documento" class="form-control @error('numero_documento') is-invalid @enderror"
                value="{{ old('numero_documento') }}" placeholder="Número de Documento" maxlength="20">

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-id-badge {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('numero_documento')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Email field --}}
        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email') }}" placeholder="Correo Electrónico">

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Password field --}}
        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                placeholder="{{ __('adminlte::adminlte.password') }}">

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Confirm password field --}}
        <div class="input-group mb-3">
            <input type="password" name="password_confirmation"
                class="form-control @error('password_confirmation') is-invalid @enderror"
                placeholder="{{ __('adminlte::adminlte.retype_password') }}">

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('password_confirmation')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Servicio/Área field --}}
        <div class="input-group mb-3">
            <select name="servicio_area_id" class="form-control @error('servicio_area_id') is-invalid @enderror">
                <option value="">Seleccione Servicio/Área</option>
                @foreach($serviciosAreas as $servicio)
                    <option value="{{ $servicio->id }}" {{ old('servicio_area_id') == $servicio->id ? 'selected' : '' }}>
                        {{ $servicio->nombre }}
                    </option>
                @endforeach
            </select>

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-building {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('servicio_area_id')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Vinculación/Contrato field --}}
        <div class="input-group mb-3">
            <select name="vinculacion_contrato_id" class="form-control @error('vinculacion_contrato_id') is-invalid @enderror">
                <option value="">Seleccione Tipo de Vinculación</option>
                @foreach($vinculacionesContrato as $vinculacion)
                    <option value="{{ $vinculacion->id }}" {{ old('vinculacion_contrato_id') == $vinculacion->id ? 'selected' : '' }}>
                        {{ $vinculacion->nombre }}
                    </option>
                @endforeach
            </select>

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-file-contract {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('vinculacion_contrato_id')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Sede field --}}
        <div class="input-group mb-3">
            <select name="sede_id" class="form-control @error('sede_id') is-invalid @enderror">
                <option value="">Seleccione Sede</option>
                @foreach($sedes as $sede)
                    <option value="{{ $sede->id }}" {{ old('sede_id') == $sede->id ? 'selected' : '' }}>
                        {{ $sede->nombre }}
                    </option>
                @endforeach
            </select>

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-hospital {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('sede_id')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Register button --}}
        <button type="submit" class="btn btn-block {{ config('adminlte.classes_auth_btn', 'btn-flat btn-primary') }}">
            <span class="fas fa-user-plus"></span>
            {{ __('adminlte::adminlte.register') }}
        </button>
    </form>
@stop

@section('auth_footer')
    <p class="my-0">
        <a href="{{ $loginUrl }}">
            {{ __('adminlte::adminlte.i_already_have_a_membership') }}
        </a>
    </p>
@stop
