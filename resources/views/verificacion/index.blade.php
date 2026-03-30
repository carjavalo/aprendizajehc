<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificar Certificado - {{ config('app.name', 'Hospital Universitario del Valle') }}</title>
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
        .verify-card {
            max-width: 560px;
            margin: 60px auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .verify-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .verify-header img {
            height: 60px;
            margin-bottom: 12px;
        }
        .verify-header h2 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .verify-header p {
            font-size: 0.85rem;
            opacity: 0.85;
            margin: 0;
        }
        .verify-body {
            padding: 30px;
        }
        .verify-input {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 1rem;
            letter-spacing: 1.5px;
            text-align: center;
            font-weight: 600;
            text-transform: uppercase;
            transition: border-color 0.2s;
        }
        .verify-input:focus {
            border-color: #2c5282;
            box-shadow: 0 0 0 3px rgba(44, 82, 130, 0.15);
            outline: none;
        }
        .btn-verify {
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: transform 0.1s, box-shadow 0.2s;
        }
        .btn-verify:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(30, 58, 95, 0.3);
            color: white;
        }
        .shield-icon {
            font-size: 3rem;
            color: white;
            opacity: 0.9;
        }
        .footer-text {
            text-align: center;
            color: #94a3b8;
            font-size: 0.8rem;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verify-card">
            <div class="verify-header">
                <i class="fas fa-shield-alt shield-icon"></i>
                <h2>Verificación de Certificado</h2>
                <p>Hospital Universitario del Valle - Educación Continua</p>
            </div>
            <div class="verify-body">
                <p class="text-muted text-center mb-4" style="font-size: 0.9rem;">
                    Ingrese el código de verificación que aparece en el certificado para comprobar su autenticidad.
                </p>
                <form action="{{ route('verificar.buscar') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <input type="text" 
                               name="codigo" 
                               class="form-control verify-input" 
                               placeholder="SHC-XXXXXXXX-XXXX"
                               value="{{ old('codigo') }}"
                               required
                               maxlength="64"
                               autocomplete="off">
                        @error('codigo')
                            <small class="text-danger mt-1 d-block text-center">{{ $message }}</small>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-verify">
                        <i class="fas fa-search me-2"></i> Verificar Certificado
                    </button>
                </form>
            </div>
            <div class="footer-text">
                <i class="fas fa-lock me-1"></i> Sistema de verificación oficial &mdash; {{ config('app.name') }}
            </div>
        </div>
    </div>
</body>
</html>
