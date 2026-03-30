<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServicioArea;
use App\Models\VinculacionContrato;
use App\Models\Sede;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $availableDocumentTypes = User::getAvailableDocumentTypes();
        $serviciosAreas = ServicioArea::all();
        $vinculacionesContrato = VinculacionContrato::all();
        $sedes = Sede::all();
        
        return view('adminlte::auth.register', compact(
            'availableDocumentTypes',
            'serviciosAreas',
            'vinculacionesContrato',
            'sedes'
        ));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'apellido1' => ['required', 'string', 'max:100'],
            'apellido2' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'tipo_documento' => ['required', 'in:' . implode(',', User::getAvailableDocumentTypes())],
            'numero_documento' => ['required', 'string', 'max:20', 'unique:users'],
            'servicio_area_id' => ['required', 'exists:servicios_areas,id'],
            'vinculacion_contrato_id' => ['required', 'exists:vinculacion_contrato,id'],
            'sede_id' => ['required', 'exists:sedes,id'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'apellido1' => $request->apellido1,
            'apellido2' => $request->apellido2,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'Estudiante', // Todos los registros públicos son Estudiantes
            'tipo_documento' => $request->tipo_documento,
            'numero_documento' => $request->numero_documento,
            'servicio_area_id' => $request->servicio_area_id,
            'vinculacion_contrato_id' => $request->vinculacion_contrato_id,
            'sede_id' => $request->sede_id,
            'phone' => $request->phone,
        ]);

        // Asignar automáticamente el curso de Inducción Institucional (ID 18)
        try {
            $cursoInduccion = \App\Models\Curso::find(18);
            if ($cursoInduccion) {
                // Obtener un administrador para asignar el curso, o usar el propio usuario si no hay
                $adminRole = \App\Models\User::whereIn('role', ['Super Admin', 'Administrador'])->first();
                $asignadorId = $adminRole ? $adminRole->id : $user->id;

                // Crear la asignación para que pueda inscribirse
                \App\Models\CursoAsignacion::firstOrCreate(
                    [
                        'curso_id' => 18,
                        'estudiante_id' => $user->id
                    ],
                    [
                        'asignado_por' => $asignadorId,
                        'estado' => 'activo',
                        'fecha_asignacion' => now()
                    ]
                );

                // Enviar correo de asignación de curso
                $inscripcionUrl = route('academico.curso.inscribirse', 18);
                \Illuminate\Support\Facades\Mail::to($user->email)->send(
                    new \App\Mail\AsignacionCurso($user, $cursoInduccion, $inscripcionUrl)
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al asignar automáticamente curso Inducción al registrar usuario: ' . $e->getMessage());
        }

        // NO disparar evento Registered para evitar correo automático de Laravel en inglés
        // event(new Registered($user));

        // Enviar SOLO correo de verificación personalizado en español
        try {
            $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'verification.verify',
                now()->addHours(24),
                ['id' => $user->id, 'hash' => sha1($user->email)]
            );

            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\VerificarCuenta($user, $verificationUrl)
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al enviar correo de verificación: ' . $e->getMessage());
            // Mostramos un mensaje claro para que los administradores identifiquen el error de SMTP
            session()->flash('error', 'Registro exitoso, pero hubo un error con el servidor de correo. La configuración SMTP de credenciales de Google es incorrecta. Por favor contacta al administrador.');
        }

        Auth::login($user);

        return redirect(route('verification.notice'))->with('status', '¡Registro exitoso! Por favor verifica tu correo electrónico.');
    }
}
