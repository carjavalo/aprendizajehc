@extends('adminlte::page')

@section('title', 'Publicidad y Productos')

@section('content_header')
    <h1><i class="fas fa-bullhorn text-primary"></i> Publicidad y Productos</h1>
@stop

@section('content')
<div class="row">
    <!-- Configuración del Banner -->
    @can('publicidad.banner')
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-image"></i> Configuración del Banner Principal</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="form-configuracion">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="banner_titulo">Título del Banner</label>
                                <textarea class="form-control" id="banner_titulo" name="banner_titulo" rows="2" placeholder="Título principal del banner">{{ $configuracion['banner_titulo'] ?? '' }}</textarea>
                                <small class="text-muted">Puedes usar &lt;br/&gt; para saltos de línea</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="banner_subtitulo">Subtítulo del Banner</label>
                                <textarea class="form-control" id="banner_subtitulo" name="banner_subtitulo" rows="2" placeholder="Descripción o subtítulo">{{ $configuracion['banner_subtitulo'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="banner_imagen">Imagen del Banner</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="banner_imagen" name="banner_imagen" accept="image/*">
                                        <label class="custom-file-label" for="banner_imagen">Seleccionar imagen...</label>
                                    </div>
                                </div>
                                <small class="text-muted">O ingresa una URL de imagen externa</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="banner_url_imagen">URL de Imagen Externa</label>
                                <input type="url" class="form-control" id="banner_url_imagen" name="banner_url_imagen" placeholder="https://ejemplo.com/imagen.jpg" value="{{ $configuracion['banner_imagen'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="mostrar_categorias" name="mostrar_categorias" {{ ($configuracion['mostrar_categorias'] ?? true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="mostrar_categorias">Mostrar sección de categorías</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="mostrar_seccion_vendedor" name="mostrar_seccion_vendedor" {{ ($configuracion['mostrar_seccion_vendedor'] ?? true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="mostrar_seccion_vendedor">Mostrar sección de inscripción</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Configuración
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    <!-- Gestión de Categorías -->
    <div class="col-md-12">
        <div class="card card-info card-outline collapsed-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tags"></i> Gestión de Categorías</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="categorias-container">
                    @foreach($categorias as $index => $categoria)
                    <div class="row categoria-row mb-2 align-items-center">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="categorias[{{ $index }}][nombre]" value="{{ $categoria['nombre'] }}" placeholder="Nombre">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="categorias[{{ $index }}][icono]" value="{{ $categoria['icono'] }}" placeholder="Icono (Material Symbols)">
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="cat_activo_{{ $index }}" name="categorias[{{ $index }}][activo]" {{ ($categoria['activo'] ?? true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="cat_activo_{{ $index }}">Activo</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm btn-eliminar-categoria">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-success btn-sm" id="btn-agregar-categoria">
                        <i class="fas fa-plus"></i> Agregar Categoría
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-guardar-categorias">
                        <i class="fas fa-save"></i> Guardar Categorías
                    </button>
                </div>
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle"></i> Iconos disponibles en: <a href="https://fonts.google.com/icons" target="_blank">Material Symbols</a>
                </small>
            </div>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div class="col-md-12">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-box"></i> Productos / Cursos Promocionados</h3>
                <div class="card-tools">
                    @can('publicidad.create')
                    <button type="button" class="btn btn-primary btn-sm" id="btn-nuevo-producto">
                        <i class="fas fa-plus"></i> Agregar Producto
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <table id="tabla-productos" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="80">Imagen</th>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th width="100">Estado</th>
                            <th width="80">Orden</th>
                            <th width="100">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $producto)
                        <tr>
                            <td>
                                @if($producto['imagen'])
                                    <img src="{{ url('media/' . $producto['imagen']) }}" class="img-thumbnail" style="max-width: 60px;">
                                @else
                                    <span class="badge badge-secondary">Sin imagen</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $producto['titulo'] }}</strong>
                                @if($producto['descripcion'])
                                    <br><small class="text-muted">{{ Str::limit($producto['descripcion'], 50) }}</small>
                                @endif
                            </td>
                            <td>{{ $producto['categoria'] }}</td>
                            <td>{{ $producto['precio'] ? '$' . number_format($producto['precio'], 2) : 'Gratis' }}</td>
                            <td>
                                @if($producto['estado'] == 'activo')
                                    <span class="badge badge-success">Activo</span>
                                @elseif($producto['estado'] == 'destacado')
                                    <span class="badge badge-warning">Destacado</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td>{{ $producto['orden'] }}</td>
                            <td>
                                @can('publicidad.edit')
                                <button class="btn btn-sm btn-info btn-editar" data-producto='@json($producto)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @can('publicidad.delete')
                                <button class="btn btn-sm btn-danger btn-eliminar" data-id="{{ $producto['id'] }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Producto - Tamaño Aumentado 80% -->
<div class="modal fade" id="modal-producto" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-xl" style="max-width: 1100px;">
        <div class="modal-content" style="border-radius: 10px;">
            <div class="modal-header py-3" style="background: linear-gradient(135deg, #2c4370 0%, #1e2f4d 100%); border: none;">
                <h5 class="modal-title text-white font-weight-bold mb-0">
                    <i class="fas fa-box-open"></i> <span id="modal-titulo">Agregar Producto</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 1;">
                    <span style="font-size: 1.5rem;">&times;</span>
                </button>
            </div>
            <form id="form-producto" enctype="multipart/form-data">
                <div class="modal-body p-4" style="background: #f8f9fa;">
                    <input type="hidden" id="producto_id" name="id">
                    
                    <div class="row">
                        <!-- Columna Izquierda: Formulario -->
                        <div class="col-lg-7">
                            <!-- Imagen -->
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="card-body p-3">
                                    <label class="font-weight-bold mb-2" style="color: #2c4370;"><i class="fas fa-image"></i> Imagen del Producto</label>
                                    
                                    <div id="drop-zone" class="border text-center p-4" style="border: 2px dashed #cbd5e0 !important; border-radius: 8px; background: #fff; cursor: pointer;">
                                        <i class="fas fa-cloud-upload-alt" style="font-size: 2.5rem; color: #2c4370;"></i>
                                        <p class="mb-2 font-weight-bold" style="color: #2d3748;">Arrastra y suelta la imagen del producto</p>
                                        <p class="mb-0 text-muted">JPG, PNG (máximo 10MB) - Tamaño recomendado 1080x1080</p>
                                        <input type="file" id="imagen" name="imagen" accept="image/*" style="display: none;">
                                    </div>
                                    
                                    <div id="image-preview-container" class="mt-3" style="display: none;">
                                        <div class="position-relative d-inline-block">
                                            <img id="img-preview-modal" src="" class="img-fluid rounded shadow" style="max-height: 150px;">
                                            <button type="button" class="btn btn-danger btn-sm position-absolute" id="btn-remove-image" style="top: 5px; right: 5px; border-radius: 50%; width: 28px; height: 28px; padding: 0;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detalles -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-3">
                                    <label class="font-weight-bold mb-2" style="color: #2c4370;"><i class="fas fa-info-circle"></i> Detalles del Producto</label>
                                    
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold">Título del Producto *</label>
                                        <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="ej. Curso de Capacitación en Salud" style="border-radius: 6px;">
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold">Categoría *</label>
                                                <select class="form-control" id="categoria" name="categoria" required style="border-radius: 6px;">
                                                    @foreach($categorias as $cat)
                                                        @if($cat['nombre'] != 'Todos')
                                                            <option value="{{ $cat['nombre'] }}">{{ $cat['nombre'] }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold">Estado *</label>
                                                <select class="form-control" id="estado" name="estado" required style="border-radius: 6px;">
                                                    <option value="activo">Nuevo</option>
                                                    <option value="destacado">Destacado</option>
                                                    <option value="inactivo">Usado</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold">Precio ($)</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text font-weight-bold">$</span>
                                                    </div>
                                                    <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" placeholder="0.00">
                                                </div>
                                                <small class="text-muted">Dejar en 0 para "Gratis"</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold">Orden de visualización</label>
                                                <input type="number" class="form-control" id="orden" name="orden" min="0" placeholder="1" style="border-radius: 6px;">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold">Descripción</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4" placeholder="Describe tu producto en detalle..." style="resize: none; border-radius: 6px;"></textarea>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold">URL Externa (opcional)</label>
                                        <input type="url" class="form-control" id="url_externa" name="url_externa" placeholder="https://..." style="border-radius: 6px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Vista Previa -->
                        <div class="col-lg-5">
                            <div class="card border-0 shadow-lg">
                                <div class="card-header text-white py-2 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #2c4370 0%, #1e2f4d 100%);">
                                    <span class="font-weight-bold">VISTA PREVIA EN VIVO</span>
                                    <span class="badge badge-light" style="color: #2c4370;">Live</span>
                                </div>
                                
                                <div class="position-relative" style="background: #f0f4f7; height: 250px; overflow: hidden;">
                                    <img id="preview-product-image" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Crect width='400' height='300' fill='%23e2e8f0'/%3E%3Ctext x='50%25' y='50%25' font-family='Arial,sans-serif' font-size='16' fill='%2364748b' text-anchor='middle' dy='.3em'%3ESin Imagen%3C/text%3E%3C/svg%3E" class="w-100 h-100" style="object-fit: cover; opacity: 0.7;">
                                </div>

                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0 font-weight-bold" id="preview-titulo" style="color: #2d3748; flex: 1;">Título del Producto</h6>
                                        <span class="font-weight-bold ml-2" id="preview-precio" style="color: #2c4370; font-size: 1.1rem;">$0.00</span>
                                    </div>

                                    <div class="d-flex align-items-center mb-2">
                                        <div class="text-warning mr-2">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                        </div>
                                        <span class="text-muted small">4.8 (124 reseñas)</span>
                                    </div>

                                    <div class="mb-2">
                                        <span class="badge badge-light mr-1" id="preview-estado" style="background: #edf2f7; color: #4a5568;">Nuevo</span>
                                        <span class="badge badge-light" id="preview-categoria" style="background: #edf2f7; color: #4a5568;">Categoría</span>
                                    </div>

                                    <div class="border-top pt-2 mt-2">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-secondary mr-2" style="width: 35px; height: 35px;"></div>
                                                <div>
                                                    <p class="mb-0 font-weight-bold small" style="color: #2d3748;">Vendedor</p>
                                                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">100% Positivo</p>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-light btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0;">
                                                <i class="fas fa-heart text-muted"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info mt-3 p-3 mb-0" style="border-radius: 8px; border: none; background: rgba(44, 67, 112, 0.1);">
                                <i class="fas fa-lightbulb mr-2" style="color: #2c4370;"></i>
                                <span style="color: #1e2f4d; font-size: 0.85rem;">
                                    Los productos con fotos claras y de alta resolución se venden 45% más rápido.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-3" style="background: white; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-light" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn text-white" style="background: linear-gradient(135deg, #2c4370 0%, #1e2f4d 100%); border: none; padding: 0.5rem 2rem;">
                        <i class="fas fa-check"></i> Publicar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
/* Variables de colores corporativos */
:root {
    --corp-primary: #2c4370;
    --corp-primary-dark: #1e2f4d;
    --corp-primary-light: #3d5a8a;
}

.categoria-row {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
}
.categoria-row:hover {
    background: #e9ecef;
}

/* Estilos para el nuevo modal */
.border-dashed {
    transition: all 0.3s ease;
}

.border-dashed:hover {
    border-color: var(--corp-primary) !important;
    background: rgba(44, 67, 112, 0.05) !important;
}

#drop-zone {
    transition: all 0.3s ease;
}

.modal-xl {
    max-width: 1200px !important;
}

@media (max-width: 991px) {
    .modal-xl {
        max-width: 95% !important;
    }
}

/* Animaciones suaves */
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-outline-primary:hover {
    background-color: var(--corp-primary) !important;
    border-color: var(--corp-primary) !important;
    color: white !important;
}

/* Preview de imagen con efecto */
#preview-product-image {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

#image-preview-container img {
    transition: transform 0.2s ease;
}

#image-preview-container img:hover {
    transform: scale(1.02);
}

/* Estilos para el editor de texto */
[data-action] {
    transition: all 0.2s ease;
}

[data-action]:hover {
    background: #e2e8f0 !important;
    color: var(--corp-primary) !important;
}

/* Scrollbar personalizado para el modal */
.modal-body::-webkit-scrollbar {
    width: 8px;
}

.modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: var(--corp-primary);
    border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: var(--corp-primary-dark);
}

