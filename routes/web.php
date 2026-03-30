<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserLoginController;
use App\Http\Controllers\UserOperationController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\CursoClassroomController;
use App\Http\Controllers\AcademicoController;
use App\Http\Controllers\ControlPedagogicoController;
use App\Http\Controllers\AsignacionCursoController;
use App\Http\Controllers\ServicioAreaController;
use App\Http\Controllers\VinculacionContratoController;
use App\Http\Controllers\SedeController;
use App\Http\Controllers\PublicidadProductoController;
use App\Http\Controllers\CertificadoEditorController;
use App\Http\Controllers\VerificacionCertificadoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// ── Ruta para servir archivos de storage público (alternativa al symlink en cPanel) ──
Route::get('/media/{path}', function ($path) {
    $disk = Storage::disk('public');
    if (!$disk->exists($path)) {
        abort(404);
    }
    $fullPath = $disk->path($path);
    $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
    $size = filesize($fullPath);

    // Para videos, soportar Range requests (streaming)
    if (str_starts_with($mimeType, 'video/')) {
        $request = request();
        $headers = [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=86400',
        ];

        if ($request->hasHeader('Range')) {
            $range = $request->header('Range');
            preg_match('/bytes=(\d+)-(\d*)/', $range, $matches);
            $start = intval($matches[1]);
            $end = !empty($matches[2]) ? intval($matches[2]) : $size - 1;
            $length = $end - $start + 1;

            $headers['Content-Range'] = "bytes $start-$end/$size";
            $headers['Content-Length'] = $length;

            $stream = fopen($fullPath, 'rb');
            fseek($stream, $start);
            $data = fread($stream, $length);
            fclose($stream);

            return response($data, 206, $headers);
        }

        $headers['Content-Length'] = $size;
        return response()->file($fullPath, $headers);
    }

    // Para otros archivos (imágenes, etc.)
    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*')->name('media.serve');

// ── Rutas públicas de verificación de certificados (sin autenticación) ──
Route::get('/verificar-certificado', [VerificacionCertificadoController::class, 'formulario'])->name('verificar.formulario');
Route::post('/verificar-certificado', [VerificacionCertificadoController::class, 'buscar'])->name('verificar.buscar');
Route::get('/verificar-certificado/{codigo}', [VerificacionCertificadoController::class, 'verificar'])->name('verificar.certificado');

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    $banners = collect();
    try {
        $banners = \App\Models\WelcomeBanner::vigentes()->get();
    } catch (\Exception $e) {
        // La tabla aún no existe en este entorno
    }
    return view('welcome', compact('banners'));
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('chat')->name('chat.')->group(function () {
    Route::get('/buscar-usuarios', [\App\Http\Controllers\ChatController::class, 'buscarUsuarios'])->name('buscar-usuarios');
    Route::post('/enviar', [\App\Http\Controllers\ChatController::class, 'enviarMensaje'])->name('enviar');
    Route::get('/mensajes', [\App\Http\Controllers\ChatController::class, 'obtenerMensajes'])->name('mensajes');
    Route::get('/bandeja', [\App\Http\Controllers\ChatController::class, 'bandeja'])->name('bandeja');
    Route::post('/marcar-leido/{mensaje}', [\App\Http\Controllers\ChatController::class, 'marcarLeido'])->name('marcar-leido');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de usuarios - protegidas por permisos
    Route::post('users/import', [UserController::class, 'import'])->name('users.import')->middleware('check.permission:users.import');
    Route::get('users', [UserController::class, 'index'])->name('users.index')->middleware('check.permission:users.view');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create')->middleware('check.permission:users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store')->middleware('check.permission:users.create');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show')->middleware('check.permission:users.view');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('check.permission:users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('check.permission:users.edit');
    Route::patch('users/{user}', [UserController::class, 'update'])->middleware('check.permission:users.edit');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('check.permission:users.delete');
    
    // Roles solo accesible por Super Admin
    Route::resource('roles', \App\Http\Controllers\RoleController::class)->middleware(\App\Http\Middleware\CheckSuperAdminRole::class);

    // Permisos solo accesible por Super Admin
    Route::prefix('permisos')->name('permisos.')->middleware(\App\Http\Middleware\CheckSuperAdminRole::class)->group(function () {
        Route::get('/', [\App\Http\Controllers\PermissionController::class, 'index'])->name('index');
        Route::post('/update-permissions', [\App\Http\Controllers\PermissionController::class, 'updatePermissions'])->name('update-permissions');
        Route::post('/update-assignable', [\App\Http\Controllers\PermissionController::class, 'updateAssignableRoles'])->name('update-assignable');
    });

    // Rutas de seguimiento de accesos
    Route::prefix('tracking')->name('tracking.')->group(function () {
        Route::get('logins', [UserLoginController::class, 'index'])->name('logins.index');
        Route::get('logins/data', [UserLoginController::class, 'getData'])->name('logins.data');
        Route::get('logins/{userLogin}', [UserLoginController::class, 'show'])->name('logins.show');
        Route::post('logins/resend-verification/{user}', [UserLoginController::class, 'resendVerification'])->name('logins.resend-verification');
        Route::get('stats', [UserLoginController::class, 'getStats'])->name('stats');
        
        // Rutas de operaciones
        Route::get('operations', [UserOperationController::class, 'index'])->name('operations.index');
        Route::get('operations/data', [UserOperationController::class, 'getData'])->name('operations.data');
        Route::get('operations/{operation}', [UserOperationController::class, 'show'])->name('operations.show');
        Route::get('operations-stats', [UserOperationController::class, 'getStats'])->name('operations.stats');
    });

    // Rutas de capacitaciones
    Route::prefix('capacitaciones')->name('capacitaciones.')->group(function () {
        // Rutas de categorías (con permisos individuales)
        Route::get('categorias/data', [CategoriaController::class, 'getData'])->name('categorias.data')->middleware('check.permission:categorias.view');
        Route::get('categorias', [CategoriaController::class, 'index'])->name('categorias.index')->middleware('check.permission:categorias.view');
        Route::get('categorias/create', [CategoriaController::class, 'create'])->name('categorias.create')->middleware('check.permission:categorias.create');
        Route::post('categorias', [CategoriaController::class, 'store'])->name('categorias.store')->middleware('check.permission:categorias.create');
        Route::get('categorias/{categoria}', [CategoriaController::class, 'show'])->name('categorias.show')->middleware('check.permission:categorias.view');
        Route::get('categorias/{categoria}/edit', [CategoriaController::class, 'edit'])->name('categorias.edit')->middleware('check.permission:categorias.edit');
        Route::put('categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update')->middleware('check.permission:categorias.edit');
        Route::patch('categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update.patch')->middleware('check.permission:categorias.edit');
        Route::delete('categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy')->middleware('check.permission:categorias.delete');

        // Rutas de áreas (con permisos individuales)
        Route::get('areas/data', [AreaController::class, 'getData'])->name('areas.data')->middleware('check.permission:areas.view');
        Route::get('areas', [AreaController::class, 'index'])->name('areas.index')->middleware('check.permission:areas.view');
        Route::get('areas/create', [AreaController::class, 'create'])->name('areas.create')->middleware('check.permission:areas.create');
        Route::post('areas', [AreaController::class, 'store'])->name('areas.store')->middleware('check.permission:areas.create');
        Route::get('areas/{area}', [AreaController::class, 'show'])->name('areas.show')->middleware('check.permission:areas.view');
        Route::get('areas/{area}/edit', [AreaController::class, 'edit'])->name('areas.edit')->middleware('check.permission:areas.edit');
        Route::put('areas/{area}', [AreaController::class, 'update'])->name('areas.update')->middleware('check.permission:areas.edit');
        Route::patch('areas/{area}', [AreaController::class, 'update'])->name('areas.update.patch')->middleware('check.permission:areas.edit');
        Route::delete('areas/{area}', [AreaController::class, 'destroy'])->name('areas.destroy')->middleware('check.permission:areas.delete');

        // Rutas de cursos (con permisos individuales)
        Route::get('cursos/data', [CursoController::class, 'getData'])->name('cursos.data')->middleware('check.permission:cursos.view');
        Route::get('cursos/{curso}/stats', [CursoController::class, 'getStats'])->name('cursos.stats')->middleware('check.permission:cursos.view');
        Route::get('cursos', [CursoController::class, 'index'])->name('cursos.index')->middleware('check.permission:cursos.view');
        Route::get('cursos/create', [CursoController::class, 'create'])->name('cursos.create')->middleware('check.permission:cursos.create');
        Route::post('cursos', [CursoController::class, 'store'])->name('cursos.store')->middleware('check.permission:cursos.create');
        Route::get('cursos/{curso}', [CursoController::class, 'show'])->name('cursos.show')->middleware('check.permission:cursos.view');
        Route::get('cursos/{curso}/edit', [CursoController::class, 'edit'])->name('cursos.edit')->middleware('check.permission:cursos.edit');
        Route::put('cursos/{curso}', [CursoController::class, 'update'])->name('cursos.update')->middleware('check.permission:cursos.edit');
        Route::patch('cursos/{curso}', [CursoController::class, 'update'])->name('cursos.update.patch')->middleware('check.permission:cursos.edit');
        Route::delete('cursos/{curso}', [CursoController::class, 'destroy'])->name('cursos.destroy')->middleware('check.permission:cursos.delete');

        // Rutas del classroom
        Route::prefix('cursos/{curso}/classroom')->name('cursos.classroom.')->group(function () {
            Route::get('/', [CursoClassroomController::class, 'index'])->name('index');
            Route::get('/materiales', [CursoClassroomController::class, 'materiales'])->name('materiales');
            Route::post('/materiales', [CursoClassroomController::class, 'subirMaterial'])->name('materiales.store');
            Route::get('/materiales/test', function() { return response()->json(['test' => 'ok']); })->name('materiales.test');
            Route::get('/materiales/{material}/obtener', [CursoClassroomController::class, 'obtenerMaterial'])->name('materiales.obtener');
            Route::put('/materiales/{material}', [CursoClassroomController::class, 'actualizarMaterial'])->name('materiales.update');
            Route::delete('/materiales/{material}', [CursoClassroomController::class, 'eliminarMaterial'])->name('materiales.destroy');
            Route::get('/foros', [CursoClassroomController::class, 'foros'])->name('foros');
            Route::post('/foros', [CursoClassroomController::class, 'crearPost'])->name('foros.store');
            Route::post('/foros/{post}/responder', [CursoClassroomController::class, 'responderPost'])->name('foros.responder');
            Route::get('/actividades', [CursoClassroomController::class, 'actividades'])->name('actividades');
            Route::post('/actividades', [CursoClassroomController::class, 'crearActividad'])->name('actividades.store');
            Route::get('/actividades/{actividad}/obtener', [CursoClassroomController::class, 'obtenerActividad'])->name('actividades.obtener');
            Route::get('/actividades/{actividad}/datos-quiz', [CursoClassroomController::class, 'obtenerDatosQuiz'])->name('actividades.datos-quiz');
            Route::get('/datos-disponibles', [CursoClassroomController::class, 'obtenerDatosDisponibles'])->name('datos-disponibles');
            Route::get('/actividades/{actividad}/entregas', [CursoClassroomController::class, 'entregas'])->name('actividades.entregas');
            Route::post('/actividades/{actividad}/entregar', [CursoClassroomController::class, 'entregarActividad'])->name('actividades.entregar');
            Route::post('/actividades/{actividad}/toggle', [CursoClassroomController::class, 'toggleActividad'])->name('actividades.toggle');
            Route::post('/actividades/{actividad}/resolver-quiz', [CursoClassroomController::class, 'resolverQuiz'])->name('actividades.resolver-quiz');
            Route::put('/actividades/{actividad}/actualizar', [CursoClassroomController::class, 'actualizarActividad'])->name('actividades.actualizar');
            Route::delete('/actividades/{actividad}', [CursoClassroomController::class, 'eliminarActividad'])->name('actividades.destroy');
            Route::post('/actividades/{actividad}/calificar', [CursoClassroomController::class, 'calificarTarea'])->name('actividades.calificar');
            Route::get('/participantes', [CursoClassroomController::class, 'participantes'])->name('participantes');
            Route::post('/inscribir', [CursoClassroomController::class, 'inscribirEstudiante'])->name('inscribir');
            
            // Rutas de calificaciones
            Route::get('/calificaciones', [CursoClassroomController::class, 'getCalificacionesEstudiante'])->name('calificaciones');
            Route::get('/calificaciones/{estudiante}', [CursoClassroomController::class, 'getCalificacionesEstudiante'])->name('calificaciones.estudiante');
            Route::get('/porcentajes', [CursoClassroomController::class, 'getResumenPorcentajes'])->name('porcentajes');
        });

        // Ruta directa al classroom
        Route::get('cursos/{curso}/classroom', [CursoClassroomController::class, 'index'])->name('cursos.classroom');
    });

    // Rutas de la sección Académico (para usuarios finales)
    Route::prefix('academico')->name('academico.')->group(function () {
        // Control Pedagógico (Gradebook)
        Route::get('control-pedagogico', [ControlPedagogicoController::class, 'index'])->name('control-pedagogico.index');
        Route::post('control-pedagogico/guardar-calificacion', [ControlPedagogicoController::class, 'guardarCalificacionPublic'])->name('control-pedagogico.guardar-calificacion');
        Route::post('control-pedagogico/reset-actividad', [ControlPedagogicoController::class, 'resetActividad'])->name('control-pedagogico.reset-actividad');
        Route::post('control-pedagogico/toggle-actividad', [ControlPedagogicoController::class, 'toggleActividad'])->name('control-pedagogico.toggle-actividad');
        Route::post('control-pedagogico/reset-actividad-grupo', [ControlPedagogicoController::class, 'resetActividadGrupo'])->name('control-pedagogico.reset-actividad-grupo');
        Route::get('control-pedagogico/preview-certificado/{curso}/{estudiante}', [ControlPedagogicoController::class, 'previewCertificado'])->name('control-pedagogico.preview-certificado')->withoutMiddleware([]);
        
        // Cursos disponibles para estudiantes
        Route::get('cursos-disponibles', [AcademicoController::class, 'cursosDisponibles'])->name('cursos.disponibles');
        Route::get('cursos-disponibles/data', [AcademicoController::class, 'getCursosDisponiblesData'])->name('cursos.disponibles.data');

        // Acceso a curso específico para estudiante
        Route::get('curso/{curso}', [AcademicoController::class, 'verCurso'])->name('curso.ver');
        Route::get('curso/{curso}/aula-virtual', [AcademicoController::class, 'aulaVirtual'])->name('curso.aula-virtual');
        Route::get('curso/{curso}/materiales', [AcademicoController::class, 'verMateriales'])->name('curso.materiales');
        Route::get('curso/{curso}/actividades', [AcademicoController::class, 'verActividades'])->name('curso.actividades');
        Route::get('curso/{curso}/evaluaciones', [AcademicoController::class, 'verEvaluaciones'])->name('curso.evaluaciones');
        Route::get('curso/{curso}/certificado', [AcademicoController::class, 'generarCertificado'])->name('curso.certificado');

        // Interacciones del estudiante
        Route::match(['get', 'post'], 'curso/{curso}/inscribirse', [AcademicoController::class, 'inscribirseCurso'])->name('curso.inscribirse');
        Route::post('curso/{curso}/marcar-material/{material}', [AcademicoController::class, 'marcarMaterialVisto'])->name('curso.material.marcar');
        Route::post('curso/{curso}/entregar-actividad/{actividad}', [AcademicoController::class, 'entregarActividad'])->name('curso.actividad.entregar');
        Route::post('curso/{curso}/resolver-quiz/{actividad}', [AcademicoController::class, 'resolverQuiz'])->name('curso.quiz.resolver');
    });

    // Rutas de Configuración - Asignación de Cursos
    Route::prefix('configuracion')->name('configuracion.')->group(function () {
        
        // Ayuda - Administrador de Banner y Media de Inicio (protegido por permisos)
        Route::get('/ayuda', [\App\Http\Controllers\AyudaController::class, 'index'])->name('ayuda')->middleware('check.permission:ayuda.view');
        Route::post('/ayuda', [\App\Http\Controllers\AyudaController::class, 'store'])->name('ayuda.store')->middleware('check.permission:ayuda.create');
        Route::put('/ayuda/{id}', [\App\Http\Controllers\AyudaController::class, 'update'])->name('ayuda.update')->middleware('check.permission:ayuda.edit');
        Route::delete('/ayuda/{id}', [\App\Http\Controllers\AyudaController::class, 'destroy'])->name('ayuda.destroy')->middleware('check.permission:ayuda.delete');
        Route::post('/ayuda/{id}/toggle', [\App\Http\Controllers\AyudaController::class, 'toggleActivo'])->name('ayuda.toggle')->middleware('check.permission:ayuda.edit');
        Route::post('/ayuda/orden', [\App\Http\Controllers\AyudaController::class, 'updateOrden'])->name('ayuda.orden')->middleware('check.permission:ayuda.edit');

        Route::prefix('asignacion-cursos')->name('asignacion-cursos.')->group(function () {
            Route::get('/', [AsignacionCursoController::class, 'index'])->name('index');
            Route::get('/buscar-estudiantes', [AsignacionCursoController::class, 'buscarEstudiantes'])->name('buscar-estudiantes');
            Route::get('/estudiante/{estudiante}', [AsignacionCursoController::class, 'getEstudiante'])->name('get-estudiante');
            Route::get('/cursos', [AsignacionCursoController::class, 'getCursosDisponibles'])->name('get-cursos');
            Route::get('/categorias', [AsignacionCursoController::class, 'getCategorias'])->name('get-categorias');
            Route::post('/asignar', [AsignacionCursoController::class, 'asignar'])->name('asignar');
            Route::post('/asignar-todos', [AsignacionCursoController::class, 'asignarATodos'])->name('asignar-todos');
            Route::post('/remover', [AsignacionCursoController::class, 'removerAsignacion'])->name('remover');
            Route::get('/historial', [AsignacionCursoController::class, 'getHistorial'])->name('historial');
        });

        // Rutas de Gestión de Componentes
        Route::prefix('componentes')->name('componentes.')->group(function () {
            // Servicios/Áreas
            Route::get('servicios-areas/data', [ServicioAreaController::class, 'getData'])->name('servicios-areas.data');
            Route::resource('servicios-areas', ServicioAreaController::class);

            // Vinculación/Contrato
            Route::get('vinculacion-contrato/data', [VinculacionContratoController::class, 'getData'])->name('vinculacion-contrato.data');
            Route::resource('vinculacion-contrato', VinculacionContratoController::class);

            // Sedes
            Route::get('sedes/data', [SedeController::class, 'getData'])->name('sedes.data');
            Route::resource('sedes', SedeController::class);
        });

        // Rutas de Editor de Certificados
        Route::prefix('editor-certificados')->name('editor-certificados.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CertificadoPlantillaController::class, 'index'])->name('index');
            Route::get('/data', [\App\Http\Controllers\CertificadoPlantillaController::class, 'getData'])->name('data');
            Route::post('/guardar', [\App\Http\Controllers\CertificadoPlantillaController::class, 'store'])->name('store');
            Route::put('/{plantilla}', [\App\Http\Controllers\CertificadoPlantillaController::class, 'update'])->name('update');
            Route::delete('/{plantilla}', [\App\Http\Controllers\CertificadoPlantillaController::class, 'destroy'])->name('destroy');
            Route::get('/docente/{docente}/cursos', [\App\Http\Controllers\CertificadoPlantillaController::class, 'getCursosPorDocente'])->name('docente.cursos');
            Route::get('/curso/{curso}/estudiantes', [\App\Http\Controllers\CertificadoPlantillaController::class, 'getEstudiantesPorCurso'])->name('curso.estudiantes');
            Route::get('/{plantilla}/json', [\App\Http\Controllers\CertificadoPlantillaController::class, 'showJson'])->name('showJson');
        });

        // Rutas de Publicidad y Productos (con permisos individuales)
        Route::prefix('publicidad-productos')->name('publicidad-productos.')->group(function () {
            Route::get('/', [PublicidadProductoController::class, 'index'])->name('index')->middleware('check.permission:publicidad.view');
            Route::get('/data', [PublicidadProductoController::class, 'getData'])->name('data')->middleware('check.permission:publicidad.view');
            Route::post('/', [PublicidadProductoController::class, 'store'])->name('store')->middleware('check.permission:publicidad.create');
            Route::put('/{id}', [PublicidadProductoController::class, 'update'])->name('update')->middleware('check.permission:publicidad.edit');
            Route::delete('/{id}', [PublicidadProductoController::class, 'destroy'])->name('destroy')->middleware('check.permission:publicidad.delete');
            Route::post('/guardar-config', [PublicidadProductoController::class, 'guardarConfiguracion'])->name('guardar-config')->middleware('check.permission:publicidad.banner');
            Route::post('/guardar-categorias', [PublicidadProductoController::class, 'guardarCategorias'])->name('guardar-categorias')->middleware('check.permission:publicidad.edit');
        });
    });

    // Rutas de Consultas
    Route::prefix('consultas')->name('consultas.')->group(function () {
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ReporteEstudiantesController::class, 'index'])->middleware('check.permission:reportes.view')->name('index');
            Route::get('/data', [\App\Http\Controllers\ReporteEstudiantesController::class, 'getData'])->middleware('check.permission:reportes.view')->name('data');
            Route::get('/{id}', [\App\Http\Controllers\ReporteEstudiantesController::class, 'show'])->middleware('check.permission:reportes.view')->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\ReporteEstudiantesController::class, 'edit'])->middleware('check.permission:reportes.edit')->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\ReporteEstudiantesController::class, 'update'])->middleware('check.permission:reportes.edit')->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\ReporteEstudiantesController::class, 'destroy'])->middleware('check.permission:reportes.delete')->name('destroy');
        });
    });

});

require __DIR__.'/auth.php';

