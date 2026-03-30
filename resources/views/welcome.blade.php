<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Hospital Universitario del Valle') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ====== DESIGN TOKENS ====== */
        :root {
            --space-xs: clamp(0.25rem, 0.5vw, 0.5rem);
            --space-sm: clamp(0.5rem, 1vw, 0.75rem);
            --space-md: clamp(0.75rem, 1.5vw, 1.25rem);
            --space-lg: clamp(1rem, 2vw, 1.5rem);
            --space-xl: clamp(1.25rem, 3vw, 2rem);
            --radius-sm: 8px;
            --radius-md: 12px;
            --color-primary: #2c4370;
            --color-primary-dark: #1e2f4d;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background: url('{{ asset('images/huv.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            height: 100dvh;
            margin: 0;
            color: #fff;
        }

        .overlay {
            background-color: rgba(0, 0, 0, 0.6);
            min-height: 100vh;
            min-height: 100dvh;
            width: 100%;
            position: fixed;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        /* ====== BANNER SUPERIOR ====== */
        .welcome-banner {
            width: 100%;
            padding: clamp(10px, 2vw, 18px) clamp(15px, 3vw, 30px);
            text-align: center;
            border-radius: 0 0 var(--radius-sm) var(--radius-sm);
            position: relative;
            z-index: 10;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: background-color 0.6s ease;
            flex-shrink: 0;
        }
        .welcome-banner h2 {
            margin: 0;
            font-weight: 700;
            font-size: clamp(1rem, 2.5vw, 1.5rem);
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
            animation: fadeInDown 1s ease;
        }
        .welcome-banner p {
            margin: 5px 0 0;
            font-size: clamp(0.8rem, 1.8vw, 1rem);
            opacity: 0.9;
            animation: fadeInDown 1.2s ease;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ====== CONTENEDOR PRINCIPAL (Grid · mobile-first) ====== */
        .main-content {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr;
            align-items: start;
            justify-items: center;
            gap: var(--space-lg);
            padding: var(--space-md);
            min-height: 0;
        }

        /* ====== AREA MEDIA (VIDEO/IMAGEN) ====== */
        .media-area {
            width: 100%;
            aspect-ratio: 16 / 9;
            background-color: rgba(0, 0, 0, 0.4);
            border-radius: var(--radius-md);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            border: 2px solid rgba(255,255,255,0.08);
            box-shadow: 0 8px 30px rgba(0,0,0,0.4);
        }
        .media-area iframe,
        .media-area video {
            width: 100%;
            height: 100%;
            border: none;
            border-radius: var(--radius-md);
            object-fit: cover;
        }
        .media-area img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: var(--radius-md);
        }
        .media-placeholder {
            color: rgba(255,255,255,0.3);
            font-size: clamp(1rem, 2vw, 1.5rem);
            text-align: center;
        }
        .media-placeholder i {
            font-size: clamp(2.5rem, 5vw, 4rem);
            display: block;
            margin-bottom: var(--space-sm);
        }

        /* ====== CAROUSEL AUTO-ROTACIÓN ====== */
        .carousel-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }
        .carousel-slide.active {
            opacity: 1;
            z-index: 2;
            pointer-events: auto;
        }
        .carousel-indicators-custom {
            position: absolute;
            bottom: var(--space-sm);
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: clamp(5px, 1vw, 8px);
            z-index: 5;
        }
        .carousel-dot {
            width: clamp(8px, 1.2vw, 12px);
            height: clamp(8px, 1.2vw, 12px);
            border-radius: 50%;
            background: rgba(255,255,255,0.4);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid rgba(255,255,255,0.6);
            padding: 0;
        }
        .carousel-dot.active {
            background: rgba(255,255,255,0.95);
            transform: scale(1.2);
        }
        .carousel-dot:hover {
            background: rgba(255,255,255,0.7);
        }

        /* ====== AUTH CARD ====== */
        .auth-card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: var(--radius-md);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            width: 100%;
            max-width: 520px;
        }

        .card-left {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            color: white;
            padding: var(--space-lg);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card-right {
            padding: var(--space-md) var(--space-lg);
            color: #333;
        }

        .logo {
            max-width: 120px;
            margin-bottom: var(--space-lg);
        }

        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
            display: flex;
            flex-wrap: nowrap;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: var(--space-xs) var(--space-md);
            font-size: clamp(0.85rem, 1.5vw, 0.95rem);
            white-space: nowrap;
        }

        .nav-tabs .nav-link.active {
            color: var(--color-primary);
            background-color: transparent;
            border-bottom: 3px solid var(--color-primary);
        }

        .tab-content {
            padding-top: var(--space-sm);
        }

        .form-control {
            border-radius: 5px;
            padding: 6px 10px;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-label {
            font-size: 0.9rem;
            margin-bottom: 3px;
        }

        .btn-primary {
            border-radius: 5px;
            padding: 8px 12px;
            font-weight: 500;
            font-size: 0.95rem;
            background-color: var(--color-primary) !important;
            border-color: var(--color-primary) !important;
        }

        .btn-primary:hover {
            background-color: var(--color-primary-dark) !important;
            border-color: var(--color-primary-dark) !important;
        }

        .form-control:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 0.2rem rgba(44, 67, 112, 0.25);
        }

        a { color: var(--color-primary); }
        a:hover { color: var(--color-primary-dark); }

        .hospital-info h1 {
            font-size: clamp(1.1rem, 2vw, 1.4rem);
            font-weight: 700;
            margin-bottom: var(--space-sm);
        }

        .hospital-info p {
            font-size: 0.9rem;
            margin-bottom: var(--space-sm);
        }

        .features {
            margin-top: var(--space-sm);
        }

        .feature-item {
            margin-bottom: var(--space-xs);
            font-size: 0.85rem;
        }

        .feature-item i {
            margin-right: 8px;
            color: #fff;
        }

        .card-right h3 {
            font-size: clamp(1.05rem, 1.8vw, 1.2rem);
            margin-bottom: var(--space-sm);
        }

        .mb-3 { margin-bottom: 8px !important; }
        .form-check { margin-bottom: 8px !important; }
        .row { margin-bottom: 0 !important; }
        .row .col-md-6 .mb-3 { margin-bottom: 8px !important; }

        /* Formulario de registro compacto */
        #register .form-control { padding: 5px 10px; margin-bottom: 6px; }
        #register .form-label { margin-bottom: 2px; }
        #register .mb-3 { margin-bottom: 6px !important; }
        #register h3 { margin-bottom: 8px !important; }
        #register .row { margin-bottom: 0 !important; }
        #register .d-grid { margin-top: 8px; }
        #register .row.g-2 { margin-left: -4px; margin-right: -4px; }
        #register .row.g-2 .col-6 { padding-left: 4px; padding-right: 4px; }

        /* ====================================================
           RESPONSIVE — mobile-first base ➜ progressive
           Principle 2: rearrange with purpose at each tier
           ==================================================== */

        /* ---- ≥ 768px  Tablet landscape ---- */
        @media (min-width: 768px) {
            .main-content {
                align-items: center;
                padding: var(--space-lg);
            }
            .media-area {
                max-height: 50vh;
            }
        }

        /* ---- ≥ 993px  Laptop — two-column side-by-side ---- */
        @media (min-width: 993px) {
            .main-content {
                grid-template-columns: 1fr clamp(380px, 35vw, 480px);
                align-items: stretch;
                padding: var(--space-lg);
            }
            .media-area {
                aspect-ratio: auto;
                max-height: none;
                align-self: stretch;
            }
            .auth-card {
                max-width: none;
                align-self: center;
            }
        }

        /* ---- ≥ 1200px Desktop — spacious ---- */
        @media (min-width: 1200px) {
            .main-content {
                grid-template-columns: 1fr 480px;
                gap: var(--space-xl);
                padding: var(--space-xl);
            }
        }

        /* ---- ≤ 767px  Phone / small tablet — stack + reflow ---- */
        @media (max-width: 767px) {
            .auth-card {
                max-width: 100%;
                border-radius: var(--radius-sm);
            }
            .card-left {
                padding: var(--space-md);
            }
            .hospital-info h1 {
                font-size: 1.1rem;
            }
            .features {
                display: flex;
                flex-wrap: wrap;
                gap: var(--space-xs) var(--space-md);
            }
            .feature-item {
                margin-bottom: 0;
            }
        }

        /* ---- ≤ 575px  Phone portrait — auth form is king ---- */
        @media (max-width: 575px) {
            .main-content {
                gap: var(--space-sm);
                padding: var(--space-sm);
            }
            .media-area {
                aspect-ratio: 2 / 1;
                border-radius: var(--radius-sm);
            }
            .media-area iframe,
            .media-area video,
            .media-area img {
                border-radius: var(--radius-sm);
            }
            .card-left {
                padding: var(--space-sm) var(--space-md);
            }
            .hospital-info h1 {
                font-size: 1rem;
                margin-bottom: 4px;
            }
            .hospital-info p {
                margin-bottom: 4px;
                font-size: 0.85rem;
            }
            .features {
                margin-top: 4px;
            }
            .feature-item {
                font-size: 0.8rem;
            }
            .card-right {
                padding: var(--space-sm) var(--space-md);
            }
            .card-right h3 {
                font-size: 1.05rem;
            }
        }
    </style>
