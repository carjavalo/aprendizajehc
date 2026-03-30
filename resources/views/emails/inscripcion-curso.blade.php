@extends('emails.layout')

@section('content')
    <h2>¡Inscripción Exitosa!</h2>
    
    <p>Hola <strong>{{ $user->name }}</strong>,</p>
    
    <p>¡Felicitaciones! Te has inscrito exitosamente en el siguiente curso:</p>
    
    <div class="info-box">
        <p><strong>📚 Curso:</strong> {{ $curso->nombre }}</p>
        @if($curso->instructor)
            <p><strong>👨‍🏫 Instructor:</strong> {{ $curso->instructor->name }}</p>
        @endif
        @if($curso->fecha_inicio)
            <p><strong>📅 Fecha de inicio:</strong> {{ \Carbon\Carbon::parse($curso->fecha_inicio)->format('d/m/Y') }}</p>
        @endif
        @if($curso->fecha_fin)
            <p><strong>📅 Fecha de finalización:</strong> {{ \Carbon\Carbon::parse($curso->fecha_fin)->format('d/m/Y') }}</p>
        @endif
        @if($curso->duracion_horas)
            <p><strong>⏱️ Duración:</strong> {{ $curso->duracion_horas }} horas</p>
        @endif
    </div>
    
    <p>Ya puedes acceder al aula virtual del curso y comenzar tu aprendizaje:</p>
    
    <div style="text-align: center;">
        <a href="{{ $cursoUrl }}" class="btn-primary">Ir al Aula Virtual</a>
    </div>
    
    <div class="divider"></div>
    
    <p><strong>📋 Próximos pasos:</strong></p>
    <ol style="color: #555555; font-size: 15px; line-height: 1.8; margin-left: 20px;">
        <li>Accede al aula virtual del curso</li>
        <li>Revisa el material de introducción</li>
        <li>Consulta el cronograma de actividades</li>
        <li>Participa activamente en las sesiones</li>
        <li>Completa las evaluaciones a tiempo</li>
    </ol>
    
    @if($curso->descripcion)
    <div class="divider"></div>
    
    <p><strong>Sobre el curso:</strong></p>
    <p style="color: #666666; font-size: 14px; line-height: 1.7;">
        {{ Str::limit($curso->descripcion, 300) }}
    </p>
    @endif
    
    <div class="divider"></div>
    
    <p><strong>💡 Consejos para aprovechar al máximo tu curso:</strong></p>
    <ul style="color: #555555; font-size: 15px; line-height: 1.8; margin-left: 20px;">
        <li>Dedica tiempo regular al estudio</li>
        <li>Participa en los foros de discusión</li>
        <li>No dudes en hacer preguntas al instructor</li>
        <li>Completa las actividades antes de las fechas límite</li>
        <li>Interactúa con tus compañeros de curso</li>
    </ul>
    
    <p style="margin-top: 30px;">¡Te deseamos mucho éxito en tu proceso de aprendizaje!</p>
    
    <p>Saludos cordiales,<br>
    <strong>Equipo de Soporte de Historias Clínicas</strong><br>
    Hospital Universitario del Valle</p>
@endsection
