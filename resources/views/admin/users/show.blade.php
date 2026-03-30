@extends('admin.layouts.master')

@section('title', 'Detalle de Usuario')

@section('content_header')
    <h1>Detalle de Usuario</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">Información de {{ $user->name }} {{ $user->apellido1 }} {{ $user->apellido2 }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th style="width: 25%" class="bg-light">ID</th>
                                            <td>{{ $user->id }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Nombre</th>
                                            <td>{{ $user->name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Primer Apellido</th>
                                            <td>{{ $user->apellido1 }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Segundo Apellido</th>
                                            <td>{{ $user->apellido2 }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Correo Electrónico</th>
                                            <td>{{ $user->email }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Verificado</th>
                                            <td>
                                                @if($user->email_verified_at)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle"></i> Verificado el 
                                                        {{ \Carbon\Carbon::parse($user->email_verified_at)->format('d/m/Y H:i') }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Pendiente de verificación
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Fecha de Registro</th>
                                            <td>{{ $user->created_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Última Actualización</th>
                                            <td>{{ $user->updated_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Servicio / Área</th>
                                            <td>
                                                @if($user->servicioArea)
                                                    <span class="badge badge-primary">{{ $user->servicioArea->nombre }}</span>
                                                @else
                                                    <span class="text-muted">No especificado</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Tipo de Vinculación/Contrato</th>
                                            <td>
                                                @if($user->vinculacionContrato)
                                                    <span class="badge badge-info">{{ $user->vinculacionContrato->nombre }}</span>
                                                @else
                                                    <span class="text-muted">No especificado</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Sede</th>
                                            <td>
                                                @if($user->sede)
                                                    <span class="badge badge-success">{{ $user->sede->nombre }}</span>
                                                @else
                                                    <span class="text-muted">No especificado</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="user-profile">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name . ' ' . $user->apellido1) }}&size=200&background=random&color=fff" 
                                     alt="{{ $user->name }}" class="img-circle img-fluid border p-2 mb-3">
                                <h3>{{ $user->name }} {{ $user->apellido1 }} {{ $user->apellido2 }}</h3>
                                <p class="text-muted">{{ $user->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="btn-group" role="group">
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger delete-btn">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/admin_custom.css') }}">
    <style>
        .user-profile img {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        .user-profile img:hover {
            transform: scale(1.05);
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Confirmación para eliminar
            $('.delete-form').submit(function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esto!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
    </script>
@stop 