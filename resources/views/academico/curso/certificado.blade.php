<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado - {{ $curso->titulo }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @page { size: 960px 680px; margin: 0; }
        body { 
            margin: 0; 
            font-family: 'Inter', sans-serif; 
            background: #525659;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .cert-container {
            width: 960px; /* same as editor */
            height: 680px; 
            background-color: #fff;
            background-size: 100% 100%;
            background-repeat: no-repeat;
            background-position: center;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            overflow: hidden;
        }

        /* Print Specific */
        @media print {
            body { 
                background: white; 
                display: block; 
                min-height: auto;
                margin: 0;
                padding: 0;
            }
            .cert-container { 
                width: 960px; 
                height: 680px; 
                box-shadow: none !important;
                margin: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            /* Prevenir que Bootstrap oculte o desestructure elementos absolutos en impresión */
            .cert-container * {
                visibility: visible !important;
            }
            .no-print { display: none !important; }
        }

        /* Toolbar floating on top */
        .toolbar {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        .btn-print {
            background: #1e3a8a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
        }
        .btn-print:hover { background: #152c6e; }

        /* Barra de verificación en la parte inferior del certificado */
        .cert-verification-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 28px;
            background: rgba(30, 58, 138, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            z-index: 50;
            border-top: 1px solid rgba(30, 58, 138, 0.12);
        }
        .cert-verification-bar .verify-icon {
            width: 14px;
            height: 14px;
            opacity: 0.5;
        }
        .cert-verification-bar .verify-text {
            font-family: 'Inter', monospace;
            font-size: 8px;
            color: #475569;
            letter-spacing: 0.5px;
        }
        .cert-verification-bar .verify-code {
            font-family: monospace;
            font-size: 8.5px;
            font-weight: 700;
            color: #1e3a8a;
            letter-spacing: 1.5px;
            background: rgba(30, 58, 138, 0.06);
            padding: 2px 8px;
            border-radius: 4px;
        }
        .cert-verification-bar .verify-url {
            font-family: 'Inter', sans-serif;
            font-size: 7.5px;
            color: #64748b;
        }
        .cert-qr-container {
            position: absolute;
            bottom: 32px;
            right: 16px;
            z-index: 50;
            text-align: center;
        }
        .cert-qr-container img, .cert-qr-container canvas {
            width: 144px !important;
            height: 144px !important;
            border: 1px solid rgba(30, 58, 138, 0.15);
            border-radius: 4px;
            background: white;
            padding: 3px;
        }
        .cert-qr-container .qr-label {
            font-size: 8px;
            color: #94a3b8;
            margin-top: 1px;
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body>

    @if(empty($enIframe))
    <div class="toolbar no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir Certificado</button>
    </div>
    @endif

    <!-- Container that uses the background and HTML structure from the DB -->
    @php
        // Obtener URL del fondo - el accessor ya maneja fallback a base64
        $fondoUrl = $plantilla->fondo_url;
        // Doble protección: si fondo_url retornó vacío, usar base64 directo
        if (empty($fondoUrl)) {
            $fondoUrl = $plantilla->elementos_json['fondo_base64'] ?? '';
        }
        // Escapar comillas simples en data URIs para evitar romper el CSS
        $fondoUrlSafe = str_replace("'", "%27", $fondoUrl);
    @endphp
    <div class="cert-container" id="certCanvas" 
         style="background-image: url('{{ $fondoUrlSafe }}')">
        {!! $plantilla->html_content !!}

        {{-- QR Code de verificación (generado localmente con JS) --}}
        @if(isset($certificadoEmitido))
        <div class="cert-qr-container">
            <div id="certQRCode"></div>
            <div class="qr-label">Verificar</div>
        </div>

        {{-- Barra inferior de verificación --}}
        <div class="cert-verification-bar">
            <svg class="verify-icon" viewBox="0 0 24 24" fill="none" stroke="#1e3a8a" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="M9 12l2 2 4-4" stroke="#059669" stroke-width="2.5"/>
            </svg>
            <span class="verify-text">Código de verificación:</span>
            <span class="verify-code">{{ $certificadoEmitido->codigo_verificacion }}</span>
            <span class="verify-url">{{ $certificadoEmitido->url_verificacion }}</span>
        </div>
        @endif
    </div>

    <!-- QR Code generator library (local) -->
    <script src="{{ asset('js/qrcode.min.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // === Verificar que la imagen de fondo cargó correctamente ===
            const certCanvas = document.getElementById('certCanvas');
            if (certCanvas) {
                const bgImage = getComputedStyle(certCanvas).backgroundImage;
                // Si el fondo es una URL de archivo (no base64), verificar que cargue
                if (bgImage && bgImage.includes('url(') && !bgImage.includes('data:image')) {
                    const urlMatch = bgImage.match(/url\(["']?([^"')]+)["']?\)/);
                    if (urlMatch && urlMatch[1]) {
                        const img = new Image();
                        img.onerror = function() {
                            // La imagen de archivo no cargó - intentar con base64 inline
                            console.warn('Fondo de certificado no accesible, usando base64 fallback');
                            @php
                                $base64Fallback = $plantilla->elementos_json['fondo_base64'] ?? '';
                            @endphp
                            const base64Bg = @json($base64Fallback);
                            if (base64Bg) {
                                certCanvas.style.backgroundImage = 'url(' + base64Bg + ')';
                            }
                        };
                        img.src = urlMatch[1];
                    }
                }
            }

            // === Generar QR de verificación ===
            @if(isset($certificadoEmitido))
            const qrContainer = document.getElementById('certQRCode');
            if (qrContainer) {
                new QRCode(qrContainer, {
                    text: "{{ $certificadoEmitido->url_verificacion }}",
                    width: 144,
                    height: 144,
                    colorDark: '#1e3a8a',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            }
            @endif
            // === Datos dinámicos del estudiante y curso ===
            const nombreCompleto = "{{ mb_strtoupper(trim(($user->name ?? '') . ' ' . ($user->apellido1 ?? '') . ' ' . ($user->apellido2 ?? ''))) }}";
            const numeroDocumento = "{{ $user->numero_documento ?? '' }}";
            const cursoNombre = "{{ mb_strtoupper($curso->titulo) }}";
            const horas = "{{ $curso->duracion_horas ?? '40' }}";

            @php
                // Obtener datos de inscripción real del estudiante al curso
                $inscripcion = \DB::table('curso_estudiantes')
                    ->where('curso_id', $curso->id)
                    ->where('estudiante_id', $user->id)
                    ->first();
                
                // Fecha inicio = fecha de inscripción del estudiante (cuando inició el curso)
                $fechaInicioEstudiante = $inscripcion && $inscripcion->fecha_inscripcion 
                    ? $inscripcion->fecha_inscripcion 
                    : $curso->fecha_inicio;

                // Fecha fin = cuando el estudiante terminó el curso
                // Prioridad: 1) fecha_emision del certificado, 2) ultima_actividad del estudiante, 3) fecha_fin del curso
                $fechaFinEstudiante = null;
                if (isset($certificadoEmitido) && $certificadoEmitido && $certificadoEmitido->fecha_emision) {
                    $fechaFinEstudiante = $certificadoEmitido->fecha_emision;
                } elseif ($inscripcion && $inscripcion->ultima_actividad) {
                    $fechaFinEstudiante = $inscripcion->ultima_actividad;
                } else {
                    $fechaFinEstudiante = $curso->fecha_fin;
                }

                // Formatear las fechas con Carbon
                $fechaInicioFormateada = $fechaInicioEstudiante 
                    ? \Carbon\Carbon::parse($fechaInicioEstudiante)->locale('es')->isoFormat('DD [de] MMMM [de] YYYY') 
                    : 'Inicio';
                $fechaFinFormateada = $fechaFinEstudiante 
                    ? \Carbon\Carbon::parse($fechaFinEstudiante)->locale('es')->isoFormat('DD [de] MMMM [de] YYYY') 
                    : 'Fin';
                // Capitalizar nombre del mes (marzo → Marzo)
                $fechaInicioFormateada = preg_replace_callback('/de ([a-záéíóúñ])/u', function($m) {
                    return 'de ' . mb_strtoupper($m[1]);
                }, $fechaInicioFormateada, 1);
                $fechaFinFormateada = preg_replace_callback('/de ([a-záéíóúñ])/u', function($m) {
                    return 'de ' . mb_strtoupper($m[1]);
                }, $fechaFinFormateada, 1);
            @endphp
            const fechaInicio = "{{ $fechaInicioFormateada }}";
            const fechaFin = "{{ $fechaFinFormateada }}";

            // === Reemplazar SOLO los campos dinámicos del estudiante ===

            // Nombre completo del estudiante
            const elNombre = document.getElementById('certNombreCompleto');
            if (elNombre) elNombre.textContent = nombreCompleto;

            // Documento: mostrar solo el tipo y el número (ej. "CC 12345678") sin texto adicional
            const elDoc = document.getElementById('certDocumento');
            if (elDoc) {
                if (numeroDocumento) {
                    const tipoDoc = "{{ mb_strtoupper(str_replace('.', '', $user->tipo_documento ?? 'CC')) }}";
                    elDoc.textContent = tipoDoc + ' ' + numeroDocumento;
                }
            }

            // Nombre del curso
            const elCurso = document.getElementById('certCursoNombre');
            if (elCurso) elCurso.textContent = cursoNombre;

            // Horas
            const elHoras = document.getElementById('certHoras');
            if (elHoras) elHoras.textContent = horas;

            // Fechas de inicio y fin del curso
            const elFI = document.getElementById('certFechaInicio');
            if (elFI) elFI.textContent = fechaInicio;
            const elFF = document.getElementById('certFechaFin');
            if (elFF) elFF.textContent = fechaFin;

            // === certFirmaNombre y certFirmaCargo NO se tocan ===
            // Se dejan con los valores originales configurados en la plantilla del certificado

            // Limpiar clases del editor
            document.querySelectorAll('.draggable-element').forEach(el => {
                el.classList.remove('draggable-element');
                el.style.cursor = 'default';
                el.oncontextmenu = null;
                el.onmousedown = null;
            });
        });
    </script>
</body>
</html>