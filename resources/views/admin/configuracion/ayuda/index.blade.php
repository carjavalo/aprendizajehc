@extends('adminlte::page')

@section('title', 'Ayuda - Administrar Contenido de Inicio')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-tv mr-2"></i>Administrar Contenido de Pantalla de Inicio</h1>
        @can('ayuda.create')
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalCrear">
            <i class="fas fa-plus mr-1"></i> Nuevo Banner / Media
        </button>
        @endcan
    </div>
@stop

@section('content')

{{-- Mensajes de éxito/error --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

{{-- Vista previa de la pantalla de inicio --}}
<div class="card card-outline card-info mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-eye mr-2"></i>Vista Previa de la Pantalla de Inicio</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="preview-container" style="position: relative; background: url('{{ asset('images/huv.jpg') }}') no-repeat center center; background-size: cover; min-height: 400px; border-radius: 0 0 5px 5px;">
            <div style="background-color: rgba(0,0,0,0.6); min-height: 400px; padding: 20px;">
                
                {{-- Banner superior --}}
                @php $bannerActivo = $banners->where('activo', true)->first(); @endphp
                <div id="preview-banner" style="background-color: {{ $bannerActivo ? $bannerActivo->banner_color_fondo : '#2c4370' }}; border: 2px dashed rgba(255,255,255,0.3); border-radius: 8px; padding: 15px 25px; margin-bottom: 20px; text-align: center;">
                    <h3 style="color: {{ $bannerActivo ? $bannerActivo->banner_color_texto : '#fff' }}; margin: 0; font-weight: 600;">
                        {{ $bannerActivo ? $bannerActivo->banner_titulo : 'MENSAJE TIPO BANNER' }}
                    </h3>
                    @if($bannerActivo && $bannerActivo->banner_subtitulo)
                        <p style="color: {{ $bannerActivo ? $bannerActivo->banner_color_texto : '#fff' }}; margin: 5px 0 0; opacity: 0.9;">
                            {{ $bannerActivo->banner_subtitulo }}
                        </p>
                    @endif
                </div>

                <div class="row">
                    {{-- Área de video/imagen --}}
                    <div class="col-md-8">
                        <div id="preview-media" style="background-color: rgba(0,0,0,0.4); border: 2px dashed rgba(255,255,255,0.3); border-radius: 8px; min-height: 280px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            @if($bannerActivo)
                                @if($bannerActivo->media_tipo === 'video')
                                    @if($bannerActivo->esYoutube())
                                        <iframe src="{{ $bannerActivo->getYoutubeEmbedUrl() }}" style="width: 100%; height: 280px; border: none; border-radius: 8px;" allowfullscreen></iframe>
                                    @elseif($bannerActivo->media_archivo)
                                        <video controls style="width: 100%; height: 280px; object-fit: contain; border-radius: 8px;">
                                            <source src="/media/{{ $bannerActivo->media_archivo }}" type="video/mp4">
                                        </video>
                                    @else
                                        <span style="color: rgba(255,255,255,0.5); font-size: 1.2rem;">VIDEO ILUSTRATIVO</span>
                                    @endif
                                @else
                                    @if($bannerActivo->media_archivo)
                                        <img src="/media/{{ $bannerActivo->media_archivo }}" style="width: 100%; height: 280px; object-fit: contain; border-radius: 8px;" alt="{{ $bannerActivo->media_titulo }}">
                                    @else
                                        <span style="color: rgba(255,255,255,0.5); font-size: 1.2rem;">IMAGEN ILUSTRATIVA</span>
                                    @endif
                                @endif
                            @else
                                <span style="color: rgba(255,255,255,0.5); font-size: 1.2rem;">VIDEO ILUSTRATIVO</span>
                            @endif
                        </div>
                    </div>

                    {{-- Formulario de ejemplo (solo visual) --}}
                    <div class="col-md-4">
                        <div style="background-color: rgba(255,255,255,0.9); border-radius: 8px; padding: 15px; min-height: 280px;">
                            <div style="background: linear-gradient(135deg, #2c4370, #1e2f4d); color: white; padding: 10px; border-radius: 5px; margin-bottom: 10px; text-align: center;">
                                <strong style="font-size: 0.85rem;">Hospital Universitario del Valle</strong>
                                <p style="margin: 3px 0 0; font-size: 0.75rem;">Gestión Educativa</p>
                            </div>
                            <div style="color: #999; text-align: center; padding: 20px; font-size: 0.8rem;">
                                <i class="fas fa-lock" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                Formulario de Login / Registro<br>
                                <small class="text-muted">(No se modifica desde aquí)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Listado de banners/media --}}
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list mr-2"></i>Banners y Contenido Multimedia Configurados</h3>
    </div>
    <div class="card-body">
        @if($banners->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-photo-video text-muted" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3">No hay contenido configurado aún.</p>
                @can('ayuda.create')
                <button class="btn btn-primary" data-toggle="modal" data-target="#modalCrear">
                    <i class="fas fa-plus mr-1"></i> Crear Primer Banner
                </button>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Título del Banner</th>
                            <th>Tipo Media</th>
                            <th>Título del Media</th>
                            <th style="width: 120px;">Vigencia</th>
                            <th style="width: 100px;">Estado</th>
                            <th style="width: 80px;">Colores</th>
                            <th style="width: 200px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-banners">
                        @foreach($banners as $banner)
                        <tr data-id="{{ $banner->id }}">
                            <td><i class="fas fa-grip-vertical text-muted mr-1" style="cursor: grab;"></i> {{ $banner->orden + 1 }}</td>
                            <td>
                                <strong>{{ $banner->banner_titulo }}</strong>
                                @if($banner->banner_subtitulo)
                                    <br><small class="text-muted">{{ $banner->banner_subtitulo }}</small>
                                @endif
                            </td>
                            <td>
                                @if($banner->media_tipo === 'video')
                                    <span class="badge badge-info"><i class="fas fa-video mr-1"></i>Video</span>
                                    @if($banner->esYoutube())
                                        <span class="badge badge-danger"><i class="fab fa-youtube mr-1"></i>YouTube</span>
                                    @endif
                                @else
                                    <span class="badge badge-success"><i class="fas fa-image mr-1"></i>Imagen</span>
                                @endif
                            </td>
                            <td>{{ $banner->media_titulo ?? '-' }}</td>
                            <td>
                                @if($banner->fecha_inicio || $banner->fecha_fin)
                                    <small>
                                        @if($banner->fecha_inicio)
                                            <i class="fas fa-play-circle text-success"></i> {{ $banner->fecha_inicio->format('d/m/Y') }}
                                        @else
                                            <i class="fas fa-play-circle text-muted"></i> Siempre
                                        @endif
                                        <br>
                                        @if($banner->fecha_fin)
                                            <i class="fas fa-stop-circle text-danger"></i> {{ $banner->fecha_fin->format('d/m/Y') }}
                                        @else
                                            <i class="fas fa-stop-circle text-muted"></i> Sin fin
                                        @endif
                                    </small>
                                    @if(!$banner->estaVigente())
                                        <br><span class="badge badge-warning"><i class="fas fa-clock"></i> No vigente</span>
                                    @endif
                                @else
                                    <small class="text-muted">Permanente</small>
                                @endif
                            </td>
                            <td>
                                @can('ayuda.edit')
                                <button class="btn btn-sm toggle-activo {{ $banner->activo ? 'btn-success' : 'btn-secondary' }}" data-id="{{ $banner->id }}">
                                    <i class="fas {{ $banner->activo ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                    {{ $banner->activo ? 'Activo' : 'Inactivo' }}
                                </button>
                                @else
                                <span class="badge {{ $banner->activo ? 'badge-success' : 'badge-secondary' }}">
                                    <i class="fas {{ $banner->activo ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                    {{ $banner->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                                @endcan
                            </td>
                            <td>
                                <span style="display: inline-block; width: 22px; height: 22px; border-radius: 4px; background-color: {{ $banner->banner_color_fondo }}; border: 1px solid #ddd;" title="Fondo: {{ $banner->banner_color_fondo }}"></span>
                                <span style="display: inline-block; width: 22px; height: 22px; border-radius: 4px; background-color: {{ $banner->banner_color_texto }}; border: 1px solid #ddd;" title="Texto: {{ $banner->banner_color_texto }}"></span>
                            </td>
                            <td>
                                @can('ayuda.edit')
                                <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalEditar{{ $banner->id }}" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                <button class="btn btn-sm btn-info btn-preview-media" data-id="{{ $banner->id }}" title="Vista previa">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @can('ayuda.delete')
                                <form action="/configuracion/ayuda/{{ $banner->id }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este banner?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- MODAL: Crear nuevo banner --}}
@can('ayuda.create')
<div class="modal fade" id="modalCrear" tabindex="-1" role="dialog" aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formCrearBanner" action="/configuracion/ayuda" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalCrearLabel"><i class="fas fa-plus-circle mr-2"></i>Nuevo Banner / Contenido Multimedia</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    {{-- Contenedor de errores del formulario --}}
                    <div id="crear-form-errors" class="alert alert-danger" style="display:none;"></div>
                    <div class="row">
                        {{-- Configuración del Banner Superior --}}
                        <div class="col-md-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-heading mr-1"></i> Configuración del Banner Superior
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="banner_titulo_crear"><strong>Título del Banner</strong> <span class="text-danger">*</span></label>
                                <input type="text" name="banner_titulo" id="banner_titulo_crear" class="form-control" placeholder="Ej: Bienvenidos al Hospital Universitario" required>
                                <small class="form-text text-muted">Este texto se muestra en la franja superior de la pantalla de inicio.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="banner_subtitulo_crear"><strong>Subtítulo</strong></label>
                                <input type="text" name="banner_subtitulo" id="banner_subtitulo_crear" class="form-control" placeholder="Ej: Plataforma de Gestión Educativa">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="banner_color_fondo_crear"><strong>Color de Fondo</strong></label>
                                <input type="color" name="banner_color_fondo" id="banner_color_fondo_crear" class="form-control" value="#2c4370" style="height: 40px;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="banner_color_texto_crear"><strong>Color del Texto</strong></label>
                                <input type="color" name="banner_color_texto" id="banner_color_texto_crear" class="form-control" value="#ffffff" style="height: 40px;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Estado</strong></label>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="checkbox" class="custom-control-input" id="activo_crear" name="activo" checked>
                                    <label class="custom-control-label" for="activo_crear">Activo (visible en la pantalla de inicio)</label>
                                </div>
                            </div>
                        </div>

                        {{-- Programación por fechas --}}
                        <div class="col-md-12 mt-3">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-calendar-alt mr-1"></i> Programación de Vigencia
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_inicio_crear"><strong>Fecha de Inicio</strong></label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio_crear" class="form-control">
                                <small class="form-text text-muted">Dejar vacío = visible inmediatamente.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_fin_crear"><strong>Fecha de Finalización</strong></label>
                                <input type="date" name="fecha_fin" id="fecha_fin_crear" class="form-control">
                                <small class="form-text text-muted">Dejar vacío = sin fecha de expiración.</small>
                            </div>
                        </div>

                        {{-- Configuración del Media --}}
                        <div class="col-md-12 mt-3">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-photo-video mr-1"></i> Contenido Multimedia (Video o Imagen)
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="media_tipo_crear"><strong>Tipo de Media</strong> <span class="text-danger">*</span></label>
                                <select name="media_tipo" id="media_tipo_crear" class="form-control select-media-tipo" required>
                                    <option value="video">Video</option>
                                    <option value="imagen">Imagen</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="media_titulo_crear"><strong>Título del Media</strong></label>
                                <input type="text" name="media_titulo" id="media_titulo_crear" class="form-control" placeholder="Ej: Video institucional 2026">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="media_archivo_crear"><strong>Subir Archivo</strong></label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="media_archivo" id="media_archivo_crear" accept="video/mp4,video/webm,video/ogg,image/jpeg,image/png,image/gif,image/webp">
                                    <label class="custom-file-label" for="media_archivo_crear" data-browse="Explorar">Seleccionar archivo...</label>
                                </div>
                                <small class="form-text text-muted">Formatos: MP4, WebM, OGG, JPG, PNG, GIF, WebP. Máx: 50MB</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="media_url_crear"><strong>O URL externa (YouTube, etc.)</strong></label>
                                <input type="url" name="media_url" id="media_url_crear" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                                <small class="form-text text-muted">Si se especifica URL, tiene prioridad sobre el archivo subido.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-block">
                    <div class="upload-progress mb-2">
                        <small class="text-muted"><i class="fas fa-cloud-upload-alt mr-1"></i> Subiendo archivo...</small>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%">0%</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-submit-banner" id="btnGuardarBanner">
                            <i class="fas fa-save mr-1"></i> Guardar Banner
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- MODALES: Editar cada banner --}}
@can('ayuda.edit')
@foreach($banners as $banner)
<div class="modal fade" id="modalEditar{{ $banner->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form class="form-banner-submit" action="/configuracion/ayuda/{{ $banner->id }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Editar Banner #{{ $banner->id }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger form-errors-container" style="display:none;"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-heading mr-1"></i> Configuración del Banner Superior
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Título del Banner</strong> <span class="text-danger">*</span></label>
                                <input type="text" name="banner_titulo" class="form-control" value="{{ $banner->banner_titulo }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Subtítulo</strong></label>
                                <input type="text" name="banner_subtitulo" class="form-control" value="{{ $banner->banner_subtitulo }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><strong>Color de Fondo</strong></label>
                                <input type="color" name="banner_color_fondo" class="form-control" value="{{ $banner->banner_color_fondo }}" style="height: 40px;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><strong>Color del Texto</strong></label>
                                <input type="color" name="banner_color_texto" class="form-control" value="{{ $banner->banner_color_texto }}" style="height: 40px;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Estado</strong></label>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="checkbox" class="custom-control-input" id="activo_editar_{{ $banner->id }}" name="activo" {{ $banner->activo ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="activo_editar_{{ $banner->id }}">Activo</label>
                                </div>
                            </div>
                        </div>

                        {{-- Programación por fechas --}}
                        <div class="col-md-12 mt-3">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-calendar-alt mr-1"></i> Programación de Vigencia
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Fecha de Inicio</strong></label>
                                <input type="date" name="fecha_inicio" class="form-control" value="{{ $banner->fecha_inicio ? $banner->fecha_inicio->format('Y-m-d') : '' }}">
                                <small class="form-text text-muted">Dejar vacío = visible inmediatamente.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Fecha de Finalización</strong></label>
                                <input type="date" name="fecha_fin" class="form-control" value="{{ $banner->fecha_fin ? $banner->fecha_fin->format('Y-m-d') : '' }}">
                                <small class="form-text text-muted">Dejar vacío = sin fecha de expiración.</small>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-photo-video mr-1"></i> Contenido Multimedia
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Tipo de Media</strong></label>
                                <select name="media_tipo" class="form-control select-media-tipo">
                                    <option value="video" {{ $banner->media_tipo === 'video' ? 'selected' : '' }}>Video</option>
                                    <option value="imagen" {{ $banner->media_tipo === 'imagen' ? 'selected' : '' }}>Imagen</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Título del Media</strong></label>
                                <input type="text" name="media_titulo" class="form-control" value="{{ $banner->media_titulo }}">
                            </div>
                        </div>

                        {{-- Mostrar archivo/URL actual --}}
                        @if($banner->media_archivo || $banner->media_url)
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Media actual:</strong>
                                @if($banner->media_url)
                                    URL: <a href="{{ $banner->media_url }}" target="_blank">{{ Str::limit($banner->media_url, 60) }}</a>
                                @elseif($banner->media_archivo)
                                    Archivo: {{ basename($banner->media_archivo) }}
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Subir Nuevo Archivo</strong></label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="media_archivo" id="media_archivo_editar_{{ $banner->id }}" accept="video/mp4,video/webm,video/ogg,image/jpeg,image/png,image/gif,image/webp">
                                    <label class="custom-file-label" for="media_archivo_editar_{{ $banner->id }}" data-browse="Explorar">Seleccionar archivo...</label>
                                </div>
                                <small class="form-text text-muted">Deja vacío para mantener el archivo actual.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>O URL externa</strong></label>
                                <input type="url" name="media_url" class="form-control" value="{{ $banner->media_url }}" placeholder="https://www.youtube.com/watch?v=...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-block">
                    <div class="upload-progress mb-2">
                        <small class="text-muted"><i class="fas fa-cloud-upload-alt mr-1"></i> Subiendo archivo...</small>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 0%">0%</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning btn-submit-banner"><i class="fas fa-save mr-1"></i> Actualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endcan

{{-- MODAL: Vista previa de media --}}
<div class="modal fade" id="modalPreviewMedia" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-eye mr-2"></i>Vista Previa</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center p-4" id="preview-media-body" style="min-height: 300px; background: #111;">
                <p class="text-muted">Cargando...</p>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<style>
    .toggle-activo {
        min-width: 100px;
        transition: all 0.3s;
    }
    .custom-file-label::after {
        content: "Explorar";
    }
    #sortable-banners tr {
        transition: background-color 0.3s;
    }
    #sortable-banners tr:hover {
        background-color: rgba(0,123,255,0.05);
    }
    .btn-submit-banner.loading {
        pointer-events: none;
        opacity: 0.75;
    }
    .upload-progress {
        display: none;
        margin-top: 10px;
    }
    .upload-progress .progress {
        height: 25px;
    }
    .upload-progress .progress-bar {
        font-size: 0.85rem;
        font-weight: 600;
        line-height: 25px;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {

    // =============================================
    // MANEJO ROBUSTO DE ENVÍO DE FORMULARIOS
    // =============================================
    var MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB

    function validarFormulario(form) {
        var errores = [];
        var titulo = form.find('input[name="banner_titulo"]').val();
        if (!titulo || titulo.trim() === '') {
            errores.push('El título del banner es obligatorio.');
        }
        // Validar tamaño de archivo
        var fileInput = form.find('input[name="media_archivo"]')[0];
        if (fileInput && fileInput.files && fileInput.files.length > 0) {
            var file = fileInput.files[0];
            if (file.size > MAX_FILE_SIZE) {
                var sizeMB = (file.size / 1024 / 1024).toFixed(1);
                errores.push('El archivo es demasiado grande (' + sizeMB + 'MB). Máximo permitido: 50MB.');
            }
        }
        return errores;
    }

    function mostrarErrores(form, errores) {
        var container = form.find('.form-errors-container, #crear-form-errors');
        if (container.length) {
            var html = '<ul class="mb-0">';
            errores.forEach(function(err) { html += '<li>' + err + '</li>'; });
            html += '</ul>';
            container.html(html).slideDown(200);
            container[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            alert(errores.join('\n'));
        }
    }

    function enviarFormularioAjax(form) {
        var errores = validarFormulario(form);
        if (errores.length > 0) {
            mostrarErrores(form, errores);
            return;
        }

        var btn = form.find('.btn-submit-banner');
        var btnTextoOriginal = btn.html();
        var errorContainer = form.find('.form-errors-container, #crear-form-errors');

        // Ocultar errores previos
        errorContainer.hide();

        // Mostrar estado de carga
        btn.addClass('loading').html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');
        btn.prop('disabled', true);

        var formData = new FormData(form[0]);
        var actionUrl = form.attr('action');
        
        // Asegurar URL absoluta con el origen actual
        if (actionUrl.startsWith('/')) {
            actionUrl = window.location.origin + actionUrl;
        }

        var xhr = new XMLHttpRequest();
        xhr.open(form.attr('method') || 'POST', actionUrl, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'text/html, application/json');

        // Mostrar barra de progreso si hay archivo
        var fileInput = form.find('input[name="media_archivo"]')[0];
        var tieneArchivo = fileInput && fileInput.files && fileInput.files.length > 0;
        var progressBar = form.find('.upload-progress');
        
        if (tieneArchivo && progressBar.length) {
            progressBar.show();
        }

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable && progressBar.length) {
                var percent = Math.round((e.loaded / e.total) * 100);
                progressBar.find('.progress-bar').css('width', percent + '%').text(percent + '%');
            }
        });

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 400) {
                // Éxito - recargar página
                window.location.reload();
            } else if (xhr.status === 422) {
                // Error de validación Laravel
                try {
                    var response = JSON.parse(xhr.responseText);
                    var errs = [];
                    if (response.errors) {
                        Object.keys(response.errors).forEach(function(key) {
                            response.errors[key].forEach(function(msg) { errs.push(msg); });
                        });
                    } else if (response.message) {
                        errs.push(response.message);
                    }
                    mostrarErrores(form, errs);
                } catch(e) {
                    mostrarErrores(form, ['Error de validación. Verifica los campos.']);
                }
                btn.removeClass('loading').html(btnTextoOriginal).prop('disabled', false);
                progressBar.hide();
            } else if (xhr.status === 419) {
                mostrarErrores(form, ['La sesión ha expirado. Recarga la página e intenta de nuevo.']);
                btn.removeClass('loading').html(btnTextoOriginal).prop('disabled', false);
                progressBar.hide();
            } else {
                mostrarErrores(form, ['Error del servidor (código ' + xhr.status + '). Intenta de nuevo o contacta al administrador.']);
                btn.removeClass('loading').html(btnTextoOriginal).prop('disabled', false);
                progressBar.hide();
                console.error('Error en envío de banner:', xhr.status, xhr.responseText);
            }
        };

        xhr.onerror = function() {
            mostrarErrores(form, ['Error de conexión. Verifica tu conexión a internet e intenta de nuevo.']);
            btn.removeClass('loading').html(btnTextoOriginal).prop('disabled', false);
            progressBar.hide();
            console.error('Error de red al enviar formulario de banner');
        };

        xhr.ontimeout = function() {
            mostrarErrores(form, ['La solicitud tardó demasiado tiempo. Si estás subiendo un archivo grande, intenta con uno más pequeño o usa una URL de YouTube.']);
            btn.removeClass('loading').html(btnTextoOriginal).prop('disabled', false);
            progressBar.hide();
        };

        xhr.timeout = 300000; // 5 minutos
        xhr.send(formData);
    }

    // Interceptar envío del formulario de CREAR
    $('#formCrearBanner').on('submit', function(e) {
        e.preventDefault();
        enviarFormularioAjax($(this));
    });

    // Interceptar envío de formularios de EDITAR
    $(document).on('submit', '.form-banner-submit', function(e) {
        e.preventDefault();
        enviarFormularioAjax($(this));
    });

    // Actualizar label del file input al seleccionar archivo
    $(document).on('change', '.custom-file-input', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName || 'Seleccionar archivo...');
        
        // Validar tamaño inmediatamente
        if (this.files && this.files.length > 0) {
            var file = this.files[0];
            if (file.size > MAX_FILE_SIZE) {
                var sizeMB = (file.size / 1024 / 1024).toFixed(1);
                var form = $(this).closest('form');
                mostrarErrores(form, ['El archivo seleccionado es demasiado grande (' + sizeMB + 'MB). Máximo permitido: 50MB. Considera usar una URL de YouTube en lugar de subir el archivo.']);
                $(this).val('');
                $(this).siblings('.custom-file-label').html('Seleccionar archivo...');
            } else {
                var form = $(this).closest('form');
                form.find('.form-errors-container, #crear-form-errors').slideUp(200);
            }
        }
    });

    // Toggle activo/inactivo
    $(document).on('click', '.toggle-activo', function() {
        var btn = $(this);
        var id = btn.data('id');

        $.ajax({
            url: '/configuracion/ayuda/' + id + '/toggle',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    if (response.activo) {
                        btn.removeClass('btn-secondary').addClass('btn-success');
                        btn.html('<i class="fas fa-eye"></i> Activo');
                    } else {
                        btn.removeClass('btn-success').addClass('btn-secondary');
                        btn.html('<i class="fas fa-eye-slash"></i> Inactivo');
                    }
                    location.reload();
                }
            },
            error: function() {
                alert('Error al cambiar el estado.');
            }
        });
    });

    // Vista previa individual de media
    var bannersData = @json($banners);
    $(document).on('click', '.btn-preview-media', function() {
        var id = $(this).data('id');
        var banner = bannersData.find(function(b) { return b.id == id; });
        var html = '';

        if (banner) {
            if (banner.media_url && (banner.media_url.includes('youtube.com') || banner.media_url.includes('youtu.be'))) {
                var videoId = '';
                var match = banner.media_url.match(/[?&]v=([^&]+)/);
                if (match) videoId = match[1];
                else {
                    match = banner.media_url.match(/youtu\.be\/([^?&]+)/);
                    if (match) videoId = match[1];
                }
                if (videoId) {
                    html = '<iframe src="https://www.youtube.com/embed/' + videoId + '" style="width:100%;height:450px;border:none;" allowfullscreen></iframe>';
                }
            } else if (banner.media_tipo === 'video' && banner.media_archivo) {
                html = '<video controls autoplay style="max-width:100%;max-height:450px;"><source src="/media/' + banner.media_archivo + '" type="video/mp4"></video>';
            } else if (banner.media_tipo === 'imagen' && banner.media_archivo) {
                html = '<img src="/media/' + banner.media_archivo + '" style="max-width:100%;max-height:450px;" alt="Vista previa">';
            } else {
                html = '<p class="text-white mt-5">No hay media configurado para este banner.</p>';
            }

            html = '<div style="background-color:' + banner.banner_color_fondo + ';padding:10px 20px;border-radius:5px;margin-bottom:15px;"><h4 style="color:' + banner.banner_color_texto + ';margin:0;">' + banner.banner_titulo + '</h4></div>' + html;
        }

        $('#preview-media-body').html(html);
        $('#modalPreviewMedia').modal('show');
    });

    // Limpiar iframe/video al cerrar modal preview
    $('#modalPreviewMedia').on('hidden.bs.modal', function () {
        $('#preview-media-body').html('<p class="text-muted">Cargando...</p>');
    });

    // Limpiar errores al abrir modales
    $('.modal').on('show.bs.modal', function() {
        $(this).find('.form-errors-container, #crear-form-errors').hide();
    });
});
</script>
@stop