/* Botón de submit con hover corporativo */
button[type="submit"]:hover {
    background: linear-gradient(135deg, var(--corp-primary-dark) 0%, #0f1829 100%) !important;
    box-shadow: 0 4px 15px rgba(44, 67, 112, 0.4);
}

/* Focus en inputs con color corporativo */
.form-control:focus {
    border-color: var(--corp-primary) !important;
    box-shadow: 0 0 0 0.2rem rgba(44, 67, 112, 0.25) !important;
}

select.form-control:focus {
    border-color: var(--corp-primary) !important;
    box-shadow: 0 0 0 0.2rem rgba(44, 67, 112, 0.25) !important;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#tabla-productos').DataTable({
        responsive: true,
        language: {
            url: '{{ asset("js/datatables-spanish.json") }}'
        },
        order: [[5, 'asc']]
    });

    // ========== MODAL COMPACTO: Funcionalidades ==========
    
    // Drag & Drop y Click para imágenes
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('imagen');
    
    // Prevenir comportamiento por defecto
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    // Highlight en drag over
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.style.borderColor = '#2c4370';
            dropZone.style.background = 'rgba(44, 67, 112, 0.05)';
        }, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.style.borderColor = '#cbd5e0';
            dropZone.style.background = '#fff';
        }, false);
    });
    
    // Handle drop
    dropZone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleImagePreview(files[0]);
        }
    }, false);
    
    // Click para abrir selector de archivos
    dropZone.addEventListener('click', () => fileInput.click());
    
    // Cambio de archivo
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            handleImagePreview(this.files[0]);
        }
    });
    
    // Función para manejar preview de imagen
    function handleImagePreview(file) {
        if (!file.type.startsWith('image/')) {
            Swal.fire('Error', 'Por favor selecciona un archivo de imagen válido', 'error');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#img-preview-modal').attr('src', e.target.result);
            $('#preview-product-image').attr('src', e.target.result).css('opacity', '1');
            $('#image-preview-container').show();
            $('#drop-zone').hide();
        };
        reader.readAsDataURL(file);
    }
    
    // Remover imagen
    $('#btn-remove-image').on('click', function(e) {
        e.stopPropagation();
        fileInput.value = '';
        $('#image-preview-container').hide();
        $('#drop-zone').show();
        $('#preview-product-image').attr('src', "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Crect width='400' height='300' fill='%23e2e8f0'/%3E%3Ctext x='50%25' y='50%25' font-family='Arial,sans-serif' font-size='16' fill='%2364748b' text-anchor='middle' dy='.3em'%3ESin Imagen%3C/text%3E%3C/svg%3E").css('opacity', '0.7');
    });
    
    // Vista previa en tiempo real
    $('#titulo').on('input', function() {
        const value = $(this).val() || 'Título del Producto';
        $('#preview-titulo').text(value);
    });
    
    $('#precio').on('input', function() {
        const value = parseFloat($(this).val()) || 0;
        $('#preview-precio').text('$' + value.toFixed(2));
    });
    
    $('#categoria').on('change', function() {
        const value = $(this).val() || 'Categoría';
        $('#preview-categoria').text(value);
    });
    
    $('#estado').on('change', function() {
        const value = $(this).find('option:selected').text();
        $('#preview-estado').text(value);
    });

    // Guardar configuración del banner
    $('#form-configuracion').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        formData.append('_token', '{{ csrf_token() }}');
        
        $.ajax({
            url: '{{ route("configuracion.publicidad-productos.guardar-config") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Éxito!', response.message, 'success');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Error al guardar la configuración', 'error');
            }
        });
    });

    // Agregar categoría
    let catIndex = {{ count($categorias) }};
    $('#btn-agregar-categoria').on('click', function() {
        let html = `
            <div class="row categoria-row mb-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="categorias[${catIndex}][nombre]" placeholder="Nombre">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="categorias[${catIndex}][icono]" placeholder="Icono">
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="cat_activo_${catIndex}" name="categorias[${catIndex}][activo]" checked>
                        <label class="custom-control-label" for="cat_activo_${catIndex}">Activo</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm btn-eliminar-categoria">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#categorias-container').append(html);
        catIndex++;
    });

    // Eliminar categoría
    $(document).on('click', '.btn-eliminar-categoria', function() {
        $(this).closest('.categoria-row').remove();
    });

    // Guardar categorías
    $('#btn-guardar-categorias').on('click', function() {
        let categorias = [];
        $('.categoria-row').each(function(index) {
            categorias.push({
                nombre: $(this).find('input[name*="[nombre]"]').val(),
                icono: $(this).find('input[name*="[icono]"]').val(),
                activo: $(this).find('input[type="checkbox"]').is(':checked')
            });
        });

        $.ajax({
            url: '{{ route("configuracion.publicidad-productos.guardar-categorias") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                categorias: categorias
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Éxito!', response.message, 'success');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Error al guardar las categorías', 'error');
            }
        });
    });

    // Nuevo producto
    $('#btn-nuevo-producto').on('click', function() {
        $('#modal-titulo').text('Agregar Producto');
        $('#form-producto')[0].reset();
        $('#producto_id').val('');
        $('#image-preview-container').hide();
        $('#drop-zone').show();
        $('#preview-product-image').attr('src', "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Crect width='400' height='300' fill='%23e2e8f0'/%3E%3Ctext x='50%25' y='50%25' font-family='Arial,sans-serif' font-size='16' fill='%2364748b' text-anchor='middle' dy='.3em'%3ESin Imagen%3C/text%3E%3C/svg%3E").css('opacity', '0.7');
        $('#preview-titulo').text('Título del Producto');
        $('#preview-precio').text('$0.00');
        $('#preview-categoria').text('Categoría');
        $('#preview-estado').text('Nuevo');
        $('#modal-producto').modal('show');
    });

    // Editar producto
    $(document).on('click', '.btn-editar', function() {
        let producto = $(this).data('producto');
        $('#modal-titulo').text('Editar Producto');
        $('#producto_id').val(producto.id);
        $('#titulo').val(producto.titulo).trigger('input');
        $('#descripcion').val(producto.descripcion);
        $('#categoria').val(producto.categoria).trigger('change');
        $('#precio').val(producto.precio).trigger('input');
        $('#estado').val(producto.estado).trigger('change');
        $('#orden').val(producto.orden);
        $('#url_externa').val(producto.url_externa);
        
        if (producto.imagen) {
            const imgUrl = '/media/' + producto.imagen;
            $('#img-preview-modal').attr('src', imgUrl);
            $('#preview-product-image').attr('src', imgUrl).css('opacity', '1');
            $('#image-preview-container').show();
            $('#drop-zone').hide();
        } else {
            $('#image-preview-container').hide();
            $('#drop-zone').show();
            $('#preview-product-image').attr('src', "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Crect width='400' height='300' fill='%23e2e8f0'/%3E%3Ctext x='50%25' y='50%25' font-family='Arial,sans-serif' font-size='16' fill='%2364748b' text-anchor='middle' dy='.3em'%3ESin Imagen%3C/text%3E%3C/svg%3E").css('opacity', '0.7');
        }
        
        $('#modal-producto').modal('show');
    });

    // Guardar producto
    $('#form-producto').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        formData.append('_token', '{{ csrf_token() }}');
        
        let id = $('#producto_id').val();
        let url = id ? 
            '{{ route("configuracion.publicidad-productos.update", ":id") }}'.replace(':id', id) :
            '{{ route("configuracion.publicidad-productos.store") }}';
        
        if (id) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Éxito!', response.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    let errorMsg = Object.values(errors).flat().join('<br>');
                    Swal.fire('Error de validación', errorMsg, 'error');
                } else {
                    Swal.fire('Error', 'Error al guardar el producto', 'error');
                }
            }
        });
    });

    // Eliminar producto
    $(document).on('click', '.btn-eliminar', function() {
        let id = $(this).data('id');
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("configuracion.publicidad-productos.destroy", ":id") }}'.replace(':id', id),
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('¡Eliminado!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Error al eliminar el producto', 'error');
                    }
                });
            }
        });
    });
});
</script>
@stop
