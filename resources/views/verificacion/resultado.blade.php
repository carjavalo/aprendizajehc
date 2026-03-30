<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resultado de Verificación - {{ config('app.name', 'Hospital Universitario del Valle') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            min-height: 100vh;
            font-family: 'Figtree', sans-serif;
        }
        .result-card {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .result-header-valid {
            background: linear-gradient(135deg, #065f46 0%, #059669 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .result-header-invalid {
            background: linear-gradient(135deg, #991b1b 0%, #dc2626 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .result-icon {
            font-size: 3.5rem;
            margin-bottom: 10px;
        }
        .result-body {
            padding: 30px;
        }
        .data-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .data-row:last-child {
            border-bottom: none;
        }
        .data-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
        }
        .data-value {
            font-size: 0.9rem;
            color: #1e293b;
            font-weight: 600;
            text-align: right;
            max-width: 60%;
        }
        .badge-verified {
            background: #dcfce7;
            color: #166534;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .badge-not-found {
            background: #fee2e2;
            color: #991b1b;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .footer-actions {
            padding: 0 30px 30px;
            text-align: center;
        }
        .btn-back {
            background: #f1f5f9;
            color: #475569;
            border: none;
            border-radius: 10px;
            padding: 10px 24px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-back:hover {
            background: #e2e8f0;
            color: #334155;
        }
        .codigo-display {
            font-family: monospace;
            font-size: 1.1rem;
            letter-spacing: 2px;
            background: rgba(255,255,255,0.15);
            padding: 6px 14px;
            border-radius: 8px;
            display: inline-block;
            margin-top: 6px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="result-card">
            @if($encontrado)
                {{-- CERTIFICADO VÁLIDO --}}
                <div class="result-header-valid">
                    <i class="fas fa-check-circle result-icon"></i>
                    <h2 style="font-size: 1.3rem; font-weight: 600;">Certificado Verificado</h2>
                    <p style="opacity: 0.85; font-size: 0.85rem; margin-bottom: 8px;">
                        Este certificado es auténtico y fue emitido oficialmente.
                    </p>
                    <div class="codigo-display">{{ $certificado->codigo_verificacion }}</div>
                </div>
                <div class="result-body">
                    <div class="data-row">
                        <span class="data-label"><i class="fas fa-user me-1"></i> Estudiante</span>
                        <span class="data-value">
                            {{ $certificado->estudiante->name ?? '' }}
                            {{ $certificado->estudiante->apellido1 ?? '' }}
                            {{ $certificado->estudiante->apellido2 ?? '' }}
                        </span>
                    </div>
                    <div class="data-row">
                        <span class="data-label"><i class="fas fa-id-card me-1"></i> Documento</span>
                        <span class="data-value">
                            {{ $certificado->estudiante->tipo_documento ?? 'C.C.' }}
                            {{ $certificado->estudiante->numero_documento ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="data-row">
                        <span class="data-label"><i class="fas fa-book me-1"></i> Curso</span>
                        <span class="data-value">{{ $certificado->curso->titulo ?? 'N/A' }}</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label"><i class="fas fa-star me-1"></i> Nota Final</span>
                        <span class="data-value">{{ $certificado->nota_final }}</span>
                    </div>
                    @php
                        // Obtener fechas reales del estudiante en el curso
                        $inscripcionCert = \DB::table('curso_estudiantes')
                            ->where('curso_id', $certificado->curso_id)
                            ->where('estudiante_id', $certificado->estudiante_id)
                            ->first();
                        
                        // Fecha inicio = fecha de inscripción del estudiante
                        $fechaInicioCert = $inscripcionCert && $inscripcionCert->fecha_inscripcion 
                            ? \Carbon\Carbon::parse($inscripcionCert->fecha_inscripcion)->locale('es')->isoFormat('DD [de] MMMM [de] YYYY') 
                            : 'N/A';
                        
                        // Fecha fin = fecha emisión del certificado (cuando terminó el curso)
                        $fechaFinCert = $certificado->fecha_emision 
                            ? $certificado->fecha_emision->locale('es')->isoFormat('DD [de] MMMM [de] YYYY') 
                            : ($inscripcionCert && $inscripcionCert->ultima_actividad 
                                ? \Carbon\Carbon::parse($inscripcionCert->ultima_actividad)->locale('es')->isoFormat('DD [de] MMMM [de] YYYY') 
                                : 'N/A');
                    @endphp
                    <div class="data-row">
                        <span class="data-label"><i class="fas fa-calendar-plus me-1"></i> Fecha de Inicio</span>
                        <span class="data-value">{{ $fechaInicioCert }}</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label"><i class="fas fa-calendar-check me-1"></i> Fecha de Finalización</span>
                        <span class="data-value">{{ $fechaFinCert }}</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label"><i class="fas fa-calendar me-1"></i> Fecha de Emisión</span>
                        <span class="data-value">
                            {{ $certificado->fecha_emision ? $certificado->fecha_emision->locale('es')->isoFormat('DD [de] MMMM [de] YYYY') : 'N/A' }}
                        </span>
                    </div>
                    <div class="data-row">
                        <span class="data-label"><i class="fas fa-shield-alt me-1"></i> Estado</span>
                        <span class="data-value">
                            <span class="badge-verified"><i class="fas fa-check me-1"></i>Auténtico</span>
                        </span>
                    </div>
                </div>
            @else
                {{-- CERTIFICADO NO ENCONTRADO --}}
                <div class="result-header-invalid">
                    <i class="fas fa-times-circle result-icon"></i>
                    <h2 style="font-size: 1.3rem; font-weight: 600;">Certificado No Encontrado</h2>
                    <p style="opacity: 0.85; font-size: 0.85rem; margin-bottom: 8px;">
                        No se encontró ningún certificado con el código proporcionado.
                    </p>
                    <div class="codigo-display">{{ $codigo }}</div>
                </div>
                <div class="result-body text-center">
                    <div class="py-3">
                        <i class="fas fa-exclamation-triangle text-warning mb-3" style="font-size: 2rem;"></i>
                        <p class="text-muted" style="font-size: 0.9rem;">
                            El código ingresado no corresponde a ningún certificado emitido por esta institución.
                            Verifique que el código sea correcto e intente nuevamente.
                        </p>
                        <div class="mt-3 p-3 rounded" style="background: #fef2f2; border: 1px solid #fecaca;">
                            <small class="text-danger">
                                <i class="fas fa-info-circle me-1"></i>
                                Si sospecha que un certificado es fraudulento, contacte a la institución.
                            </small>
                        </div>
                    </div>
                </div>
            @endif

            <div class="footer-actions">
                <a href="{{ route('verificar.formulario') }}" class="btn-back">
                    <i class="fas fa-arrow-left me-1"></i> Verificar otro certificado
                </a>
            </div>
        </div>

        <p class="text-center text-muted mt-3" style="font-size: 0.8rem;">
            <i class="fas fa-lock me-1"></i> Sistema de verificación oficial &mdash; {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
