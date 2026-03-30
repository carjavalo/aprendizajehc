@extends('admin.layouts.master')

@section('content')
    <div class="container">
        <h1>Test Quiz View</h1>
        @php
            $actividad = App\Models\CursoActividad::find(15);
            $curso = $actividad ? $actividad->curso : null;
        @endphp
        
        @if($actividad)
            <div class="card">
                <div class="card-body">
                    <h3>{{ $actividad->titulo }}</h3>
                    <p>Tipo: {{ $actividad->tipo }}</p>
                    <p>Habilitado: {{ $actividad->habilitado ? 'Si' : 'No' }}</p>
                    
                    <button type="button" class="btn btn-warning btn-lg btn-block" 
                            onclick="iniciarQuiz({{ $actividad->id }})">
                        <i class="fas fa-play-circle"></i> Iniciar Quiz
                    </button>
                    <textarea id="quiz-data-{{ $actividad->id }}" class="d-none">{!! json_encode($actividad, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</textarea>
                </div>
            </div>
            
            @include('academico.curso.partials.quiz-scripts')
        @else
            <div class="alert alert-danger">Actividad 15 no encontrada</div>
        @endif
    </div>
@stop
