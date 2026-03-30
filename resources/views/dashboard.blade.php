@extends('admin.layouts.master')

@section('title', 'Dashboard - Plataforma de Cursos')

@section('content_header')
    <h1>Plataforma de Aprendizaje</h1>
@stop

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
            {{ session('status') }}
        </div>
    @endif

    <!-- Marketplace Section -->
    <div class="marketplace-container">
        <!-- Hero Banner -->
        <section class="hero-banner mb-4">
            <div class="hero-content">
                <span class="hero-badge">
                    <i class="fas fa-star"></i> Nuevos Cursos Disponibles
                </span>
                <h1 class="hero-title">
                    {!! $configuracion['banner_titulo'] ?? 'Descubre tu próximo curso,<br/>impulsa tu carrera.' !!}
                </h1>
                <p class="hero-subtitle">
                    {{ $configuracion['banner_subtitulo'] ?? 'Únete a miles de profesionales de la salud. La forma más fácil de capacitarte y crecer profesionalmente.' }}
                </p>
                <div class="hero-buttons">
                    <a href="{{ route('academico.cursos.disponibles') }}" class="btn-hero-primary">
                        <i class="fas fa-graduation-cap"></i> Ver Cursos
                    </a>
                    <a href="#cursos-destacados" class="btn-hero-secondary">
                        <i class="fas fa-info-circle"></i> Más Información
                    </a>
                </div>
            </div>
        </section>

        @if($configuracion['mostrar_categorias'] ?? true)
        <!-- Categories Section -->
        <div class="categories-section mb-4">
            <div class="section-header">
                <h2 class="section-title">Explorar Categorías</h2>
                <a href="{{ route('academico.cursos.disponibles') }}" class="view-all-link">Ver Todos <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="categories-scroll">
                @foreach($categorias as $index => $categoria)
                    @if($categoria['activo'] ?? true)
                    <button class="category-btn {{ $index === 0 ? 'active' : '' }}" data-categoria="{{ $categoria['nombre'] }}">
                        <span class="material-symbols-outlined">{{ $categoria['icono'] }}</span>
                        <span>{{ $categoria['nombre'] }}</span>
                    </button>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        <!-- Products/Courses Grid -->
        <div class="products-section mb-4" id="cursos-destacados">
            <div class="section-header">
                <h2 class="section-title">Cursos Destacados</h2>
                <div class="section-actions">
                    <button class="action-btn" title="Filtrar">
                        <span class="material-symbols-outlined">filter_list</span>
                    </button>
                    <button class="action-btn" title="Ordenar">
                        <span class="material-symbols-outlined">sort</span>
                    </button>
                </div>
            </div>
            <div class="products-grid">
                @forelse($productos as $producto)
                    @if($producto['estado'] != 'inactivo')
                    <div class="product-card" data-categoria="{{ $producto['categoria'] }}">
                        <div class="product-image">
                            @if($producto['imagen'])
                                <img src="{{ url('media/' . $producto['imagen']) }}" alt="{{ $producto['titulo'] }}">
                            @else
                                <div class="product-placeholder">
                                    <span class="material-symbols-outlined">school</span>
                                </div>
                            @endif
                            <button class="favorite-btn" title="Agregar a favoritos">
                                <span class="material-symbols-outlined">favorite</span>
                            </button>
                            @if($producto['estado'] == 'destacado')
                                <span class="product-badge">Destacado</span>
                            @endif
                        </div>
                        <div class="product-info">
                            <span class="product-category">{{ $producto['categoria'] }}</span>
                            <h3 class="product-title">{{ $producto['titulo'] }}</h3>
                            @if($producto['descripcion'])
                                <p class="product-description">{{ Str::limit($producto['descripcion'], 80) }}</p>
                            @endif
                            <div class="product-footer">
                                <span class="product-price">
                                    @if($producto['precio'])
                                        ${{ number_format($producto['precio'], 2) }}
                                    @else
                                        <span class="free-badge">Gratis</span>
                                    @endif
                                </span>
                                <div class="product-rating">
                                    <span class="material-symbols-outlined star">star</span>
                                    <span>4.8</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @empty
                    <div class="col-12 text-center py-5">
                        <span class="material-symbols-outlined" style="font-size: 64px; color: #ccc;">school</span>
                        <h4 class="mt-3 text-muted">No hay cursos disponibles</h4>
                        <p class="text-muted">Pronto agregaremos nuevos cursos para ti.</p>
                    </div>
                @endforelse
            </div>
        </div>

        @if($configuracion['mostrar_seccion_vendedor'] ?? true)
        <!-- CTA Section -->
        <section class="cta-section">
            <div class="cta-content">
                <div class="cta-text">
                    <h2>¿Quieres inscribirte en un curso?</h2>
                    <p>Únete a miles de profesionales que ya están mejorando sus habilidades. Accede a cursos de calidad impartidos por expertos en el área de la salud.</p>
                    <div class="cta-buttons">
                        <a href="{{ route('academico.cursos.disponibles') }}" class="btn-cta-primary">
                            <i class="fas fa-user-plus"></i> Inscribirme Ahora
                        </a>
                        <a href="#" class="btn-cta-secondary">
                            <i class="fas fa-book"></i> Ver Catálogo
                        </a>
                    </div>
                    <div class="cta-stats">
                        <div class="stat-avatars">
                            <div class="avatar" style="background-color: #2c4370;"><i class="fas fa-user"></i></div>
                            <div class="avatar" style="background-color: #3d5a8a;"><i class="fas fa-user"></i></div>
                            <div class="avatar" style="background-color: #1e2f4d;"><i class="fas fa-user"></i></div>
                            <div class="avatar count">+500</div>
                        </div>
                        <p>Estudiantes activos este mes</p>
                    </div>
                </div>
                <div class="cta-card" style="padding: 0; overflow: visible;">
                    <!-- Widget de Chat WhatsApp/Interno -->
                    <div class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg flex flex-col overflow-hidden">
                        <div class="bg-primary p-4 text-white flex items-center justify-between" style="background-color: #2e3a75;">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined" style="font-size: 24px;">chat_bubble</span>
                                <div>
                                    <h2 class="text-sm font-semibold leading-none">Canal de Comunicación</h2>
                                    <p class="text-[10px] opacity-80 mt-1 uppercase tracking-wider font-medium">Chat Institucional</p>
                                </div>
                            </div>
                            <a href="{{ route('chat.bandeja') }}" class="hover:bg-white/10 p-1 rounded-full transition-colors" title="Ver todos los mensajes">
                                <span class="material-symbols-outlined">open_in_new</span>
                            </a>
                        </div>

                        <!-- Tabs para Enviar/Recibir -->
                        <div class="border-b border-slate-200 dark:border-slate-700">
                            <div class="flex">
                                <button class="chat-tab active flex-1 px-4 py-3 text-sm font-medium text-slate-700 dark:text-slate-200 border-b-2 border-primary transition-colors" data-tab="enviar" style="border-color: #2e3a75;">
                                    <span class="material-symbols-outlined text-sm" style="vertical-align: middle;">send</span>
                                    Enviar
                                </button>
                                <button class="chat-tab flex-1 px-4 py-3 text-sm font-medium text-slate-500 dark:text-slate-400 border-b-2 border-transparent hover:text-slate-700 transition-colors" data-tab="recibidos">
                                    <span class="material-symbols-outlined text-sm" style="vertical-align: middle;">inbox</span>
                                    Recibidos
                                    @php
                                        $noLeidos = \App\Models\MensajeChat::where('destinatario_id', auth()->id())->where('leido', false)->count();
                                    @endphp
                                    @if($noLeidos > 0)
                                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full ml-1">{{ $noLeidos }}</span>
                                    @endif
                                </button>
                            </div>
                        </div>
                        
                        <!-- Tab Content: Enviar Mensaje -->
                        <div id="tab-enviar" class="tab-content p-4 space-y-4">
                            <!-- Buscar Estudiante/Usuario -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-tight">Buscar Usuario</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                                    <input id="searchUser" class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 transition-all text-slate-700 dark:text-slate-200" placeholder="Nombre o email del usuario..." type="text"/>
                                </div>
                                <div id="userResults" class="hidden mt-1 border border-slate-100 dark:border-slate-700 rounded-lg overflow-hidden shadow-sm max-h-40 overflow-y-auto">
                                    <!-- Resultados de búsqueda se cargarán aquí -->
                                </div>
                            </div>

                            <!-- Mensaje -->
                            <div class="space-y-1.5">
                                <div class="flex justify-between items-end">
                                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-tight">Mensaje</label>
                                    <span id="charCount" class="text-[10px] font-medium text-slate-400">0 / 4000</span>
                                </div>
                                <textarea id="messageText" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg p-3 text-sm bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none" placeholder="Escribe tu mensaje institucional aquí..." rows="4" maxlength="4000" style="outline: none;"></textarea>
                            </div>

                            <!-- Destinatarios info -->
                            <div class="flex items-center gap-2 px-1">
                                <div class="flex -space-x-2">
                                    <div class="w-6 h-6 rounded-full border-2 border-white dark:border-slate-800 bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                        <span class="text-[8px] font-bold text-slate-500" id="recipientCount">0</span>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Destinatarios: <span class="font-bold text-slate-700 dark:text-slate-200" id="recipientText">Ninguno seleccionado</span>
                                </p>
                            </div>

                            <!-- Botón de envío -->
                            <div class="pt-2">
                                <button id="sendMessageBtn" class="w-full bg-primary hover:bg-[#252f5e] text-white py-3 rounded-lg font-semibold flex items-center justify-center gap-2 shadow-lg transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed" style="background-color: #2e3a75;" disabled>
                                    <span class="material-symbols-outlined">send</span>
                                    <span>Enviar Mensaje</span>
                                </button>
                            </div>
                        </div>

                        <!-- Tab Content: Mensajes Recibidos -->
                        <div id="tab-recibidos" class="tab-content hidden">
                            <div class="p-4">
                                <div id="mensajesRecibidosContainer" class="space-y-2 overflow-y-scroll" style="height: 300px; overflow-x: hidden;">
                                    <!-- Los mensajes se cargarán aquí dinámicamente -->
                                    <div class="text-center py-8">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-2" style="border-color: #2e3a75;"></div>
                                        <p class="text-sm text-slate-500">Cargando mensajes...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 pt-0 border-t border-slate-200 dark:border-slate-700">
                                <a href="{{ route('chat.bandeja') }}" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 py-2 rounded-lg font-medium flex items-center justify-center gap-2 transition-all text-sm">
                                    <span class="material-symbols-outlined text-sm">open_in_full</span>
                                    <span>Ver todos los mensajes</span>
                                </a>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-4 pb-4">
                            <p class="text-[10px] text-center text-slate-400 flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-xs">verified_user</span>
                                Uso institucional supervisado • v1.0.0
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif
    </div>
    <!-- Sistema Info Box (mantener funcionalidad existente) -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="info-box bg-light">
                <div class="info-box-content">
                    <span class="info-box-text">Estado del Sistema</span>
                    <span class="info-box-number text-success">
                        <i class="fas fa-check-circle"></i> Operativo
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón flotante para acceder a la bandeja de mensajes -->
    <a href="{{ route('chat.bandeja') }}" class="btn btn-primary btn-lg floating-chat-btn" title="Ver mis mensajes">
        <i class="fas fa-envelope"></i>
        @php
            $noLeidos = \App\Models\MensajeChat::where('destinatario_id', auth()->id())->where('leido', false)->count();
        @endphp
        @if($noLeidos > 0)
            <span class="badge badge-danger floating-badge">{{ $noLeidos }}</span>
        @endif
    </a>
