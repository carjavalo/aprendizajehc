<script>
    // Variables globales para el quiz - usar var para permitir redeclaración en cargas dinámicas
    var quizTimer = null;
    var tiempoInicio = null;

    // Función para iniciar el quiz
    function iniciarQuiz(actividadId) {
        // Obtener datos del quiz desde el textarea oculto
        const quizDataElement = document.getElementById('quiz-data-' + actividadId);
        
        if (!quizDataElement) {
            Swal.fire('Error', 'No se encontraron los datos del quiz', 'error');
            return;
        }
        
        try {
            const actividad = JSON.parse(quizDataElement.value);
            
            // Fix for double-encoded JSON
            if (typeof actividad.contenido_json === 'string') {
                try {
                    actividad.contenido_json = JSON.parse(actividad.contenido_json);
                } catch (e) {
                    console.error('Error parsing nested JSON:', e);
                }
            }
            
            if (!actividad || !actividad.contenido_json) {
                Swal.fire('Error', 'No se encontraron las preguntas del quiz', 'error');
                return;
            }
            
            mostrarModalQuiz(actividadId, actividad);
        } catch (e) {
            console.error('Error parsing quiz data:', e);
            Swal.fire('Error', 'Error al procesar los datos del quiz', 'error');
        }
    }

    // Mostrar modal del quiz
    function mostrarModalQuiz(actividadId, actividad) {
        const quizData = actividad.contenido_json;
        const preguntas = quizData.questions || [];
        const duracion = quizData.duration || 30;
        
        if (preguntas.length === 0) {
            Swal.fire('Error', 'Este quiz no tiene preguntas configuradas', 'error');
            return;
        }
        
        let preguntasHTML = '';
        preguntas.forEach((pregunta, index) => {
            // Determinar si es pregunta de múltiple respuesta
            const esMultiple = (pregunta.isMultipleChoice === true) || 
                               (pregunta.correctAnswers && pregunta.correctAnswers.length > 1);
            const inputType = esMultiple ? 'checkbox' : 'radio';
            const tipoBadge = esMultiple 
                ? '<span class="badge badge-info ml-2"><i class="fas fa-check-double"></i> Múltiple respuesta</span>' 
                : '';
            
            preguntasHTML += `
                <div class="quiz-question" data-multiple="${esMultiple}">
                    <h5>
                        <span class="badge badge-primary">${index + 1}</span> ${pregunta.text}
                        ${tipoBadge}
                    </h5>
                    <small class="text-muted"><i class="fas fa-percentage"></i> ${pregunta.points}% del quiz</small>
                    ${esMultiple ? '<br><small class="text-info"><i class="fas fa-info-circle"></i> Selecciona todas las respuestas correctas</small>' : ''}
                    <div class="mt-3">
            `;
            
            // Normalizar opciones: soportar tanto formato key-value {"A": "texto"}
            // como formato array de objetos [{id, text, isCorrect}]
            const opciones = pregunta.options;
            if (Array.isArray(opciones)) {
                // Formato antiguo: array de objetos
                opciones.forEach((opt, optIndex) => {
                    const letter = String.fromCharCode(65 + optIndex); // A, B, C...
                    const textoOpcion = (typeof opt === 'object') ? (opt.text || '') : String(opt);
                    preguntasHTML += `
                        <label class="quiz-option">
                            <input type="${inputType}" name="pregunta_${pregunta.id}" value="${letter}" 
                                   ${esMultiple ? `data-pregunta-id="${pregunta.id}"` : ''}>
                            <strong>${letter})</strong> ${textoOpcion}
                        </label>
                    `;
                });
            } else if (opciones && typeof opciones === 'object') {
                // Formato nuevo: key-value map {"A": "texto", "B": "texto"}
                Object.keys(opciones).forEach(opcion => {
                    const textoOpcion = (typeof opciones[opcion] === 'object') 
                        ? (opciones[opcion].text || JSON.stringify(opciones[opcion])) 
                        : String(opciones[opcion]);
                    preguntasHTML += `
                        <label class="quiz-option">
                            <input type="${inputType}" name="pregunta_${pregunta.id}" value="${opcion}" 
                                   ${esMultiple ? `data-pregunta-id="${pregunta.id}"` : ''}>
                            <strong>${opcion})</strong> ${textoOpcion}
                        </label>
                    `;
                });
            }
            
            preguntasHTML += `
                    </div>
                </div>
            `;
        });
        
        Swal.fire({
            title: '<i class="fas fa-graduation-cap"></i> ' + actividad.titulo,
            html: `
                <div class="text-center mb-3">
                    <div class="quiz-timer" id="quiz-timer">
                        <i class="fas fa-clock"></i> <span id="tiempo-restante">${duracion}:00</span>
                    </div>
                    <small class="text-muted">Nota máxima: 5.0 | Nota mínima aprobación: ${actividad.nota_minima_aprobacion || 3.0}</small>
                </div>
                <div id="quiz-preguntas" style="max-height: 400px; overflow-y: auto; text-align: left;">
                    ${preguntasHTML}
                </div>
            `,
            width: '800px',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check-circle"></i> Enviar Respuestas',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                iniciarTemporizador(duracion, actividadId, actividad);
                
                // Manejar selección de opciones - radio buttons
                $(document).off('click.quizRadio').on('click.quizRadio', '.quiz-option input[type="radio"]', function() {
                    const name = $(this).attr('name');
                    $(`input[name="${name}"]`).closest('.quiz-option').removeClass('selected');
                    $(this).closest('.quiz-option').addClass('selected');
                });
                
                // Manejar selección de opciones - checkboxes
                $(document).off('click.quizCheckbox').on('click.quizCheckbox', '.quiz-option input[type="checkbox"]', function() {
                    if ($(this).is(':checked')) {
                        $(this).closest('.quiz-option').addClass('selected');
                    } else {
                        $(this).closest('.quiz-option').removeClass('selected');
                    }
                });
                
                // Click en el label activa selción visual
                $(document).off('click.quizOption').on('click.quizOption', '.quiz-option', function(e) {
                    if ($(e.target).is('input')) return; // No double-trigger
                    const input = $(this).find('input');
                    if (input.attr('type') === 'radio') {
                        input.prop('checked', true).trigger('click');
                    } else {
                        input.prop('checked', !input.prop('checked')).trigger('click');
                    }
                });
            },
            willClose: () => {
                if (quizTimer) {
                    clearInterval(quizTimer);
                }
                $(document).off('click.quizRadio click.quizCheckbox click.quizOption');
            },
            preConfirm: () => {
                const respuestas = {};
                let todasRespondidas = true;
                
                preguntas.forEach(pregunta => {
                    const esMultiple = (pregunta.isMultipleChoice === true) || 
                                       (pregunta.correctAnswers && pregunta.correctAnswers.length > 1);
                    
                    if (esMultiple) {
                        // Recoger checkboxes marcados como array
                        const checked = [];
                        $(`input[data-pregunta-id="${pregunta.id}"]:checked`).each(function() {
                            checked.push($(this).val());
                        });
                        if (checked.length > 0) {
                            respuestas[pregunta.id] = checked;
                        } else {
                            todasRespondidas = false;
                        }
                    } else {
                        // Recoger radio button
                        const respuesta = $(`input[name="pregunta_${pregunta.id}"]:checked`).val();
                        if (respuesta) {
                            respuestas[pregunta.id] = respuesta;
                        } else {
                            todasRespondidas = false;
                        }
                    }
                });
                
                if (!todasRespondidas) {
                    Swal.showValidationMessage('Por favor responde todas las preguntas');
                    return false;
                }
                
                return respuestas;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                enviarRespuestasQuiz(actividadId, result.value);
            }
        });
    }

    // Iniciar temporizador del quiz
    function iniciarTemporizador(duracionMinutos, actividadId, actividad) {
        tiempoInicio = Date.now();
        let tiempoRestante = duracionMinutos * 60; // segundos
        
        quizTimer = setInterval(() => {
            tiempoRestante--;
            
            const minutos = Math.floor(tiempoRestante / 60);
            const segundos = tiempoRestante % 60;
            
            $('#tiempo-restante').text(`${minutos}:${segundos.toString().padStart(2, '0')}`);
            
            if (tiempoRestante <= 60) {
                $('#tiempo-restante').parent().css('color', '#dc3545');
            }
            
            if (tiempoRestante <= 0) {
                clearInterval(quizTimer);
                Swal.close();
                Swal.fire({
                    icon: 'warning',
                    title: 'Tiempo Agotado',
                    text: 'El tiempo del quiz ha terminado. Se enviarán las respuestas marcadas.',
                    confirmButtonText: 'Entendido'
                }).then(() => {
                    const respuestas = {};
                    const preguntas = actividad.contenido_json.questions || [];
                    preguntas.forEach(pregunta => {
                        const esMultiple = (pregunta.isMultipleChoice === true) || 
                                           (pregunta.correctAnswers && pregunta.correctAnswers.length > 1);
                        if (esMultiple) {
                            const checked = [];
                            $(`input[data-pregunta-id="${pregunta.id}"]:checked`).each(function() {
                                checked.push($(this).val());
                            });
                            if (checked.length > 0) respuestas[pregunta.id] = checked;
                        } else {
                            const respuesta = $(`input[name="pregunta_${pregunta.id}"]:checked`).val();
                            if (respuesta) respuestas[pregunta.id] = respuesta;
                        }
                    });
                    enviarRespuestasQuiz(actividadId, respuestas);
                });
            }
        }, 1000);
    }

    // Enviar respuestas del quiz
    function enviarRespuestasQuiz(actividadId, respuestas) {
        const tiempoTranscurrido = Math.floor((Date.now() - tiempoInicio) / 1000); // segundos
        
        Swal.fire({
            title: 'Enviando respuestas...',
            html: '<i class="fas fa-spinner fa-spin fa-3x"></i>',
            showConfirmButton: false,
            allowOutsideClick: false
        });
        
        $.ajax({
            url: '{{ route("academico.curso.quiz.resolver", [$curso->id, ":actividadId"]) }}'.replace(':actividadId', actividadId),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                respuestas: respuestas,
                tiempo_transcurrido: tiempoTranscurrido
            },
            success: function(response) {
                if (response.success) {
                    mostrarResultados(response);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error al enviar las respuestas';
                Swal.fire('Error', message, 'error');
            }
        });
    }

    // Mostrar resultados del quiz
    function mostrarResultados(response) {
        const resultados = response.resultados || [];
        const notaObtenida = response.nota_obtenida || 0;
        const notaMaxima = response.nota_maxima || 5.0;
        const notaMinimaAprobacion = response.nota_minima_aprobacion || 3.0;
        const porcentaje = response.porcentaje || 0;
        const aprobado = response.aprobado || false;
        
        let resultadosHTML = '';
        resultados.forEach((resultado, index) => {
            const claseResultado = resultado.es_correcta ? 'quiz-result-correct' : 'quiz-result-incorrect';
            const iconoResultado = resultado.es_correcta ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>';
            const porcentajePregunta = resultado.porcentaje_pregunta || 0;
            const multipleBadge = resultado.es_multiple ? ' <span class="badge badge-info badge-sm"><i class="fas fa-check-double"></i></span>' : '';
            
            resultadosHTML += `
                <div class="quiz-question ${claseResultado}">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6><span class="badge badge-secondary">${index + 1}</span> ${resultado.pregunta}${multipleBadge}</h6>
                        ${iconoResultado}
                    </div>
                    <p class="mb-1"><strong>Tu respuesta:</strong> ${resultado.respuesta_estudiante || 'Sin respuesta'}</p>
                    ${!resultado.es_correcta ? `<p class="mb-1"><strong>Respuesta correcta:</strong> ${resultado.respuesta_correcta}</p>` : ''}
                    <p class="mb-0"><strong>Nota obtenida:</strong> ${resultado.puntos} / ${(porcentajePregunta / 100 * 5).toFixed(2)} <small class="text-muted">(${porcentajePregunta}% del quiz)</small></p>
                </div>
            `;
        });
        
        Swal.fire({
            icon: aprobado ? 'success' : 'warning',
            title: aprobado ? '¡Felicitaciones!' : 'Quiz Completado',
            html: `
                <div class="text-center mb-4">
                    <h2 class="display-4" style="color: ${aprobado ? '#28a745' : '#dc3545'}">${notaObtenida} / ${notaMaxima}</h2>
                    <p class="lead">${porcentaje}% de respuestas correctas</p>
                    <p class="text-muted">Nota mínima de aprobación: ${notaMinimaAprobacion}</p>
                    <span class="badge badge-${aprobado ? 'success' : 'danger'} badge-pill px-3 py-2" style="font-size: 1.1em;">
                        ${aprobado ? '<i class="fas fa-check-circle"></i> APROBADO' : '<i class="fas fa-times-circle"></i> NO APROBADO'}
                    </span>
                </div>
                <div style="max-height: 400px; overflow-y: auto; text-align: left;">
                    <h5 class="mb-3">Revisión de Respuestas:</h5>
                    ${resultadosHTML}
                </div>
            `,
            width: '800px',
            confirmButtonText: '<i class="fas fa-check"></i> Entendido',
            confirmButtonColor: '#007bff'
        }).then(() => {
            // Recargar la página para actualizar el progreso
            location.reload();
        });
    }
</script>

<style>
    /* Quiz Styles */
    .quiz-question {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    
    .quiz-option {
        padding: 12px;
        margin: 8px 0;
        background: white;
        border: 2px solid #dee2e6;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: block;
    }
    
    .quiz-option:hover {
        background: #e9ecef;
        border-color: #007bff;
    }
    
    .quiz-option input[type="radio"],
    .quiz-option input[type="checkbox"] {
        margin-right: 10px;
    }
    
    .quiz-option.selected {
        background: #cfe2ff;
        border-color: #007bff;
    }
    
    .quiz-timer {
        font-size: 24px;
        font-weight: bold;
        color: #28a745;
    }
    
    .quiz-result-correct {
        background: #d4edda;
        border-left-color: #28a745;
    }
    
    .quiz-result-incorrect {
        background: #f8d7da;
        border-left-color: #dc3545;
    }
</style>
