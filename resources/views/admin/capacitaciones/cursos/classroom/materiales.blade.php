<!-- Vista de Materiales del Curso -->
<div class="row">
    <div class="col-md-8">
        <!-- Lista de Materiales -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-folder-open"></i> Materiales del Curso</h3>
                @if($esInstructor)
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" id="btn-subir-material">
                            <i class="fas fa-upload"></i> Subir Material
                        </button>
                    </div>
                @endif
            </div>
            <div class="card-body">
                @forelse($materiales as $material)
                    <div class="material-item mb-3 p-3 border rounded">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                <i class="{{ $material->tipo_icon }} fa-2x text-primary"></i>
                            </div>
                            <div class="col-md-7">
                                <h5 class="mb-1">{{ $material->titulo }}</h5>
                                <p class="text-muted mb-1">{{ $material->descripcion }}</p>
                                <small class="text-muted">
                                    {!! $material->tipo_badge !!} • 
                                    @if($material->archivo_size)
                                        {{ $material->archivo_size_formatted }} • 
                                    @endif
                                    Subido {{ $material->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <div class="col-md-4 text-right">
                                @php
                                    // Obtener la extensión del archivo
                                    $extension = strtolower($material->archivo_extension ?? '');
                                    // Verificar si hay URL de archivo
                                    $archivoUrl = $material->archivo_url ?? ($material->archivo_path ? url('media/' . $material->archivo_path) : '');
                                    if (empty($archivoUrl) && !empty($material->url_externa)) {
                                        $archivoUrl = $material->url_externa;
                                    }
                                @endphp
                                
                                {{-- Botón Ver - SIEMPRE visible --}}
                                <button type="button" class="btn btn-primary btn-sm" 
                                        onclick="verDocumento({{ json_encode($archivoUrl) }}, {{ json_encode($material->titulo) }}, {{ json_encode($material->tipo) }}, {{ json_encode($extension) }})">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                
                                {{-- Botón Editar (solo para instructores) --}}
                                @if($esInstructor)
                                    <button type="button" class="btn btn-warning btn-sm btn-editar-material" 
                                            data-material-id="{{ $material->id }}">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                @endif
                                
                                {{-- Botón Eliminar (solo para instructores) --}}
                                @if($esInstructor)
                                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarMaterial({{ $material->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay materiales disponibles</h5>
                        <p class="text-muted">
                            @if($esInstructor)
                                Sube el primer material para comenzar.
                            @else
                                El instructor aún no ha subido materiales.
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Estadísticas de Materiales -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Estadísticas</h3>
            </div>
            <div class="card-body">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-file"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Materiales</span>
                        <span class="info-box-number">{{ $materiales->count() }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-video"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Videos</span>
                        <span class="info-box-number">{{ $materiales->where('tipo', 'video')->count() }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-file-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Documentos</span>
                        <span class="info-box-number">{{ $materiales->where('tipo', 'documento')->count() }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-danger"><i class="fas fa-image"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Imágenes</span>
                        <span class="info-box-number">{{ $materiales->where('tipo', 'imagen')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tipos de Archivo Permitidos -->
        @if($esInstructor)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Información</h3>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-file-upload"></i> Tipos de archivo permitidos:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-file-pdf text-danger"></i> PDF <small class="text-muted">(único permitido para tipo "Documento")</small></li>
                        <li><i class="fas fa-file-powerpoint text-warning"></i> PowerPoint (PPT, PPTX)</li>
                        <li><i class="fas fa-file-excel text-success"></i> Excel (XLS, XLSX)</li>
                        <li><i class="fas fa-image text-info"></i> Imágenes (JPG, PNG, GIF)</li>
                        <li><i class="fas fa-video text-purple"></i> Videos (MP4, AVI, MOV)</li>
                    </ul>
                    <hr>
                    <small class="text-muted">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Tamaño máximo: 10MB por archivo
                    </small>
                </div>
            </div>
        @endif
    </div>
</div>

@if($esInstructor)
    <!-- Modal para Subir Material -->
    <div class="modal fade" id="subirMaterialModal" tabindex="-1" role="dialog" aria-labelledby="subirMaterialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h4 class="modal-title" id="subirMaterialModalLabel"><i class="fas fa-upload"></i> Subir Nuevo Material</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="subirMaterialForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="titulo">Título del Material <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" required maxlength="200">
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Describe el contenido del material..."></textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo">Tipo de Material <span class="text-danger">*</span></label>
                                    <select class="form-control" id="tipo" name="tipo" required>
                                        <option value="">Seleccionar tipo...</option>
                                        <option value="documento">Documento</option>
                                        <option value="video">Video</option>
                                        <option value="imagen">Imagen</option>
                                        <option value="archivo">Archivo General</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="orden">Orden</label>
                                    <input type="number" class="form-control" id="orden" name="orden" min="0" placeholder="Automático">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Sistema de Calificaciones -->
                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <h6 class="text-primary"><i class="fas fa-star"></i> Configuración de Calificación</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="porcentaje_curso">Porcentaje del Curso (%)</label>
                                    <input type="number" class="form-control" id="porcentaje_curso" name="porcentaje_curso" 
                                           min="0" max="100" step="0.1" value="0" placeholder="0">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Porcentaje que representa este material sobre el curso (0-100%)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nota_minima_aprobacion">Nota Mínima de Aprobación</label>
                                    <input type="number" class="form-control" id="nota_minima_aprobacion" name="nota_minima_aprobacion" 
                                           min="0" max="5" step="0.1" value="3.0" placeholder="3.0">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Nota mínima para aprobar este material (0.0 - 5.0)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Opciones de Subida -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Método de Subida</label>
                                    <div class="nav-tabs-custom">
                                        <ul class="nav nav-tabs">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-toggle="tab" href="#archivo-tab">
                                                    <i class="fas fa-upload"></i> Subir Archivo
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" href="#url-tab">
                                                    <i class="fas fa-link"></i> URL Externa
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="archivo-tab">
                                                <div class="form-group mt-3">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="archivo" name="archivo">
                                                        <label class="custom-file-label" for="archivo">Seleccionar archivo...</label>
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                    <small class="form-text text-muted">Máximo 10MB. Formatos: PDF, PPT, PPTX, XLS, XLSX, JPG, PNG, GIF, MP4, AVI, MOV. <strong>Tipo "Documento": solo PDF.</strong></small>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="url-tab">
                                                <div class="form-group mt-3">
                                                    <label for="url_externa">URL Externa</label>
                                                    <input type="url" class="form-control" id="url_externa" name="url_externa" placeholder="https://ejemplo.com/video">
                                                    <div class="invalid-feedback"></div>
                                                    <small class="form-text text-muted">Para videos de YouTube, Vimeo, Google Drive, etc.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="btn-guardar-material">
                            <i class="fas fa-upload"></i> Subir Material
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Material -->
    <div class="modal fade" id="editarMaterialModal" tabindex="-1" role="dialog" aria-labelledby="editarMaterialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title" id="editarMaterialModalLabel"><i class="fas fa-edit"></i> Editar Material</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editarMaterialForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="edit_material_id" name="material_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="edit_titulo">Título del Material <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_titulo" name="titulo" required maxlength="200">
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_descripcion">Descripción</label>
                                    <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3" placeholder="Describe el contenido del material..."></textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_tipo">Tipo de Material <span class="text-danger">*</span></label>
                                    <select class="form-control" id="edit_tipo" name="tipo" required>
                                        <option value="">Seleccionar tipo...</option>
                                        <option value="documento">Documento</option>
                                        <option value="video">Video</option>
                                        <option value="imagen">Imagen</option>
                                        <option value="archivo">Archivo General</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_orden">Orden</label>
                                    <input type="number" class="form-control" id="edit_orden" name="orden" min="0">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Vincular a Material Prerrequisito -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label><i class="fas fa-link text-info"></i> Vincular como Prerrequisito</label>
                                    <select class="form-control" id="edit_prerequisite_id" name="prerequisite_id">
                                        <option value="">Sin prerrequisito (material independiente)</option>
                                        @foreach($materiales as $mat)
                                            <option value="{{ $mat->id }}" data-material-id="{{ $mat->id }}">
                                                {{ $mat->titulo }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Si seleccionas un material, los estudiantes deberán verlo primero antes de acceder a este.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Sistema de Calificaciones -->
                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <h6 class="text-primary"><i class="fas fa-star"></i> Configuración de Calificación</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_porcentaje_curso">Porcentaje del Curso (%)</label>
                                    <input type="number" class="form-control" id="edit_porcentaje_curso" name="porcentaje_curso" 
                                           min="0" max="100" step="0.1" value="0" placeholder="0">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Porcentaje que representa este material sobre el curso (0-100%)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_nota_minima_aprobacion">Nota Mínima de Aprobación</label>
                                    <input type="number" class="form-control" id="edit_nota_minima_aprobacion" name="nota_minima_aprobacion" 
                                           min="0" max="5" step="0.1" value="3.0" placeholder="3.0">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Nota mínima para aprobar este material (0.0 - 5.0)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Opciones de Subida -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Cambiar Archivo o URL (opcional)</label>
                                    <div class="nav-tabs-custom">
                                        <ul class="nav nav-tabs">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-toggle="tab" href="#edit-archivo-tab">
                                                    <i class="fas fa-upload"></i> Subir Archivo
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" href="#edit-url-tab">
                                                    <i class="fas fa-link"></i> URL Externa
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="edit-archivo-tab">
                                                <div class="form-group mt-3">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="edit_archivo" name="archivo">
                                                        <label class="custom-file-label" for="edit_archivo">Seleccionar nuevo archivo...</label>
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                    <small class="form-text text-muted">Deja vacío para mantener el archivo actual. Máximo 10MB.</small>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="edit-url-tab">
                                                <div class="form-group mt-3">
                                                    <label for="edit_url_externa">URL Externa</label>
                                                    <input type="url" class="form-control" id="edit_url_externa" name="url_externa" placeholder="https://ejemplo.com/video">
                                                    <div class="invalid-feedback"></div>
                                                    <small class="form-text text-muted">Para videos de YouTube, Vimeo, Google Drive, etc.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del archivo actual -->
                        <div class="row" id="edit-archivo-actual-info">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-file"></i> <strong>Archivo actual:</strong> <span id="edit-archivo-actual-nombre">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning" id="btn-actualizar-material">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Estilos CSS adicionales para el visor -->
<style>
    .material-item {
        transition: all 0.3s ease;
        border: 1px solid #e3e6f0 !important;
    }
    
    .material-item:hover {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        transform: translateY(-2px);
    }
    
    .image-controls .btn {
        opacity: 0.8;
        transition: opacity 0.3s ease;
    }
    
    .image-controls .btn:hover {
        opacity: 1;
    }
    
    .pdf-controls .btn {
        opacity: 0.9;
        transition: opacity 0.3s ease;
    }
    
    .pdf-controls .btn:hover {
        opacity: 1;
    }
    
    #document-image {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }
    
    .office-viewer-container .nav-tabs {
        border-bottom: 1px solid #dee2e6;
    }
    
    .office-viewer-container .nav-tabs .nav-link {
        border: 1px solid transparent;
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
    }
    
    .office-viewer-container .nav-tabs .nav-link.active {
        color: #495057;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
    }
    
    .video-container {
        position: relative;
        background: #000;
    }
    
    .video-container video {
        outline: none;
    }
    
    .viewer-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 999;
    }
</style>

<!-- Modal para Visualizar Documentos -->
<div class="modal fade" id="verDocumentoModal" tabindex="-1" role="dialog" aria-labelledby="verDocumentoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="verDocumentoModalLabel">
                    <i class="fas fa-file-alt"></i> <span id="documento-titulo">Documento</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <div id="documento-viewer" class="w-100 h-100">
                    <!-- El contenido se cargará dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <a id="btn-descargar-documento" href="#" download class="btn btn-info">
                    <i class="fas fa-download"></i> Descargar
                </a>
                <a id="btn-abrir-nueva-ventana" href="#" target="_blank" class="btn btn-primary">
                    <i class="fas fa-external-link-alt"></i> Abrir en Nueva Ventana
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        @if($esInstructor)
            // Inicializar custom file input (deshabilitado - no es necesario)
            // bsCustomFileInput.init();

            // Abrir modal para subir material
            $('#btn-subir-material').click(function() {
                $('#subirMaterialForm')[0].reset();
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                $('#subirMaterialModal').modal('show');
            });

            // Manejar eventos del modal para accesibilidad
            $('#subirMaterialModal').on('show.bs.modal', function () {
                // Solución específica para AdminLTE: remover aria-hidden de elementos problemáticos
                $('body').removeClass('modal-open');
                $('.wrapper, .content-wrapper, .main-sidebar, .main-header').removeAttr('aria-hidden');
                
                // Asegurar que el modal esté en el nivel superior
                $(this).appendTo('body');
            });
            
            $('#subirMaterialModal').on('shown.bs.modal', function () {
                // Enfocar el primer campo
                setTimeout(() => {
                    $('#titulo').focus();
                }, 100);
            });

            $('#subirMaterialModal').on('hidden.bs.modal', function () {
                // Limpiar formulario cuando se cierre el modal
                $('#subirMaterialForm')[0].reset();
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                
                // Restaurar clases de Bootstrap
                $('body').addClass('modal-open');
            });

            // Envío del formulario de subir material
            $('#subirMaterialForm').submit(function(e) {
                e.preventDefault();
                
                // Limpiar errores previos
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                
                // Deshabilitar botón de envío
                $('#btn-guardar-material').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Subiendo...');
                
                // Crear FormData para manejar archivos
                const formData = new FormData(this);
                
                // Debug: mostrar datos del formulario
                console.log('Enviando datos:', {
                    titulo: formData.get('titulo'),
                    descripcion: formData.get('descripcion'),
                    tipo: formData.get('tipo'),
                    archivo: formData.get('archivo'),
                    url_externa: formData.get('url_externa'),
                    orden: formData.get('orden'),
                    _token: formData.get('_token')
                });
                
                // Verificar que tenemos los datos mínimos requeridos
                if (!formData.get('titulo') || !formData.get('tipo')) {
                    Swal.fire('Error', 'Por favor completa los campos requeridos (Título y Tipo)', 'error');
                    $('#btn-guardar-material').prop('disabled', false).html('<i class="fas fa-upload"></i> Subir Material');
                    return;
                }
                
                // Verificar que tenemos archivo o URL externa
                const archivo = formData.get('archivo');
                const urlExterna = formData.get('url_externa');
                
                if ((!archivo || archivo.size === 0) && (!urlExterna || urlExterna.trim() === '')) {
                    Swal.fire('Error', 'Debes seleccionar un archivo o proporcionar una URL externa', 'error');
                    $('#btn-guardar-material').prop('disabled', false).html('<i class="fas fa-upload"></i> Subir Material');
                    return;
                }
                
                // Si url_externa está vacía, eliminarla del FormData
                if (!urlExterna || urlExterna.trim() === '') {
                    formData.delete('url_externa');
                }
                
                $.ajax({
                    url: '{{ route("capacitaciones.cursos.classroom.materiales.store", $curso->id) }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                $('#subirMaterialModal').modal('hide');
                                // Recargar solo la lista de materiales para evitar conflictos de JavaScript
                                setTimeout(() => {
                                    if (typeof loadTabContent === 'function') {
                                        loadTabContent('materiales', '#materiales');
                                    } else {
                                        // Fallback: recargar la página
                                        window.location.reload();
                                    }
                                }, 500); // Pequeño delay para asegurar que el modal se cierre completamente
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        console.log('Error response:', xhr);
                        console.log('Status:', xhr.status);
                        console.log('Response text:', xhr.responseText);
                        
                        if (xhr.status === 422) {
                            // Errores de validación
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;
                                $.each(errors, function(field, messages) {
                                    const input = $('[name="' + field + '"]');
                                    input.addClass('is-invalid');
                                    input.siblings('.invalid-feedback').text(messages[0]);
                                });
                                
                                Swal.fire('Error de Validación', 'Por favor, corrige los errores en el formulario', 'error');
                            } else {
                                Swal.fire('Error de Validación', 'Error 422: ' + (xhr.responseJSON?.message || xhr.responseText), 'error');
                            }
                        } else {
                            Swal.fire('Error', 'Ocurrió un error al subir el material (Status: ' + xhr.status + ')', 'error');
                        }
                    },
                    complete: function() {
                        // Rehabilitar botón de envío
                        $('#btn-guardar-material').prop('disabled', false).html('<i class="fas fa-upload"></i> Subir Material');
                    }
                });
            });

            // Manejar clic en botón editar material
            $(document).on('click', '.btn-editar-material', function() {
                const material = $(this).data('material');
                const materialId = $(this).data('material-id');
                
                // Llenar el formulario con los datos actuales
                $('#edit_material_id').val(materialId);
                $('#edit_titulo').val(material.titulo || '');
                $('#edit_descripcion').val(material.descripcion || '');
                $('#edit_tipo').val(material.tipo || '');
                $('#edit_orden').val(material.orden || 0);
                $('#edit_url_externa').val(material.url_externa || '');
                
                // Seleccionar prerrequisito si existe
                $('#edit_prerequisite_id').val(material.prerequisite_id || '');
                
                // Ocultar la opción del material actual en el select de prerrequisitos
                $('#edit_prerequisite_id option').show();
                $('#edit_prerequisite_id option[data-material-id="' + materialId + '"]').hide();
                
                // Mostrar información del archivo actual
                if (material.archivo_nombre) {
                    $('#edit-archivo-actual-nombre').text(material.archivo_nombre);
                    $('#edit-archivo-actual-info').show();
                } else if (material.url_externa) {
                    $('#edit-archivo-actual-nombre').text('URL: ' + material.url_externa);
                    $('#edit-archivo-actual-info').show();
                } else {
                    $('#edit-archivo-actual-info').hide();
                }
                
                // Limpiar el campo de archivo
                $('#edit_archivo').val('');
                $('.custom-file-label[for="edit_archivo"]').text('Seleccionar nuevo archivo...');
                
                // Limpiar errores previos
                $('#editarMaterialForm .form-control').removeClass('is-invalid');
                $('#editarMaterialForm .invalid-feedback').text('');
                
                // Mostrar modal
                $('#editarMaterialModal').modal('show');
            });

            // Manejar envío del formulario de editar material
            $('#editarMaterialForm').submit(function(e) {
                e.preventDefault();
                
                const materialId = $('#edit_material_id').val();
                
                // Limpiar errores previos
                $('#editarMaterialForm .form-control').removeClass('is-invalid');
                $('#editarMaterialForm .invalid-feedback').text('');
                
                // Deshabilitar botón de envío
                $('#btn-actualizar-material').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
                
                // Crear FormData para manejar archivos
                const formData = new FormData(this);
                formData.append('_method', 'PUT');
                
                // Agregar prerequisite_id
                const prerequisiteId = $('#edit_prerequisite_id').val();
                if (prerequisiteId) {
                    formData.set('prerequisite_id', prerequisiteId);
                } else {
                    formData.set('prerequisite_id', '');
                }
                
                $.ajax({
                    url: `/capacitaciones/cursos/{{ $curso->id }}/classroom/materiales/${materialId}`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                $('#editarMaterialModal').modal('hide');
                                // Recargar la pestaña de materiales
                                if (typeof loadTabContent === 'function') {
                                    loadTabContent('materiales', '#materiales');
                                } else {
                                    window.location.reload();
                                }
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;
                                $.each(errors, function(field, messages) {
                                    const input = $('#editarMaterialForm [name="' + field + '"]');
                                    input.addClass('is-invalid');
                                    input.siblings('.invalid-feedback').text(messages[0]);
                                });
                                Swal.fire('Error de Validación', 'Por favor, corrige los errores en el formulario', 'error');
                            } else {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Error de validación', 'error');
                            }
                        } else {
                            Swal.fire('Error', 'Ocurrió un error al actualizar el material', 'error');
                        }
                    },
                    complete: function() {
                        $('#btn-actualizar-material').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambios');
                    }
                });
            });

            // Manejar cambio de archivo en edición
            $('#edit_archivo').change(function() {
                const fileName = $(this).val().split('\\').pop();
                $(this).siblings('.custom-file-label').text(fileName || 'Seleccionar nuevo archivo...');
            });
        @endif
    });

    // Función de prueba simple
    function testModal() {
        $('#documento-titulo').text('Prueba');
        $('#documento-viewer').html('<div class="p-5 text-center"><h3>Modal funcionando correctamente</h3></div>');
        $('#verDocumentoModal').modal('show');
    }

    // Función para ver documento en modal (versión mejorada)
    function verDocumento(url, titulo, tipo, extension) {
        console.log('verDocumento llamada con:', {url, titulo, tipo, extension});
        
        // Función auxiliar para escapar HTML
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Escapar variables para uso seguro en HTML
        const urlEscaped = escapeHtml(url);
        const tituloEscaped = escapeHtml(titulo);
        
        // Validar que haya URL
        if (!url || url.trim() === '') {
            Swal.fire({
                icon: 'error',
                title: 'Archivo no disponible',
                text: 'Este material no tiene un archivo asociado.'
            });
            return;
        }
        
        // Actualizar título del modal
        $('#documento-titulo').text(titulo);
        
        // Actualizar botones
        $('#btn-descargar-documento').attr('href', url);
        $('#btn-abrir-nueva-ventana').attr('href', url);
        
        // Mostrar modal inmediatamente
        $('#verDocumentoModal').modal('show');
        
        // Mostrar indicador de carga
        $('#documento-viewer').html(`
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted">Preparando visualización...</p>
                </div>
            </div>
        `);
        
        // Preparar contenido basándose en el tipo y extensión
        let contenido = '';
        const ext = extension.toLowerCase();
        
        console.log('Procesando archivo:', {tipo, extension: ext});
        
        // Lógica simplificada para determinar el tipo de visualización
        if (tipo === 'imagen' || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(ext)) {
            // Mostrar imagen con controles mejorados
            contenido = `
                <div class="d-flex flex-column justify-content-center align-items-center h-100 bg-dark position-relative">
                    <div class="image-controls position-absolute" style="top: 10px; right: 10px; z-index: 1000;">
                        <button class="btn btn-sm btn-light" onclick="zoomImage('in')" title="Acercar">
                            <i class="fas fa-search-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-light ml-1" onclick="zoomImage('out')" title="Alejar">
                            <i class="fas fa-search-minus"></i>
                        </button>
                        <button class="btn btn-sm btn-light ml-1" onclick="resetZoom()" title="Tamaño original">
                            <i class="fas fa-expand-arrows-alt"></i>
                        </button>
                    </div>
                    <img id="document-image" src="${urlEscaped}" alt="${tituloEscaped}" class="img-fluid" 
                         style="max-height: 90%; max-width: 90%; object-fit: contain; cursor: grab; transition: transform 0.2s;" 
                         draggable="false">
                </div>
            `;
        } else if (extension === 'pdf' || url.toLowerCase().includes('.pdf')) {
            // Mostrar PDF con visor embebido mejorado
            contenido = `
                <div class="pdf-viewer-container h-100 position-relative">
                    <div class="pdf-controls position-absolute" style="top: 10px; left: 10px; z-index: 1000;">
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-primary" onclick="pdfAction('print')" title="Imprimir">
                                <i class="fas fa-print"></i>
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="pdfAction('fullscreen')" title="Pantalla completa">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                    <iframe id="pdf-viewer" src="${urlEscaped}#toolbar=1&navpanes=1&scrollbar=1" 
                            width="100%" 
                            height="100%" 
                            frameborder="0"
                            style="border: none;">
                        <p>Tu navegador no soporta la visualización de PDFs. 
                           <a href="${urlEscaped}" target="_blank">Haz clic aquí para descargar el PDF</a>
                        </p>
                    </iframe>
                </div>
            `;
        } else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(extension.toLowerCase())) {
            // Para documentos de Office, usar múltiples opciones de visualización
            
            // Determinar si la URL ya es absoluta o es relativa
            let fullUrl = url;
            if (!url.startsWith('http://') && !url.startsWith('https://')) {
                // Si es URL relativa, agregar el origin
                fullUrl = window.location.origin + (url.startsWith('/') ? url : '/' + url);
            }
            
            const encodedFullUrl = encodeURIComponent(fullUrl);
            
            console.log('URL para Office viewers:', {original: url, fullUrl, encoded: encodedFullUrl});
            
            contenido = `
                <div class="office-viewer-container h-100">
                    <div class="viewer-tabs mb-2">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#google-viewer" role="tab">Google Viewer</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#office-viewer" role="tab">Office Online</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content h-100">
                        <div class="tab-pane active h-100" id="google-viewer" role="tabpanel">
                            <iframe src="https://docs.google.com/gview?url=${encodedFullUrl}&embedded=true" 
                                    width="100%" 
                                    height="90%" 
                                    frameborder="0">
                                <p>No se puede mostrar el documento con Google Viewer.</p>
                            </iframe>
                        </div>
                        <div class="tab-pane h-100" id="office-viewer" role="tabpanel">
                            <iframe src="https://view.officeapps.live.com/op/embed.aspx?src=${encodedFullUrl}" 
                                    width="100%" 
                                    height="90%" 
                                    frameborder="0">
                                <p>No se puede mostrar el documento con Office Online.</p>
                            </iframe>
                        </div>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">Si no se puede visualizar, 
                            <a href="${urlEscaped}" target="_blank" class="text-primary">haz clic aquí para descargarlo</a>
                        </small>
                    </div>
                </div>
            `;
        } else if (tipo === 'video' || ['mp4', 'avi', 'mov', 'webm', 'ogg', 'mkv'].includes(ext)) {
            // Mostrar video con controles nativos
            contenido = `
                <div class="d-flex justify-content-center align-items-center h-100 bg-dark">
                    <video controls class="w-100 h-100" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                        <source src="${urlEscaped}" type="video/${escapeHtml(extension)}">
                        Tu navegador no soporta la reproducción de videos.
                        <a href="${urlEscaped}" target="_blank" class="text-white">Descargar video</a>
                    </video>
                </div>
            `;
        } else if (['txt', 'csv', 'log', 'md', 'json', 'xml', 'html', 'css', 'js'].includes(ext)) {
            // Mostrar archivos de texto
            contenido = `
                <div class="h-100 p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Vista previa del archivo de texto</h6>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary" onclick="cambiarTamanoTexto('smaller')" title="Texto más pequeño">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button class="btn btn-outline-secondary" onclick="cambiarTamanoTexto('larger')" title="Texto más grande">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div id="text-content" style="height: calc(100% - 60px); overflow-y: auto; font-family: 'Courier New', monospace; font-size: 14px; line-height: 1.5; white-space: pre-wrap; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                        Cargando contenido del archivo...
                    </div>
                </div>
            `;
            
            // Cargar contenido del archivo de texto
            setTimeout(() => {
                fetch(url)
                    .then(response => response.text())
                    .then(text => {
                        $('#text-content').text(text);
                    })
                    .catch(error => {
                        $('#text-content').html('<div class="text-center text-muted"><i class="fas fa-exclamation-triangle"></i><br>No se pudo cargar el contenido del archivo</div>');
                    });
            }, 100);
        } else {
            // Para otros tipos, mostrar mensaje
            contenido = `
                <div class="d-flex flex-column justify-content-center align-items-center h-100 bg-light">
                    <i class="fas fa-file-alt fa-5x text-muted mb-3"></i>
                    <h4>No se puede previsualizar este tipo de archivo</h4>
                    <p class="text-muted">Usa los botones de abajo para descargar o abrir en una nueva ventana</p>
                    <a href="${urlEscaped}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i> Abrir en Nueva Ventana
                    </a>
                </div>
            `;
        }
        
        // Cargar contenido después de un pequeño delay
        setTimeout(() => {
            try {
                console.log('Cargando contenido del visor');
                
                // Insertar contenido en el visor
                $('#documento-viewer').html(contenido);
                
                // Resetear zoom si es una imagen
                if (tipo === 'imagen' || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(ext)) {
                    if (window.materialesViewer) {
                        window.materialesViewer.currentZoom = 1;
                    }
                }
                
            } catch (error) {
                console.error('Error al cargar documento:', error);
                $('#documento-viewer').html(`
                    <div class="d-flex flex-column justify-content-center align-items-center h-100 bg-light">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <h5>Error al cargar</h5>
                        <p class="text-muted">No se pudo cargar la vista del archivo.</p>
                        <a href="${urlEscaped}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-external-link-alt"></i> Abrir en Nueva Ventana
                        </a>
                    </div>
                `);
            }
        }, 300);
    }

    // Función para eliminar material
    function eliminarMaterial(id) {
        Swal.fire({
            title: '¿Eliminar material?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ route('capacitaciones.cursos.classroom.materiales.destroy', ['curso' => $curso->id, 'material' => '__ID__']) }}`.replace('__ID__', id),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                // Recargar la lista de materiales con delay
                                setTimeout(() => {
                                    if (typeof loadTabContent === 'function') {
                                        loadTabContent('materiales', '#materiales');
                                    } else {
                                        // Fallback: recargar la página
                                        window.location.reload();
                                    }
                                }, 500);
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Ocurrió un error al eliminar el material', 'error');
                    }
                });
            }
        });
    }

    // Variables para zoom de imagen (usando namespace global para evitar conflictos)
    window.materialesViewer = window.materialesViewer || {};
    window.materialesViewer.currentZoom = window.materialesViewer.currentZoom || 1;
    window.materialesViewer.isDragging = window.materialesViewer.isDragging || false;
    window.materialesViewer.startX = window.materialesViewer.startX || 0;
    window.materialesViewer.startY = window.materialesViewer.startY || 0;
    window.materialesViewer.scrollLeft = window.materialesViewer.scrollLeft || 0;
    window.materialesViewer.scrollTop = window.materialesViewer.scrollTop || 0;

    // Funciones para zoom de imagen
    function zoomImage(action) {
        const img = document.getElementById('document-image');
        if (!img) return;

        if (action === 'in') {
            window.materialesViewer.currentZoom = Math.min(window.materialesViewer.currentZoom * 1.2, 5);
        } else if (action === 'out') {
            window.materialesViewer.currentZoom = Math.max(window.materialesViewer.currentZoom / 1.2, 0.1);
        }

        img.style.transform = `scale(${window.materialesViewer.currentZoom})`;
    }

    function resetZoom() {
        window.materialesViewer.currentZoom = 1;
        const img = document.getElementById('document-image');
        if (img) {
            img.style.transform = 'scale(1)';
        }
    }

    // Funciones para controles de PDF
    function pdfAction(action) {
        const iframe = document.getElementById('pdf-viewer');
        if (!iframe) return;

        switch(action) {
            case 'print':
                iframe.contentWindow.print();
                break;
            case 'fullscreen':
                if (iframe.requestFullscreen) {
                    iframe.requestFullscreen();
                } else if (iframe.webkitRequestFullscreen) {
                    iframe.webkitRequestFullscreen();
                } else if (iframe.msRequestFullscreen) {
                    iframe.msRequestFullscreen();
                }
                break;
        }
    }

    // Agregar funcionalidad de arrastrar para imágenes con zoom
    $(document).on('mousedown', '#document-image', function(e) {
        if (window.materialesViewer.currentZoom > 1) {
            window.materialesViewer.isDragging = true;
            const container = $(this).parent();
            window.materialesViewer.startX = e.pageX - container.offset().left;
            window.materialesViewer.startY = e.pageY - container.offset().top;
            window.materialesViewer.scrollLeft = container.scrollLeft();
            window.materialesViewer.scrollTop = container.scrollTop();
            $(this).css('cursor', 'grabbing');
        }
    });

    $(document).on('mousemove', function(e) {
        if (!window.materialesViewer.isDragging) return;
        e.preventDefault();
        const img = $('#document-image');
        const container = img.parent();
        const x = e.pageX - container.offset().left;
        const y = e.pageY - container.offset().top;
        const walkX = (x - window.materialesViewer.startX) * 2;
        const walkY = (y - window.materialesViewer.startY) * 2;
        container.scrollLeft(window.materialesViewer.scrollLeft - walkX);
        container.scrollTop(window.materialesViewer.scrollTop - walkY);
    });

    $(document).on('mouseup', function() {
        window.materialesViewer.isDragging = false;
        $('#document-image').css('cursor', 'grab');
    });

    // Zoom con rueda del mouse para imágenes
    $(document).on('wheel', '#document-image', function(e) {
        e.preventDefault();
        if (e.originalEvent.deltaY < 0) {
            zoomImage('in');
        } else {
            zoomImage('out');
        }
    });

    // Función para cambiar tamaño de texto en archivos de texto
    function cambiarTamanoTexto(accion) {
        const textContent = document.getElementById('text-content');
        if (!textContent) return;
        
        const currentSize = parseInt(window.getComputedStyle(textContent).fontSize);
        let newSize = currentSize;
        
        if (accion === 'larger') {
            newSize = Math.min(currentSize + 2, 24); // Máximo 24px
        } else if (accion === 'smaller') {
            newSize = Math.max(currentSize - 2, 10); // Mínimo 10px
        }
        
        textContent.style.fontSize = newSize + 'px';
    }
</script>