@stop

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/admin_custom.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        /* Variables de colores institucionales */
        :root {
            --corp-primary: #2c4370;
            --corp-primary-dark: #1e2f4d;
            --corp-primary-light: #3d5a8a;
            --corp-secondary: #ffffff;
            --corp-bg-light: #f6f7f8;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }

        .marketplace-container {
            font-family: 'Manrope', sans-serif;
            transform: scale(0.85);
            transform-origin: top left;
            width: 117.65%; /* 100/0.85 para compensar el scale */
        }

        /* Hero Banner - Reducido 15% */
        .hero-banner {
            background: linear-gradient(135deg, rgba(44, 67, 112, 0.95), rgba(30, 47, 77, 0.85)), 
                        url('{{ isset($configuracion["banner_imagen"]) && $configuracion["banner_imagen"] ? (str_starts_with($configuracion["banner_imagen"], "http") ? $configuracion["banner_imagen"] : asset("storage/" . $configuracion["banner_imagen"])) : "https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=1200" }}');
            background-size: cover;
            background-position: center;
            border-radius: 14px;
            padding: 45px 30px;
            min-height: 320px;
            display: flex;
            align-items: center;
            box-shadow: 0 8px 30px rgba(44, 67, 112, 0.3);
        }

        .hero-content {
            max-width: 520px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 15px;
        }

        .hero-title {
            color: white;
            font-size: 32px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 15px;
            letter-spacing: -1px;
        }

        .hero-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 22px;
            max-width: 420px;
        }

        .hero-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-hero-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: white;
            color: var(--corp-primary);
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-hero-primary:hover {
            background: #f0f0f0;
            color: var(--corp-primary-dark);
            transform: translateY(-2px);
            text-decoration: none;
        }

        .btn-hero-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .btn-hero-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
        }

        /* Categories Section - Reducido 15% */
        .categories-section {
            background: white;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 800;
            color: #1a1a2e;
            margin: 0;
        }

        .view-all-link {
            color: var(--corp-primary);
            font-weight: 700;
            font-size: 12px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .view-all-link:hover {
            color: var(--corp-primary-dark);
            text-decoration: underline;
        }

        .categories-scroll {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 8px;
            scrollbar-width: none;
        }

        .categories-scroll::-webkit-scrollbar {
            display: none;
        }

        .category-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            background: white;
            font-weight: 600;
            font-size: 12px;
            color: #555;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .category-btn:hover {
            border-color: var(--corp-primary);
            background: #f8f9fa;
        }

        .category-btn.active {
            background: var(--corp-primary);
            color: white;
            border-color: var(--corp-primary);
            box-shadow: 0 4px 15px rgba(44, 67, 112, 0.3);
        }

        .category-btn .material-symbols-outlined {
            font-size: 18px;
        }

        /* Products Grid - Reducido 15% */
        .products-section {
            background: white;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .section-actions {
            display: flex;
            gap: 6px;
        }

        .action-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            border-color: var(--corp-primary);
            color: var(--corp-primary);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 18px;
        }

        .product-card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #e8e8e8;
            transition: all 0.3s ease;
        }

        .product-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            transform: translateY(-3px);
        }

        .product-image {
            position: relative;
            aspect-ratio: 1;
            overflow: hidden;
            background: #f5f5f5;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--corp-primary-light), var(--corp-primary));
        }

        .product-placeholder .material-symbols-outlined {
            font-size: 50px;
            color: rgba(255, 255, 255, 0.5);
        }

        .favorite-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .favorite-btn:hover {
            color: #e74c3c;
            transform: scale(1.1);
        }

        .favorite-btn .material-symbols-outlined {
            font-size: 16px;
        }

        .product-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #f39c12;
            color: white;
            padding: 3px 10px;
            border-radius: 16px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .product-info {
            padding: 16px;
        }

        .product-category {
            font-size: 12px;
            font-weight: 700;
            color: var(--corp-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .product-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a2e;
            margin: 8px 0;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-card:hover .product-title {
            color: var(--corp-primary);
        }

        .product-description {
            font-size: 13px;
            color: #666;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .product-price {
            font-size: 20px;
            font-weight: 800;
            color: #1a1a2e;
        }

        .free-badge {
            background: #27ae60;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            color: #666;
            font-weight: 500;
        }

        .product-rating .star {
            color: #f1c40f;
            font-size: 16px;
        }

        /* CTA Section - Reducido 15% */
        .cta-section {
            background: #f0f2f5;
            border-radius: 17px;
            padding: 40px;
            border: 1px solid #e0e0e0;
        }

        .cta-content {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .cta-text {
            flex: 1;
        }

        .cta-text h2 {
            font-size: 27px;
            font-weight: 800;
            color: #1a1a2e;
            margin-bottom: 14px;
        }

        .cta-text p {
            font-size: 14px;
            color: #666;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .cta-buttons {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .btn-cta-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--corp-primary);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(44, 67, 112, 0.3);
        }

        .btn-cta-primary:hover {
            background: var(--corp-primary-dark);
            color: white;
            transform: translateY(-2px);
            text-decoration: none;
        }

        .btn-cta-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: white;
            color: #333;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .btn-cta-secondary:hover {
            background: #f8f9fa;
            color: #333;
            text-decoration: none;
        }

        .cta-stats {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .stat-avatars {
            display: flex;
        }

        .stat-avatars .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 2px solid white;
            margin-left: -8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }

        .stat-avatars .avatar:first-child {
            margin-left: 0;
        }

        .stat-avatars .avatar.count {
            background: var(--corp-primary);
            font-size: 9px;
            font-weight: 700;
        }

        .cta-stats p {
            font-size: 12px;
            color: #666;
            font-weight: 500;
            margin: 0;
        }

        .cta-card {
            flex: 0 0 320px;
            background: white;
            border-radius: 17px;
            padding: 20px;
            box-shadow: 0 8px 34px rgba(0, 0, 0, 0.1);
            border: 1px solid #e8e8e8;
        }

        .upload-area {
            aspect-ratio: 16/9;
            background: #f8f9fa;
            border-radius: 10px;
            border: 2px dashed #d0d0d0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-bottom: 14px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-area:hover {
            border-color: var(--corp-primary);
        }

        .upload-area .material-symbols-outlined {
            font-size: 40px;
            color: #aaa;
        }

        .upload-area:hover .material-symbols-outlined {
            color: var(--corp-primary);
        }

        .upload-area p {
            font-size: 12px;
            font-weight: 600;
            color: #666;
            margin: 0;
        }

        .card-skeleton {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .skeleton-line {
            height: 34px;
            background: #f0f0f0;
            border-radius: 7px;
        }

        .skeleton-line.full {
            width: 100%;
        }

        .skeleton-line.medium {
            width: 66%;
        }

        .progress-bar-container {
            height: 40px;
            background: rgba(44, 67, 112, 0.05);
            border-radius: 7px;
            padding: 0 14px;
            display: flex;
            align-items: center;
        }

        .progress-bar-container .progress-fill {
            height: 7px;
            width: 100%;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar-container .progress-fill::after {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 66%;
            background: var(--corp-primary);
            border-radius: 4px;
        }

        /* Responsive - Reducido 15% */
        @media (max-width: 992px) {
            .hero-banner {
                padding: 34px 20px;
                min-height: 280px;
            }

            .hero-title {
                font-size: 27px;
            }

            .cta-content {
                flex-direction: column;
            }

            .cta-card {
                flex: none;
                width: 100%;
                max-width: 340px;
            }
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 24px;
            }

            .hero-subtitle {
                font-size: 13px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-hero-primary,
            .btn-hero-secondary {
                width: 100%;
                justify-content: center;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .cta-section {
                padding: 27px 17px;
            }

            .cta-text h2 {
                font-size: 20px;
            }
        }

        /* Estilos para tabs del chat */
        .chat-tab {
            cursor: pointer;
            position: relative;
        }

        .chat-tab.active {
            color: #2e3a75;
            border-color: #2e3a75 !important;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Estilos para mensajes recibidos */
        .mensaje-item {
            padding: 12px;
            border-radius: 8px;
            background: #f8f9fa;
            border-left: 3px solid #2e3a75;
            transition: all 0.2s ease;
        }

        .mensaje-item:hover {
            background: #e9ecef;
            transform: translateX(2px);
        }

        .mensaje-item.no-leido {
            background: #e3f2fd;
            border-left-color: #2196f3;
        }

        .mensaje-item.no-leido::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: #2196f3;
            border-radius: 50%;
        }
        /* Info box styles */
        .info-box {
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .info-box-content {
            padding: 1rem;
            text-align: center;
        }

        .info-box-text {
            display: block;
            font-size: 0.875rem;
        }

        /* Botón flotante para bandeja de mensajes */
        .floating-chat-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            transition: all 0.3s ease;
            background-color: #2e3a75 !important;
            border: none;
        }

        .floating-chat-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
            background-color: #1e2f4d !important;
        }

        .floating-chat-btn i {
            font-size: 24px;
            color: white;
        }

        .floating-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            padding: 0 6px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .info-box-number {
            display: block;
            font-size: 1.25rem;
            font-weight: 600;
        }

        /* Estilos personalizados para el scrollbar del chat */
        #mensajesRecibidosContainer {
            scrollbar-width: thin;
            scrollbar-color: #2e3a75 #f1f1f1;
        }

        #mensajesRecibidosContainer::-webkit-scrollbar {
            width: 8px;
        }

        #mensajesRecibidosContainer::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        #mensajesRecibidosContainer::-webkit-scrollbar-thumb {
            background: #2e3a75;
            border-radius: 10px;
        }

        #mensajesRecibidosContainer::-webkit-scrollbar-thumb:hover {
            background: #1e2f4d;
        }

        /* Animación para nuevos mensajes */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .nuevo-mensaje-notif {
            animation: slideInRight 0.3s ease-out;
        }

        /* Efecto de pulsación en el badge de mensajes no leídos */
        @keyframes badgePulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        .chat-tab .bg-red-500 {
            animation: badgePulse 2s infinite;
        }

        .floating-badge {
            animation: badgePulse 2s infinite;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // ============================================
            // FUNCIONALIDAD DE TABS DEL CHAT
            // ============================================
            
            // Cambiar entre tabs
            $('.chat-tab').on('click', function() {
                const tab = $(this).data('tab');
                
                // Actualizar tabs activos
                $('.chat-tab').removeClass('active').css('border-color', 'transparent');
                $(this).addClass('active').css('border-color', '#2e3a75');
                
                // Mostrar contenido correspondiente
                $('.tab-content').removeClass('active').hide();
                $(`#tab-${tab}`).addClass('active').show();
                
                // Si es tab de recibidos, cargar mensajes
                if (tab === 'recibidos') {
                    cargarMensajesRecibidos();
                }
            });
            
            // Función para cargar mensajes recibidos
            function cargarMensajesRecibidos(preserveScroll = false) {
                // Guardar posición del scroll si se solicita
                const container = $('#mensajesRecibidosContainer');
                const scrollPos = preserveScroll ? container.scrollTop() : 0;
                
                $.ajax({
                    url: '{{ route("chat.mensajes") }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.success && response.mensajes.data.length > 0) {
                            let html = '';
                            // Mostrar TODOS los mensajes recibidos con scroll
                            response.mensajes.data.forEach(mensaje => {
                                const noLeido = !mensaje.leido && mensaje.destinatario_id === {{ auth()->id() }};
                                const esRecibido = mensaje.destinatario_id === {{ auth()->id() }};
                                
                                if (esRecibido) {
                                    const fecha = new Date(mensaje.created_at);
                                    const fechaFormato = fecha.toLocaleDateString('es-ES', { 
                                        day: '2-digit', 
                                        month: 'short',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    });
                                    
                                    html += `
                                        <div class="mensaje-item ${noLeido ? 'no-leido' : ''} relative mb-2" data-mensaje-id="${mensaje.id}">
                                            <div class="flex items-start gap-2">
                                                <div class="flex-shrink-0">
                                                    <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-xs font-bold" style="background-color: #2e3a75;">
                                                        ${mensaje.remitente.name.charAt(0).toUpperCase()}
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <p class="text-xs font-semibold text-slate-700 truncate">
                                                            ${mensaje.remitente.name}
                                                        </p>
                                                        <span class="text-[10px] text-slate-500">${fechaFormato}</span>
                                                    </div>
                                                    <p class="text-xs text-slate-600 line-clamp-2">${mensaje.mensaje}</p>
                                                    ${noLeido ? '<span class="inline-block mt-1 px-2 py-0.5 bg-blue-500 text-white text-[9px] rounded-full">Nuevo</span>' : ''}
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                }
                            });
                            
                            if (html === '') {
                                html = `
                                    <div class="text-center py-8">
                                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">inbox</span>
                                        <p class="text-sm text-slate-500">No tienes mensajes recibidos</p>
                                    </div>
                                `;
                            }
                            
                            $('#mensajesRecibidosContainer').html(html);
                            
                            // Restaurar posición del scroll si se solicitó
                            if (preserveScroll && scrollPos > 0) {
                                container.scrollTop(scrollPos);
                            }
                        } else {
                            $('#mensajesRecibidosContainer').html(`
                                <div class="text-center py-8">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">inbox</span>
                                    <p class="text-sm text-slate-500">No tienes mensajes recibidos</p>
                                </div>
                            `);
                        }
                    },
                    error: function() {
                        $('#mensajesRecibidosContainer').html(`
                            <div class="text-center py-8">
                                <span class="material-symbols-outlined text-4xl text-red-300 mb-2">error</span>
                                <p class="text-sm text-red-500">Error al cargar mensajes</p>
                            </div>
                        `);
                    }
                });
            }
            
            // Filtrar por categoría
            $('.category-btn').on('click', function() {
                $('.category-btn').removeClass('active');
                $(this).addClass('active');
                
                const categoria = $(this).data('categoria');
                
                if (categoria === 'Todos') {
                    $('.product-card').show();
                } else {
                    $('.product-card').each(function() {
                        if ($(this).data('categoria') === categoria) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }
            });

            // Animación de favoritos
            $('.favorite-btn').on('click', function(e) {
                e.preventDefault();
                const icon = $(this).find('.material-symbols-outlined');
                
                if (icon.css('font-variation-settings').includes('FILL 1')) {
                    icon.css('font-variation-settings', "'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24");
                    icon.css('color', '');
                } else {
                    icon.css('font-variation-settings', "'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24");
                    icon.css('color', '#e74c3c');
                }
            });

            // Animación de entrada
            $('.product-card').each(function(index) {
                $(this).css({
                    'opacity': '0',
                    'transform': 'translateY(20px)'
                });
                
                setTimeout(() => {
                    $(this).css({
                        'transition': 'all 0.5s ease',
                        'opacity': '1',
                        'transform': 'translateY(0)'
                    });
                }, index * 100);
            });

            // ============================================
            // FUNCIONALIDAD DEL CHAT INTERNO
            // ============================================
            
            let selectedUsers = [];
            let searchTimeout = null;
            
            // Búsqueda de usuarios
            $('#searchUser').on('input', function() {
                const query = $(this).val().trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    $('#userResults').addClass('hidden').empty();
                    return;
                }
                
                searchTimeout = setTimeout(() => {
                    $.ajax({
                        url: '{{ route("chat.buscar-usuarios") }}',
                        method: 'GET',
                        data: { query: query },
                        success: function(response) {
                            if (response.success && response.usuarios.length > 0) {
                                let html = '';
                                response.usuarios.forEach(usuario => {
                                    html += `
                                        <div class="user-result-item p-2 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-0" 
                                             data-user-id="${usuario.id}" 
                                             data-user-name="${usuario.nombre}"
                                             data-user-role="${usuario.role}">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-xs font-bold" style="background-color: #2e3a75;">
                                                    ${usuario.nombre.charAt(0).toUpperCase()}
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-slate-700">${usuario.nombre}</div>
                                                    <div class="text-xs text-slate-500">${usuario.role} • ${usuario.email}</div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                });
                                $('#userResults').html(html).removeClass('hidden');
                            } else {
                                $('#userResults').html('<div class="p-3 text-center text-sm text-slate-500">No se encontraron usuarios</div>').removeClass('hidden');
                            }
                        },
                        error: function() {
                            $('#userResults').html('<div class="p-3 text-center text-sm text-red-500">Error al buscar usuarios</div>').removeClass('hidden');
                        }
                    });
                }, 300);
            });
            
            // Seleccionar usuario de los resultados
            $(document).on('click', '.user-result-item', function() {
                const userId = $(this).data('user-id');
                const userName = $(this).data('user-name');
                const userRole = $(this).data('user-role');
                
                // Verificar si ya está seleccionado
                if (selectedUsers.find(u => u.id === userId)) {
                    return;
                }
                
                selectedUsers.push({ id: userId, name: userName, role: userRole });
                $('#searchUser').val('');
                $('#userResults').addClass('hidden').empty();
                
                actualizarDestinatarios();
            });
            
            // Contador de caracteres
            $('#messageText').on('input', function() {
                const length = $(this).val().length;
                $('#charCount').text(`${length} / 4000`);
                validarFormulario();
            });
            
            // Actualizar vista de destinatarios
            function actualizarDestinatarios() {
                const count = selectedUsers.length;
                $('#recipientCount').text(count);
                
                if (count === 0) {
                    $('#recipientText').text('Ninguno seleccionado');
                } else if (count === 1) {
                    $('#recipientText').text(selectedUsers[0].name);
                } else {
                    $('#recipientText').text(`${count} usuarios seleccionados`);
                }
                
                validarFormulario();
            }
            
            // Validar formulario
            function validarFormulario() {
                const mensaje = $('#messageText').val().trim();
                const tieneDestinatarios = selectedUsers.length > 0;
                
                if (mensaje.length > 0 && tieneDestinatarios) {
                    $('#sendMessageBtn').prop('disabled', false);
                } else {
                    $('#sendMessageBtn').prop('disabled', true);
                }
            }
            
            // Enviar mensaje
            $('#sendMessageBtn').on('click', function() {
                const mensaje = $('#messageText').val().trim();
                
                if (!mensaje) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Mensaje vacío',
                        text: 'Por favor escribe un mensaje antes de enviar'
                    });
                    return;
                }
                
                if (selectedUsers.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin destinatarios',
                        text: 'Por favor selecciona al menos un destinatario'
                    });
                    return;
                }
                
                let data = {
                    mensaje: mensaje,
                    tipo: 'individual',
                    destinatario_id: selectedUsers[0].id,
                    _token: '{{ csrf_token() }}'
                };
                
                // Deshabilitar botón mientras se envía
                $('#sendMessageBtn').prop('disabled', true).html('<span class="material-symbols-outlined animate-spin">sync</span><span>Enviando...</span>');
                
                $.ajax({
                    url: '{{ route("chat.enviar") }}',
                    method: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            // Limpiar formulario sin mostrar confirmación
                            $('#messageText').val('');
                            $('#charCount').text('0 / 4000');
                            selectedUsers = [];
                            actualizarDestinatarios();
                            
                            // Actualizar inmediatamente el contador de mensajes
                            verificarNuevosMensajes();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'No se pudo enviar el mensaje'
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al enviar el mensaje';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    },
                    complete: function() {
                        $('#sendMessageBtn').prop('disabled', false).html('<span class="material-symbols-outlined">send</span><span>Enviar Mensaje</span>');
                        validarFormulario();
                    }
                });
            });

            // ============================================
            // SISTEMA DE ACTUALIZACIÓN EN TIEMPO REAL
            // ============================================
            
            let chatPollingInterval = null;
            let lastMessageCount = 0;
            let isTabRecibidosActive = false;
            
            // Función para verificar nuevos mensajes
            function verificarNuevosMensajes() {
                $.ajax({
                    url: '{{ route("chat.mensajes") }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.success && response.mensajes.data) {
                            const mensajesRecibidos = response.mensajes.data.filter(m => m.destinatario_id === {{ auth()->id() }});
                            const currentCount = mensajesRecibidos.length;
                            const noLeidos = mensajesRecibidos.filter(m => !m.leido).length;
                            
                            // Actualizar badge de no leídos en el tab
                            const badgeHtml = noLeidos > 0 ? `<span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full ml-1">${noLeidos}</span>` : '';
                            $('.chat-tab[data-tab="recibidos"]').find('span.inline-flex').remove();
                            if (noLeidos > 0) {
                                $('.chat-tab[data-tab="recibidos"]').append(badgeHtml);
                            }
                            
                            // Actualizar badge del botón flotante
                            const floatingBadge = $('.floating-chat-btn .floating-badge');
                            if (noLeidos > 0) {
                                if (floatingBadge.length) {
                                    floatingBadge.text(noLeidos);
                                } else {
                                    $('.floating-chat-btn').append(`<span class="badge badge-danger floating-badge">${noLeidos}</span>`);
                                }
                            } else {
                                floatingBadge.remove();
                            }
                            
                            // Si hay nuevos mensajes y el tab está activo, recargar
                            if (currentCount > lastMessageCount && isTabRecibidosActive) {
                                cargarMensajesRecibidos(true); // Preservar scroll
                                
                                // Mostrar notificación visual sutil
                                if (lastMessageCount > 0) { // Solo si no es la primera carga
                                    mostrarNotificacionNuevoMensaje();
                                }
                            }
                            
                            lastMessageCount = currentCount;
                        }
                    },
                    error: function() {
                        console.log('Error al verificar nuevos mensajes');
                    }
                });
            }
            
            // Función para mostrar notificación sutil de nuevo mensaje
            function mostrarNotificacionNuevoMensaje() {
                // Crear notificación temporal
                const notif = $('<div class="nuevo-mensaje-notif">Nuevo mensaje recibido</div>');
                notif.css({
                    'position': 'fixed',
                    'top': '20px',
                    'right': '20px',
                    'background': '#2e3a75',
                    'color': 'white',
                    'padding': '12px 20px',
                    'border-radius': '8px',
                    'box-shadow': '0 4px 12px rgba(0,0,0,0.15)',
                    'z-index': '9999',
                    'font-size': '14px',
                    'font-weight': '600',
                    'opacity': '0',
                    'transition': 'opacity 0.3s ease'
                });
                
                $('body').append(notif);
                
                // Animar entrada
                setTimeout(() => notif.css('opacity', '1'), 10);
                
                // Animar salida y eliminar
                setTimeout(() => {
                    notif.css('opacity', '0');
                    setTimeout(() => notif.remove(), 300);
                }, 3000);
            }
            
            // Detectar cuando el tab de recibidos está activo
            $('.chat-tab').on('click', function() {
                const tab = $(this).data('tab');
                isTabRecibidosActive = (tab === 'recibidos');
                
                if (isTabRecibidosActive) {
                    cargarMensajesRecibidos();
                }
            });
            
            // Iniciar polling cada 5 segundos
            chatPollingInterval = setInterval(verificarNuevosMensajes, 5000);
            
            // Verificar inmediatamente al cargar
            verificarNuevosMensajes();
            
            // Detener polling cuando el usuario sale de la página
            $(window).on('beforeunload', function() {
                if (chatPollingInterval) {
                    clearInterval(chatPollingInterval);
                }
            });
            
            // Recargar mensajes cuando el usuario vuelve a la pestaña del navegador
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    verificarNuevosMensajes();
                    if (isTabRecibidosActive) {
                        cargarMensajesRecibidos();
                    }
                }
            });

            console.log('Dashboard Marketplace cargado correctamente');
            console.log('Usuario:', '{{ auth()->user()->full_name }}');
            console.log('Chat interno inicializado con actualización en tiempo real');
            console.log('Polling cada 5 segundos para nuevos mensajes');
        });
    </script>
@stop
