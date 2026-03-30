<!-- Vista de Foros del Curso -->
<div class="row">
    <div class="col-md-8">
        <!-- Lista de Posts del Foro -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-comments"></i> Foro de Discusión</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" id="btn-nuevo-post">
                        <i class="fas fa-plus"></i> Nuevo Post
                    </button>
                </div>
            </div>
            <div class="card-body">
                @forelse($foros as $post)
                    <div class="post mb-4 p-3 border rounded {{ $post->es_anuncio ? 'border-warning bg-light' : '' }}">
                        <div class="user-block mb-3">
                            <i class="fas fa-user-circle fa-2x text-secondary" style="margin-right: 10px;"></i>
                            <span class="username">
                                <a href="#">{{ $post->usuario->full_name }}</a>
                                @if($post->es_anuncio)
                                    <span class="badge badge-warning ml-1">Anuncio</span>
                                @endif
                                @if($post->es_fijado)
                                    <span class="badge badge-info ml-1">Fijado</span>
                                @endif
                            </span>
                            <span class="description">{{ $post->created_at->diffForHumans() }}</span>
                        </div>
                        
                        <h5 class="mb-2">{{ $post->titulo }}</h5>
                        <div class="post-content mb-3">
                            {!! nl2br(e($post->contenido)) !!}
                        </div>
                        
                        <div class="post-actions d-flex justify-content-between align-items-center">
                            <div>
                                <button class="btn btn-sm btn-outline-primary btn-responder" data-post-id="{{ $post->id }}">
                                    <i class="fas fa-reply"></i> Responder
                                </button>
                                <span class="text-muted ml-2">
                                    <i class="fas fa-comments"></i> {{ $post->respuestas->count() }} respuestas
                                </span>
                            </div>
                            
                            @if($esInstructor || $post->usuario_id === auth()->id())
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#"><i class="fas fa-edit"></i> Editar</a>
                                        <a class="dropdown-item text-danger" href="#"><i class="fas fa-trash"></i> Eliminar</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Formulario de respuesta (oculto inicialmente) -->
                        <div class="respuesta-form mt-3" id="respuesta-form-{{ $post->id }}" style="display: none;">
                            <form class="form-respuesta" data-post-id="{{ $post->id }}">
                                @csrf
                                <div class="form-group">
                                    <textarea class="form-control" name="contenido" rows="3" placeholder="Escribe tu respuesta..."></textarea>
                                </div>
                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-paper-plane"></i> Enviar Respuesta
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm btn-cancelar-respuesta">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Respuestas existentes -->
                        @if($post->respuestas->count() > 0)
                            <div class="respuestas mt-3 pl-4 border-left">
                                @foreach($post->respuestas as $respuesta)
                                    <div class="respuesta mb-3 p-2 bg-light rounded">
                                        <div class="user-block mb-2">
                                            <i class="fas fa-user-circle text-secondary" style="font-size: 30px; margin-right: 10px;"></i>
                                            <span class="username">
                                                <a href="#">{{ $respuesta->usuario->full_name }}</a>
                                            </span>
                                            <span class="description">{{ $respuesta->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="respuesta-content">
                                            {!! nl2br(e($respuesta->contenido)) !!}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay discusiones aún</h5>
                        <p class="text-muted">Sé el primero en iniciar una conversación.</p>
                        <button type="button" class="btn btn-primary" id="btn-primer-post">
                            <i class="fas fa-plus"></i> Crear Primer Post
                        </button>
                    </div>
                @endforelse
                
                <!-- Paginación -->
                @if($foros->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $foros->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Estadísticas del Foro -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Estadísticas del Foro</h3>
            </div>
            <div class="card-body">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-comments"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Posts</span>
                        <span class="info-box-number">{{ $foros->total() }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-bullhorn"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Anuncios</span>
                        <span class="info-box-number">{{ $foros->where('es_anuncio', true)->count() }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-thumbtack"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Posts Fijados</span>
                        <span class="info-box-number">{{ $foros->where('es_fijado', true)->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reglas del Foro -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-gavel"></i> Reglas del Foro</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> 
                        Mantén un tono respetuoso y profesional
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> 
                        Busca antes de crear un nuevo tema
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> 
                        Usa títulos descriptivos para tus posts
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> 
                        Evita el spam y contenido irrelevante
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Nuevo Post -->
<div class="modal fade" id="modal-nuevo-post" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-plus"></i> Nuevo Post</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-nuevo-post">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="titulo">Título del Post</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contenido">Contenido</label>
                        <textarea class="form-control" id="contenido" name="contenido" rows="6" required></textarea>
                    </div>
                    
                    @if($esInstructor)
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="es_anuncio" name="es_anuncio" value="1">
                                <label class="custom-control-label" for="es_anuncio">Marcar como anuncio</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="es_fijado" name="es_fijado" value="1">
                                <label class="custom-control-label" for="es_fijado">Fijar post</label>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Publicar Post
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Abrir modal para nuevo post
    $('#btn-nuevo-post, #btn-primer-post').click(function() {
        $('#modal-nuevo-post').modal('show');
    });
    
    // Enviar nuevo post
    $('#form-nuevo-post').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Publicando...');
        
        $.ajax({
            url: '{{ route("capacitaciones.cursos.classroom.foros.store", $curso->id) }}',
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#modal-nuevo-post').modal('hide');
                    form[0].reset();
                    Swal.fire('¡Éxito!', response.message, 'success').then(() => {
                        loadTabContent('foros', '#foros');
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error al crear el post';
                Swal.fire('Error', message, 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Mostrar formulario de respuesta
    $('.btn-responder').click(function() {
        const postId = $(this).data('post-id');
        const form = $('#respuesta-form-' + postId);
        
        // Ocultar otros formularios de respuesta
        $('.respuesta-form').hide();
        
        // Mostrar este formulario
        form.show();
        form.find('textarea').focus();
    });
    
    // Cancelar respuesta
    $('.btn-cancelar-respuesta').click(function() {
        $(this).closest('.respuesta-form').hide();
    });
    
    // Enviar respuesta
    $('.form-respuesta').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const postId = form.data('post-id');
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');
        
        $.ajax({
            url: `/capacitaciones/cursos/{{ $curso->id }}/classroom/foros/${postId}/responder`,
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    form[0].reset();
                    form.closest('.respuesta-form').hide();
                    Swal.fire('¡Éxito!', response.message, 'success').then(() => {
                        loadTabContent('foros', '#foros');
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error al enviar la respuesta';
                Swal.fire('Error', message, 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
