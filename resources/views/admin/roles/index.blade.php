@extends("admin.layouts.master")

@section("title", "Gestión de Roles")

@section("content_header")
    <h1>Gestión de Roles</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de usuarios y asignación de roles</h3>
        </div>
        <div class="card-body">
            @if (session("success"))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session("success") }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="roles-table" class="table table-striped table-hover table-bordered responsive nowrap" width="100%">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Email</th>
                            <th>Documento</th>
                            <th>Rol Actual</th>
                            <th>Cambiar Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }} {{ $user->apellido1 }} {{ $user->apellido2 }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->tipo_documento && $user->numero_documento)
                                        <small class="text-muted">{{ $user->tipo_documento }}:</small><br>
                                        <strong>{{ $user->numero_documento }}</strong>
                                    @else
                                        <span class="text-muted">No especificado</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $user->role === "Super Admin" ? "danger" : ($user->role === "Administrador" ? "warning" : ($user->role === "Docente" ? "success" : ($user->role === "Estudiante" ? "info" : "secondary"))) }}" id="badge-{{ $user->id }}">
                                        {{ $user->role ?? "Registrado" }}
                                    </span>
                                </td>
                                <td>
                                    <select class="form-control form-control-sm role-select" data-user="{{ $user->id }}">
                                        @foreach($availableRoles as $role)
                                            <option value="{{ $role }}" {{ $user->role == $role ? "selected" : "" }}>{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section("css")
    <style>
        .table-responsive { overflow-x: auto; }
        .role-select { min-width: 150px; }
    </style>
@stop

@section("js")
    <script>
        $(document).ready(function() {
            var table = $("#roles-table").DataTable({
                "responsive": true,
                "language": {
                    "url": "{{ asset('js/datatables-spanish.json') }}"
                },
                "order": [[ 0, "desc" ]]
            });

            // Ajustar tabla cuando cambia el tamaño de la ventana
            $(window).on("resize", function() {
                $("#roles-table").DataTable().columns.adjust().responsive.recalc();
            });

            $(".role-select").on("change", function() {
                var userId = $(this).data("user");
                var newRole = $(this).val();

                $.ajax({
                    url: "/roles/" + userId,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        _method: "PUT",
                        role: newRole
                    },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                icon: "success",
                                title: "¡Éxito!",
                                text: response.message,
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000
                            });
                            
                            // Update badge
                            var badge = $("#badge-" + userId);
                            badge.text(newRole);
                            badge.removeClass().addClass("badge");
                            if (newRole === "Super Admin") badge.addClass("badge-danger");
                            else if (newRole === "Administrador") badge.addClass("badge-warning text-dark");
                            else if (newRole === "Docente") badge.addClass("badge-success");
                            else if (newRole === "Estudiante") badge.addClass("badge-info");
                            else badge.addClass("badge-secondary");
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "No se pudo actualizar el rol. Verifica tus permisos."
                        });
                    }
                });
            });
        });
    </script>
@stop

