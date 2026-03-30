@extends('emails.layout')

@section('content')
    <h2>¡Bienvenido a nuestra plataforma!</h2>
    
    <p>Hola <strong>{{ $user->name }}</strong>,</p>
    
    <p>Gracias por registrarte en la plataforma de capacitación del Hospital Universitario del Valle. Estamos emocionados de tenerte con nosotros.</p>
    
    <p>Para completar tu registro y activar tu cuenta, por favor verifica tu dirección de correo electrónico después de leer la Política de Tratamiento de Datos al final de este correo.</p>
    
    <div class="info-box">
        <p><strong>⏰ Importante:</strong></p>
        <p>Este enlace de verificación expirará en <strong>24 horas</strong>.</p>
        <p>Si no solicitaste esta cuenta, puedes ignorar este correo de forma segura.</p>
    </div>
    
    <div class="divider"></div>
    
    <p><strong>¿Qué puedes hacer en la plataforma?</strong></p>
    <ul style="color: #555555; font-size: 15px; line-height: 1.8; margin-left: 20px;">
        <li>Acceder a cursos de capacitación profesional</li>
        <li>Obtener certificados de finalización</li>
        <li>Interactuar con instructores y compañeros</li>
        <li>Realizar evaluaciones y seguimiento de tu progreso</li>
    </ul>
    
    <div class="divider"></div>
    
    <p>Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:</p>
    
    <div class="alternative-link">
        {{ $verificationUrl }}
    </div>
    
    <div class="info-box" style="background-color: #fff3cd; border-left-color: #ffc107; margin-top: 25px;">
        <p><strong>📋 Política de Tratamiento de Datos Personales:</strong></p>
        <p style="font-size: 13px; color: #555;">Al verificar su cuenta, usted acepta y autoriza de manera expresa e inequívoca el tratamiento de sus datos personales por parte del Hospital Universitario del Valle "Evaristo García" E.S.E., conforme a la Ley 1581 de 2012 y su Decreto Reglamentario 1377 de 2013. Sus datos serán utilizados exclusivamente para fines académicos, de capacitación y gestión de la plataforma AprendizajeHC. Usted podrá ejercer sus derechos de acceso, corrección, eliminación y revocación comunicándose a <a href="mailto:soportehistoriaclinica@correohuv.gov.co">soportehistoriaclinica@correohuv.gov.co</a>.</p>
    </div>

    <div style="text-align: center; margin-top: 25px;">
        <a href="{{ $verificationUrl }}" class="btn-primary">Verificar mi cuenta</a>
    </div>

    <p style="margin-top: 30px;">¡Esperamos verte pronto en la plataforma!</p>
    
    <p>Saludos cordiales,<br>
    <strong>Equipo de Soporte de Historias Clínicas</strong><br>
    Hospital Universitario del Valle</p>
@endsection
