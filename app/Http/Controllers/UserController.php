<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Curso;
use App\Models\CursoAsignacion;
use App\Models\ServicioArea;
use App\Models\VinculacionContrato;
use App\Models\Sede;
use App\Mail\AsignacionCurso;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('users.view');

        $currentUser = auth()->user();
        
        // Si el usuario autenticado es Operador, excluir Super Admins
        if ($currentUser->role === 'Operador') {
            $users = User::with(['servicioArea', 'vinculacionContrato', 'sede'])
                ->where('role', '!=', 'Super Admin')->get();
        } else {
            // Para otros roles, mostrar todos los usuarios
            $users = User::with(['servicioArea', 'vinculacionContrato', 'sede'])->get();
        }
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('users.create');

        $availableRoles = User::getAvailableRoles();
        
        // Si el usuario autenticado es Operador, solo puede asignar Estudiante, Registrado, Docente u Operador
        if (auth()->user()->role === 'Operador') {
            $availableRoles = array_values(array_filter($availableRoles, function($role) {
                return in_array($role, ['Estudiante', 'Registrado', 'Docente', 'Operador', 'Consultor Agesoc', 'Consultor Asstracud']);
            }));
        }
        
        $availableDocumentTypes = User::getAvailableDocumentTypes();
        $serviciosAreas = ServicioArea::all();
        $vinculacionesContrato = VinculacionContrato::all();
        $sedes = Sede::all();
        
        return view('admin.users.create', compact(
            'availableRoles', 
            'availableDocumentTypes',
            'serviciosAreas',
            'vinculacionesContrato',
            'sedes'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('users.create');

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'apellido1' => ['required', 'string', 'max:100'],
            'apellido2' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:' . implode(',', User::getAvailableRoles())],
            'tipo_documento' => ['required', 'in:' . implode(',', User::getAvailableDocumentTypes())],
            'numero_documento' => ['required', 'string', 'max:20', 'unique:users'],
            'servicio_area_id' => ['nullable', 'exists:servicios_areas,id'],
            'vinculacion_contrato_id' => ['nullable', 'exists:vinculacion_contrato,id'],
            'sede_id' => ['nullable', 'exists:sedes,id'],
        ]);
        
        // Validación adicional: Operadores solo pueden asignar Estudiante, Registrado, Docente u Operador
        if (auth()->user()->role === 'Operador' && !in_array($request->role, ['Estudiante', 'Registrado', 'Docente', 'Operador', 'Consultor Agesoc', 'Consultor Asstracud'])) {
            return redirect()->back()
                ->withErrors(['role' => 'No tienes permisos para asignar ese rol. Solo se permiten Estudiante, Registrado, Docente, Operador, Consultor Agesoc y Consultor Asstracud.'])
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'apellido1' => $request->apellido1,
            'apellido2' => $request->apellido2,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'tipo_documento' => $request->tipo_documento,
            'numero_documento' => $request->numero_documento,
            'servicio_area_id' => $request->servicio_area_id,
            'vinculacion_contrato_id' => $request->vinculacion_contrato_id,
            'sede_id' => $request->sede_id,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        Gate::authorize('users.view');

        $user = User::with(['servicioArea', 'vinculacionContrato', 'sede'])->findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        Gate::authorize('users.edit');

        $user = User::findOrFail($id);
        $availableRoles = User::getAvailableRoles();
        
        // Si el usuario autenticado es Operador, solo puede asignar Estudiante, Registrado, Docente u Operador
        if (auth()->user()->role === 'Operador') {
            $availableRoles = array_values(array_filter($availableRoles, function($role) {
                return in_array($role, ['Estudiante', 'Registrado', 'Docente', 'Operador', 'Consultor Agesoc', 'Consultor Asstracud']);
            }));
        }
        
        $availableDocumentTypes = User::getAvailableDocumentTypes();
        $serviciosAreas = ServicioArea::all();
        $vinculacionesContrato = VinculacionContrato::all();
        $sedes = Sede::all();
        
        return view('admin.users.edit', compact(
            'user', 
            'availableRoles', 
            'availableDocumentTypes',
            'serviciosAreas',
            'vinculacionesContrato',
            'sedes'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Gate::authorize('users.edit');

        $user = User::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'apellido1' => ['required', 'string', 'max:100'],
            'apellido2' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'in:' . implode(',', User::getAvailableRoles())],
            'tipo_documento' => ['required', 'in:' . implode(',', User::getAvailableDocumentTypes())],
            'numero_documento' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'servicio_area_id' => ['nullable', 'exists:servicios_areas,id'],
            'vinculacion_contrato_id' => ['nullable', 'exists:vinculacion_contrato,id'],
            'sede_id' => ['nullable', 'exists:sedes,id'],
        ]);
        
        // Validación adicional: Operadores solo pueden asignar Estudiante, Registrado, Docente u Operador
        if (auth()->user()->role === 'Operador' && !in_array($request->role, ['Estudiante', 'Registrado', 'Docente', 'Operador', 'Consultor Agesoc', 'Consultor Asstracud'])) {
            return redirect()->back()
                ->withErrors(['role' => 'No tienes permisos para asignar ese rol. Solo se permiten Estudiante, Registrado, Docente, Operador, Consultor Agesoc y Consultor Asstracud.'])
                ->withInput();
        }

        $data = [
            'name' => $request->name,
            'apellido1' => $request->apellido1,
            'apellido2' => $request->apellido2,
            'email' => $request->email,
            'role' => $request->role,
            'tipo_documento' => $request->tipo_documento,
            'numero_documento' => $request->numero_documento,
            'servicio_area_id' => $request->servicio_area_id,
            'vinculacion_contrato_id' => $request->vinculacion_contrato_id,
            'sede_id' => $request->sede_id,
        ];

        // Actualizar contraseña solo si se proporciona
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['string', 'min:8', 'confirmed'],
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Gate::authorize('users.delete');

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    /**
     * Importar usuarios desde archivo Excel
     * 
     * Mapeo de columnas:
     * A = name, B = apellido1, C = apellido2, D = email, E = phone
     * F = tipo_documento, G = numero_documento, H = role (ignorado, siempre Estudiante)
     */
    public function import(Request $request)
    {
        Gate::authorize('users.import');

        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls|max:10240'
        ], [
            'archivo_excel.required' => 'Debe seleccionar un archivo Excel.',
            'archivo_excel.mimes' => 'El archivo debe ser de tipo Excel (.xlsx o .xls).',
            'archivo_excel.max' => 'El archivo no debe superar los 10MB.'
        ]);

        try {
            $file = $request->file('archivo_excel');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Obtener el curso de Inducción Institucional (ID=18)
            $cursoInduccion = Curso::find(18);
            if (!$cursoInduccion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el curso de Inducción Institucional (ID=18). Contacte al administrador.'
                ], 422);
            }

            $fechaImportacion = Carbon::now();
            $usuariosCreados = 0;
            $usuariosOmitidos = 0;
            $errores = [];
            $correosEnviados = 0;
            $correosError = 0;

            // Procesar desde la fila 2 (índice 1) ya que la fila 1 son encabezados
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $filaNumero = $i + 1; // Para mensajes de error (fila Excel)

                // Obtener datos de las celdas
                $name = trim($row[0] ?? '');           // Columna A
                $apellido1 = trim($row[1] ?? '');      // Columna B
                $apellido2 = trim($row[2] ?? '');      // Columna C
                $email = trim($row[3] ?? '');          // Columna D
                $phone = trim($row[4] ?? '');          // Columna E
                $tipoDocumento = trim($row[5] ?? '');  // Columna F
                $numeroDocumento = trim($row[6] ?? '');// Columna G
                // Columna H (role) se ignora, siempre será "Estudiante"

                // Validaciones básicas
                if (empty($numeroDocumento)) {
                    $errores[] = "Fila {$filaNumero}: Número de documento vacío, fila omitida.";
                    continue;
                }

                if (empty($name)) {
                    $errores[] = "Fila {$filaNumero}: Nombre vacío, fila omitida.";
                    continue;
                }

                // Verificar si el numero_documento ya existe
                $usuarioExistente = User::where('numero_documento', $numeroDocumento)->first();
                
                if ($usuarioExistente) {
                    $usuariosOmitidos++;
                    continue; // No modificar registros existentes
                }

                // Verificar si el email ya existe (si se proporcionó email)
                if (!empty($email)) {
                    $emailExistente = User::where('email', $email)->first();
                    if ($emailExistente) {
                        $errores[] = "Fila {$filaNumero}: El email '{$email}' ya existe, se creará usuario sin email.";
                        $email = null; // Dejar email vacío para que lo actualice después
                    }
                }

                // Normalizar tipo de documento
                $tiposDocumentoValidos = User::getAvailableDocumentTypes();
                if (!empty($tipoDocumento) && !in_array($tipoDocumento, $tiposDocumentoValidos)) {
                    // Intentar mapear abreviaciones comunes
                    $tipoDocumentoMap = [
                        'CC' => 'Cédula de Ciudadanía',
                        'TI' => 'Tarjeta de Identidad',
                        'CE' => 'Cédula de Extranjería',
                        'PA' => 'Pasaporte',
                        'RC' => 'Registro Civil',
                        'NIT' => 'NIT',
                    ];
                    $tipoDocumento = $tipoDocumentoMap[strtoupper($tipoDocumento)] ?? 'Cédula de Ciudadanía';
                }

                if (empty($tipoDocumento)) {
                    $tipoDocumento = 'Cédula de Ciudadanía'; // Valor por defecto
                }

                // Crear el usuario
                try {
                    $nuevoUsuario = User::create([
                        'name' => $name,
                        'apellido1' => $apellido1,
                        'apellido2' => $apellido2,
                        'email' => $email ?: "{$numeroDocumento}@pendiente.actualizar",
                        'phone' => $phone,
                        'tipo_documento' => $tipoDocumento,
                        'numero_documento' => $numeroDocumento,
                        'role' => 'Estudiante',
                        'password' => Hash::make($numeroDocumento),
                        'email_verified_at' => $fechaImportacion,
                    ]);

                    $usuariosCreados++;

                    // Crear registro en curso_asignaciones (para que aparezca en asignación-cursos y cursos-disponibles)
                    if (!CursoAsignacion::where('curso_id', $cursoInduccion->id)->where('estudiante_id', $nuevoUsuario->id)->exists()) {
                        CursoAsignacion::create([
                            'curso_id' => $cursoInduccion->id,
                            'estudiante_id' => $nuevoUsuario->id,
                            'asignado_por' => Auth::id(),
                            'estado' => 'activo',
                            'fecha_asignacion' => $fechaImportacion,
                        ]);
                    }

                    // Inscribir al estudiante en el curso (tabla pivot curso_estudiantes)
                    if (!$cursoInduccion->estudiantes()->where('estudiante_id', $nuevoUsuario->id)->exists()) {
                        $cursoInduccion->estudiantes()->attach($nuevoUsuario->id, [
                            'estado' => 'activo',
                            'progreso' => 0,
                            'fecha_inscripcion' => $fechaImportacion,
                        ]);
                    }

                    // Enviar correo de asignación del curso
                    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        try {
                            $inscripcionUrl = route('academico.curso.inscribirse', $cursoInduccion->id);
                            Mail::to($nuevoUsuario->email)->send(new AsignacionCurso(
                                $nuevoUsuario,
                                $cursoInduccion,
                                $inscripcionUrl
                            ));
                            $correosEnviados++;
                        } catch (\Exception $mailError) {
                            Log::warning("Error al enviar correo de asignación a {$email}: " . $mailError->getMessage());
                            $correosError++;
                        }
                    }

                } catch (\Exception $createError) {
                    $errores[] = "Fila {$filaNumero}: Error al crear usuario - " . $createError->getMessage();
                }
            }

            // Construir mensaje de resultado
            $mensaje = "Importación completada. ";
            $mensaje .= "Usuarios creados: {$usuariosCreados}. ";
            $mensaje .= "Usuarios omitidos (ya existían): {$usuariosOmitidos}. ";
            
            if ($correosEnviados > 0) {
                $mensaje .= "Correos de asignación enviados: {$correosEnviados}. ";
            }
            if ($correosError > 0) {
                $mensaje .= "Correos con error: {$correosError}. ";
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'detalles' => [
                    'usuarios_creados' => $usuariosCreados,
                    'usuarios_omitidos' => $usuariosOmitidos,
                    'correos_enviados' => $correosEnviados,
                    'correos_error' => $correosError,
                    'errores' => $errores
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en importación de usuarios: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }
}
