@extends('emails.layout')

@section('content')
    <h2>¡Bienvenido a nuestra plataforma!</h2>
    
    <p>Hola <strong>{{ $user->name }}</strong>,</p>
    
    <p>¡Tu cuenta ha sido verificada exitosamente! Ahora eres parte de la comunidad de aprendizaje del Hospital Universitario del Valle.</p>
    
    <div class="divider"></div>
    
    <p><strong>🚀 Primeros pasos en la plataforma:</strong></p>
    <ol style="color: #555555; font-size: 15px; line-height: 1.8; margin-left: 20px;">
        <li><strong>Completa tu perfil:</strong> Agrega tu información profesional y foto de perfil</li>
        <li><strong>Explora los cursos:</strong> Navega por el catálogo de cursos disponibles</li>
        <li><strong>Inscríbete:</strong> Selecciona los cursos que te interesen</li>
        <li><strong>Comienza a aprender:</strong> Accede al material y participa activamente</li>
    </ol>
    
    <div class="divider"></div>
    
    <p><strong>📚 ¿Qué encontrarás en la plataforma?</strong></p>
    
    <div class="info-box">
        <p><strong>Cursos de Capacitación</strong></p>
        <p style="font-size: 14px; color: #666;">Accede a una amplia variedad de cursos diseñados por expertos en el área de la salud.</p>
    </div>
    
    <div class="info-box">
        <p><strong>Material Didáctico</strong></p>
        <p style="font-size: 14px; color: #666;">Documentos, videos, presentaciones y recursos complementarios para tu aprendizaje.</p>
    </div>
    
    <div class="info-box">
        <p><strong>Evaluaciones y Certificados</strong></p>
        <p style="font-size: 14px; color: #666;">Realiza evaluaciones y obtén certificados oficiales al completar los cursos.</p>
    </div>
    
    <div class="info-box">
        <p><strong>Interacción y Soporte</strong></p>
        <p style="font-size: 14px; color: #666;">Comunícate con instructores y compañeros a través del chat interno.</p>
    </div>
    
    <div class="divider"></div>
    
    <p><strong>💡 Consejos para aprovechar la plataforma:</strong></p>
    <ul style="color: #555555; font-size: 15px; line-height: 1.8; margin-left: 20px;">
        <li>Revisa regularmente las notificaciones de nuevos cursos</li>
        <li>Mantén tu perfil actualizado</li>
        <li>Participa activamente en los foros y discusiones</li>
        <li>Completa los cursos a tu propio ritmo</li>
        <li>Descarga tus certificados al finalizar</li>
    </ul>
    
    <div class="divider"></div>
    
    <p><strong>📞 ¿Necesitas ayuda?</strong></p>
    <p>Si tienes alguna pregunta o necesitas asistencia, nuestro equipo de soporte está disponible para ayudarte:</p>
    <ul style="color: #555555; font-size: 14px; line-height: 1.8; margin-left: 20px;">
        <li>📧 Email: <a href="mailto:oficinacoordinadoraacademica@correohuv.gov.co">oficinacoordinadoraacademica@correohuv.gov.co</a></li>
        <li>💬 Chat interno de la plataforma</li>
        <li>📍 Oficina: Séptimo piso, Calle 5 No 36-08</li>
    </ul>
    
    <div class="info-box" style="background-color: #d4edda; border-left-color: #28a745;">
        <p><strong>✨ ¡Estamos aquí para apoyarte en tu desarrollo profesional!</strong></p>
        <p style="font-size: 14px;">Tu crecimiento y aprendizaje son nuestra prioridad.</p>
    </div>
    
    <p style="margin-top: 30px;">¡Bienvenido nuevamente y mucho éxito en tu proceso de capacitación!</p>
    
    <p>Saludos cordiales,<br>
    <strong>Equipo de Soporte de Historias Clínicas</strong><br>
    Hospital Universitario del Valle</p>
@endsection