</head>
<body>
    <div class="overlay">
        {{-- ====== BANNER SUPERIOR DINÁMICO (CAROUSEL) ====== --}}
        @if(isset($banners) && $banners->count() > 0)
            @php $primerBanner = $banners->first(); @endphp
            <div class="welcome-banner" id="welcomeBannerStrip" style="background-color: {{ $primerBanner->banner_color_fondo }};">
                <h2 id="bannerTitulo" style="color: {{ $primerBanner->banner_color_texto }};">{{ $primerBanner->banner_titulo }}</h2>
                <p id="bannerSubtitulo" style="color: {{ $primerBanner->banner_color_texto }};{{ $primerBanner->banner_subtitulo ? '' : ' display:none;' }}">{{ $primerBanner->banner_subtitulo ?? '' }}</p>
            </div>
        @else
            <div class="welcome-banner" style="background-color: #2c4370;">
                <h2 style="color: #fff;">Plataforma de Gestión Educativa</h2>
                <p style="color: #fff;">Hospital Universitario del Valle</p>
            </div>
        @endif

        {{-- ====== CONTENIDO PRINCIPAL: MEDIA + AUTH CARD ====== --}}
        <div class="main-content">
            {{-- Área de Video / Imagen --}}
            <div class="media-area" id="mediaCarousel">
                @if(isset($banners) && $banners->count() > 0)
                    @foreach($banners as $idx => $b)
                        <div class="carousel-slide {{ $idx === 0 ? 'active' : '' }}"
                             data-index="{{ $idx }}"
                             data-banner="{{ json_encode([
                                 'banner_titulo' => $b->banner_titulo,
                                 'banner_subtitulo' => $b->banner_subtitulo,
                                 'banner_color_fondo' => $b->banner_color_fondo,
                                 'banner_color_texto' => $b->banner_color_texto,
                                 'media_tipo' => $b->media_tipo,
                                 'es_youtube' => $b->esYoutube(),
                             ]) }}">
                            @if($b->media_tipo === 'video')
                                @if($b->esYoutube())
                                    <iframe src="{{ $b->getYoutubeEmbedUrl() }}?autoplay={{ $idx === 0 ? '1' : '0' }}&mute=1&rel=0&enablejsapi=1"
                                            allow="autoplay; encrypted-media" allowfullscreen
                                            id="ytframe_{{ $idx }}"></iframe>
                                @elseif($b->media_archivo)
                                    <video {{ $idx === 0 ? 'autoplay' : '' }} muted playsinline controls>
                                        <source src="/media/{{ $b->media_archivo }}" type="video/mp4">
                                    </video>
                                @else
                                    <div class="media-placeholder">
                                        <i class="fas fa-video"></i>
                                        VIDEO ILUSTRATIVO
                                    </div>
                                @endif
                            @else
                                @if($b->media_archivo)
                                    <img src="/media/{{ $b->media_archivo }}" alt="{{ $b->media_titulo ?? 'Imagen ilustrativa' }}">
                                @else
                                    <div class="media-placeholder">
                                        <i class="fas fa-image"></i>
                                        IMAGEN ILUSTRATIVA
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach

                    {{-- Indicadores del carousel --}}
                    @if($banners->count() > 1)
                        <div class="carousel-indicators-custom">
                            @foreach($banners as $idx => $b)
                                <button class="carousel-dot {{ $idx === 0 ? 'active' : '' }}"
                                        data-slide="{{ $idx }}" title="{{ $b->banner_titulo }}"></button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="media-placeholder">
                        <i class="fas fa-photo-video"></i>
                        VIDEO ILUSTRATIVO
                    </div>
                @endif
            </div>

            {{-- Card de Autenticación (NO TOCAR EL FORMULARIO) --}}
            <div class="auth-card">
                <div class="row g-0">
                    <!-- Lado izquierdo - Información del Hospital -->
                    <div class="col-md-5 card-left">
                        <div class="hospital-info">
                            <h1>Hospital Universitario del Valle</h1>
                            <p>Gestión Educativa</p>

                            <div class="features">
                                <div class="feature-item">
                                    <i class="fas fa-user-md"></i> Gestión de Cursos
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-procedures"></i> Gestion de Contenidos
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-clipboard-list"></i> Seguimientos del Proceso
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-pills"></i> Certificacion
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lado derecho - Formularios de Autenticación -->
                    <div class="col-md-7 card-right">
                        <ul class="nav nav-tabs" id="authTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab" aria-controls="login" aria-selected="true">Iniciar Sesión</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab" aria-controls="register" aria-selected="false">Registrarse</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="authTabsContent">
                            <!-- Formulario de Login -->
                            <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                                <h3 class="mb-3">Iniciar Sesión</h3>
                                
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Correo Electrónico</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Contraseña</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required autocomplete="current-password">
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">Recordarme</label>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                                    </div>
                                    
                                    @if (Route::has('password.request'))
                                        <div class="text-center mt-3">
                                            <a href="{{ route('password.request') }}" class="text-decoration-none">
                                                ¿Olvidaste tu contraseña?
                                            </a>
                                        </div>
                                    @endif
                                </form>
                            </div>
                            
                            <!-- Formulario de Registro -->
                            <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                                <h3 class="mb-3">Crear Cuenta</h3>
                                
                                <form method="POST" action="{{ route('register') }}">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nombre</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                        @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label for="apellido1" class="form-label">Primer Apellido</label>
                                                <input type="text" class="form-control @error('apellido1') is-invalid @enderror" id="apellido1" name="apellido1" value="{{ old('apellido1') }}" required autocomplete="apellido1">
                                                @error('apellido1')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label for="apellido2" class="form-label">Segundo Apellido</label>
                                                <input type="text" class="form-control @error('apellido2') is-invalid @enderror" id="apellido2" name="apellido2" value="{{ old('apellido2') }}" autocomplete="apellido2">
                                                @error('apellido2')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                                                <select class="form-control @error('tipo_documento') is-invalid @enderror" id="tipo_documento" name="tipo_documento" required>
                                                    <option value="">Seleccione tipo de documento</option>
                                                    <option value="DNI" {{ old('tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                                    <option value="Pasaporte" {{ old('tipo_documento') == 'Pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                                                    <option value="Carnet de Extranjería" {{ old('tipo_documento') == 'Carnet de Extranjería' ? 'selected' : '' }}>Carnet de Extranjería</option>
                                                    <option value="Cédula" {{ old('tipo_documento') == 'Cédula' ? 'selected' : '' }}>Cédula</option>
                                                </select>
                                                @error('tipo_documento')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label for="numero_documento" class="form-label">Número de Documento</label>
                                                <input type="text" class="form-control @error('numero_documento') is-invalid @enderror" id="numero_documento" name="numero_documento" value="{{ old('numero_documento') }}" required maxlength="20" autocomplete="numero_documento">
                                                @error('numero_documento')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Correo Electrónico</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="register_email" name="email" value="{{ old('email') }}" required autocomplete="email">
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Teléfono de Contacto</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Ej: 3001234567" maxlength="20" autocomplete="tel">
                                        @error('phone')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Contraseña</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="register_password" name="password" required autocomplete="new-password">
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                                    </div>

                                    <div class="mb-3">
                                        <label for="servicio_area_id" class="form-label">Servicio / Área</label>
                                        <select class="form-control @error('servicio_area_id') is-invalid @enderror" id="servicio_area_id" name="servicio_area_id" required>
                                            <option value="">Seleccione Servicio/Área</option>
                                            @foreach(\App\Models\ServicioArea::all() as $servicio)
                                                <option value="{{ $servicio->id }}" {{ old('servicio_area_id') == $servicio->id ? 'selected' : '' }}>{{ $servicio->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('servicio_area_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="vinculacion_contrato_id" class="form-label">Tipo de Vinculación/Contrato</label>
                                        <select class="form-control @error('vinculacion_contrato_id') is-invalid @enderror" id="vinculacion_contrato_id" name="vinculacion_contrato_id" required>
                                            <option value="">Seleccione Tipo de Vinculación</option>
                                            @foreach(\App\Models\VinculacionContrato::all() as $vinculacion)
                                                <option value="{{ $vinculacion->id }}" {{ old('vinculacion_contrato_id') == $vinculacion->id ? 'selected' : '' }}>{{ $vinculacion->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('vinculacion_contrato_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="sede_id" class="form-label">Sede</label>
                                        <select class="form-control @error('sede_id') is-invalid @enderror" id="sede_id" name="sede_id" required>
                                            <option value="">Seleccione Sede</option>
                                            @foreach(\App\Models\Sede::all() as $sede)
                                                <option value="{{ $sede->id }}" {{ old('sede_id') == $sede->id ? 'selected' : '' }}>{{ $sede->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('sede_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">Registrarse</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div> {{-- /auth-card --}}
        </div> {{-- /main-content --}}
    </div> {{-- /overlay --}}
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- ====== CAROUSEL AUTO-ROTACIÓN ====== --}}
    @if(isset($banners) && $banners->count() > 1)
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var slides = document.querySelectorAll('.carousel-slide');
        var dots = document.querySelectorAll('.carousel-dot');
        var bannerStrip = document.getElementById('welcomeBannerStrip');
        var bannerTitulo = document.getElementById('bannerTitulo');
        var bannerSubtitulo = document.getElementById('bannerSubtitulo');
        
        if (slides.length <= 1) return;
        
        var currentIndex = 0;
        var autoTimer = null;
        var IMAGE_DURATION = 8000;    // 8 seg para imágenes
        var YOUTUBE_DURATION = 20000; // 20 seg para YouTube
        
        function getSlideData(slide) {
            try { return JSON.parse(slide.getAttribute('data-banner')); }
            catch(e) { return {}; }
        }
        
        function goToSlide(newIndex) {
            if (newIndex === currentIndex) return;
            
            var oldSlide = slides[currentIndex];
            var newSlide = slides[newIndex];
            var data = getSlideData(newSlide);
            
            // Pausar video anterior
            var oldVideo = oldSlide.querySelector('video');
            if (oldVideo) { oldVideo.pause(); }
            
            // Transición de slides
            oldSlide.classList.remove('active');
            newSlide.classList.add('active');
            
            // Actualizar indicadores
            if (dots.length) {
                dots[currentIndex].classList.remove('active');
                dots[newIndex].classList.add('active');
            }
            
            // Actualizar franja superior con transición
            if (bannerStrip) {
                bannerStrip.style.backgroundColor = data.banner_color_fondo || '#2c4370';
            }
            if (bannerTitulo) {
                bannerTitulo.textContent = data.banner_titulo || '';
                bannerTitulo.style.color = data.banner_color_texto || '#fff';
            }
            if (bannerSubtitulo) {
                if (data.banner_subtitulo) {
                    bannerSubtitulo.textContent = data.banner_subtitulo;
                    bannerSubtitulo.style.display = '';
                } else {
                    bannerSubtitulo.textContent = '';
                    bannerSubtitulo.style.display = 'none';
                }
                bannerSubtitulo.style.color = data.banner_color_texto || '#fff';
            }
            
            currentIndex = newIndex;
            
            // Reproducir nuevo video si aplica
            var newVideo = newSlide.querySelector('video');
            if (newVideo) {
                newVideo.currentTime = 0;
                newVideo.play().catch(function(){});
            }
            
            // Programar siguiente avance
            scheduleNext();
        }
        
        function nextSlide() {
            var next = (currentIndex + 1) % slides.length;
            goToSlide(next);
        }
        
        function scheduleNext() {
            if (autoTimer) { clearTimeout(autoTimer); autoTimer = null; }
            
            var slide = slides[currentIndex];
            var data = getSlideData(slide);
            var video = slide.querySelector('video');
            
            if (video) {
                // Para videos locales: avanzar cuando termine
                video.onended = function() { nextSlide(); };
                // Fallback por si el video es muy largo (5 min max)
                autoTimer = setTimeout(nextSlide, 300000);
            } else if (data.es_youtube) {
                autoTimer = setTimeout(nextSlide, YOUTUBE_DURATION);
            } else {
                // Imagen
                autoTimer = setTimeout(nextSlide, IMAGE_DURATION);
            }
        }
        
        // Click en indicadores (puntos)
        dots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                var target = parseInt(this.getAttribute('data-slide'));
                if (target !== currentIndex) {
                    goToSlide(target);
                }
            });
        });
        
        // Iniciar: reproducir primer video si existe
        var firstVideo = slides[0].querySelector('video');
        if (firstVideo) {
            firstVideo.play().catch(function(){});
        }
        scheduleNext();
    });
    </script>
    @elseif(isset($banners) && $banners->count() === 1)
    <script>
    // Un solo banner: si es video local, loop infinito
    document.addEventListener('DOMContentLoaded', function() {
        var video = document.querySelector('.carousel-slide.active video');
        if (video) {
            video.loop = true;
            video.play().catch(function(){});
        }
    });
    </script>
    @endif
</body>
</html>
