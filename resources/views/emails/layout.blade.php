<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Notificación' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f6f7f8;
            padding: 20px;
            line-height: 1.6;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        /* Marca de agua con logo */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            z-index: 0;
            pointer-events: none;
        }
        
        .watermark img {
            width: 400px;
            height: auto;
        }
        
        .email-header {
            background: linear-gradient(135deg, #2c4370 0%, #1e2f4d 100%);
            padding: 30px 20px;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .email-header img {
            max-width: 120px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .email-header h1 {
            color: #ffffff;
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }
        
        .email-body {
            padding: 40px 30px;
            position: relative;
            z-index: 1;
        }
        
        .email-body h2 {
            color: #2c4370;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .email-body p {
            color: #555555;
            font-size: 15px;
            margin-bottom: 15px;
            line-height: 1.8;
        }
        
        .email-body strong {
            color: #2c4370;
            font-weight: 600;
        }
        
        .btn-primary {
            display: inline-block;
            background: #2c4370 !important;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 35px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            text-align: center;
            box-shadow: 0 4px 12px rgba(44, 67, 112, 0.4);
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary:hover {
            background: #1e2f4d !important;
            box-shadow: 0 6px 16px rgba(44, 67, 112, 0.5);
            transform: translateY(-2px);
        }
        
        .info-box {
            background-color: #f0f4f8;
            border-left: 4px solid #2c4370;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .info-box p {
            margin: 5px 0;
            font-size: 14px;
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e0e0e0, transparent);
            margin: 30px 0;
        }
        
        .email-footer {
            background-color: #f6f7f8;
            padding: 30px 20px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
            position: relative;
            z-index: 1;
        }
        
        .email-footer p {
            color: #777777;
            font-size: 13px;
            margin: 8px 0;
        }
        
        .email-footer a {
            color: #2c4370;
            text-decoration: none;
            font-weight: 500;
        }
        
        .email-footer a:hover {
            text-decoration: underline;
        }
        
        .contact-info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .contact-info p {
            font-size: 12px;
            color: #999999;
            margin: 3px 0;
        }
        
        .alternative-link {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 13px;
            color: #666666;
            word-break: break-all;
        }
        
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .email-body {
                padding: 25px 20px;
            }
            
            .email-header h1 {
                font-size: 18px;
            }
            
            .email-body h2 {
                font-size: 20px;
            }
            
            .btn-primary {
                display: block;
                padding: 12px 20px;
            }
            
            .watermark img {
                width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Marca de agua con logo -->
        <div class="watermark">
            <img src="{{ asset('images/logocorreo.jpeg') }}" alt="Logo HUV">
        </div>
        
        <!-- Header -->
        <div class="email-header">
            <img src="{{ asset('images/logocorreo.jpeg') }}" alt="Logo Hospital Universitario del Valle">
            <h1>Soporte de Historias Clínicas - Hospital Universitario del Valle</h1>
        </div>
        
        <!-- Body -->
        <div class="email-body">
            @yield('content')
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p><strong>Hospital Universitario Del Valle "Evaristo García" E.S.E.</strong></p>
            <p>Séptimo piso - Calle 5 No 36-08</p>
            <p>Cali, Valle del Cauca, Colombia</p>
            
            <div class="contact-info">
                <p>Este es un correo automático, por favor no responder.</p>
                <p>Para soporte técnico, contacte a: <a href="mailto:soportehistoriaclinica@correohuv.gov.co">soportehistoriaclinica@correohuv.gov.co</a></p>
                <p>&copy; {{ date('Y') }} Hospital Universitario del Valle. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
