<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        try {
            // Verificar si el usuario ya está verificado
            if ($request->user()->hasVerifiedEmail()) {
                return redirect()->intended(route('dashboard', absolute: false))
                    ->with('status', 'Email ya verificado. ¡Bienvenido!');
            }

            // Intentar marcar el email como verificado
            if ($request->user()->markEmailAsVerified()) {
                event(new Verified($request->user()));
                
                // Enviar correo de bienvenida
                try {
                    $dashboardUrl = route('dashboard');
                    \Illuminate\Support\Facades\Mail::to($request->user()->email)->send(
                        new \App\Mail\BienvenidaUsuario($request->user(), $dashboardUrl)
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error al enviar correo de bienvenida: ' . $e->getMessage());
                }

                // Asignar curso ID 18 y enviar correo después de 1 minuto
                $this->asignarCursoInduccion($request->user());

                return redirect()->intended(route('dashboard', absolute: false))
                    ->with('status', '¡Email verificado exitosamente! Bienvenido al sistema.');
            }

            // Si no se pudo verificar, redirigir con error
            return redirect()->route('verification.notice')
                ->with('error', 'No se pudo verificar el email. Por favor, intenta nuevamente.');

        } catch (\Exception $e) {
            // En caso de error, redirigir a la página de verificación con mensaje de error
            return redirect()->route('verification.notice')
                ->with('error', 'Hubo un problema con la verificación. Por favor, solicita un nuevo enlace.');
        }
    }

    /**
     * Alternative verification method for cases where signed middleware fails.
     */
    public function verifyAlternative(Request $request, $id, $hash): RedirectResponse
    {
        try {
            // Buscar el usuario por ID
            $user = \App\Models\User::findOrFail($id);

            // Verificar que el hash coincida con el email del usuario
            if (sha1($user->email) !== $hash) {
                return redirect()->route('login')
                    ->with('error', 'El enlace de verificación no es válido.');
            }

            // Verificar si el usuario ya está verificado
            if ($user->hasVerifiedEmail()) {
                auth()->login($user);
                return redirect()->route('dashboard')
                    ->with('status', 'Email ya verificado. ¡Bienvenido!');
            }

            // Marcar el email como verificado
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));

                // Enviar correo de bienvenida
                try {
                    $dashboardUrl = route('dashboard');
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(
                        new \App\Mail\BienvenidaUsuario($user, $dashboardUrl)
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error al enviar correo de bienvenida: ' . $e->getMessage());
                }

                // Asignar curso ID 18 y enviar correo después de 1 minuto
                $this->asignarCursoInduccion($user);

                // Autenticar al usuario automáticamente
                auth()->login($user);

                return redirect()->route('dashboard')
                    ->with('success', '¡Email verificado exitosamente! Bienvenido al sistema.');
            }

            return redirect()->route('login')
                ->with('error', 'No se pudo verificar el email. Por favor, intenta nuevamente.');

        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Hubo un problema con la verificación. Por favor, solicita un nuevo enlace.');
        }
    }

    /**
     * Show verification error page for invalid or expired links.
     */
    public function showError(Request $request): View
    {
        return view('adminlte::auth.verification-error');
    }

    /**
     * Asignar curso de Inducción Institucional (ID 18) con delay de 1 minuto
     */
    private function asignarCursoInduccion($user): void
    {
        try {
            // Programar la asignación y envío de correo para 1 minuto después
            dispatch(function () use ($user) {
                try {
                    $curso = \App\Models\Curso::find(18);
                    
                    if (!$curso) {
                        \Illuminate\Support\Facades\Log::warning('Curso ID 18 no encontrado para asignación automática');
                        return;
                    }

                    // Verificar si ya tiene asignación
                    $yaAsignado = \Illuminate\Support\Facades\DB::table('curso_asignaciones')
                        ->where('curso_id', 18)
                        ->where('estudiante_id', $user->id)
                        ->exists();

                    if ($yaAsignado) {
                        \Illuminate\Support\Facades\Log::info("Usuario {$user->id} ya tiene asignación al curso 18");
                        return;
                    }

                    // Crear asignación
                    \Illuminate\Support\Facades\DB::table('curso_asignaciones')->insert([
                        'curso_id' => 18,
                        'estudiante_id' => $user->id,
                        'asignado_por' => 1, // Sistema
                        'estado' => 'activo',
                        'fecha_asignacion' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    \Illuminate\Support\Facades\Log::info("Curso 18 asignado exitosamente al usuario {$user->id}");

                    // Enviar correo de asignación
                    $inscripcionUrl = route('academico.curso.inscribirse', 18);
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(
                        new \App\Mail\AsignacionCurso($user, $curso, $inscripcionUrl)
                    );

                    \Illuminate\Support\Facades\Log::info("Correo de asignación enviado al usuario {$user->id}");

                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error en asignación de curso 18: ' . $e->getMessage());
                }
            })->delay(now()->addMinute()); // Delay de 1 minuto

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al programar asignación de curso: ' . $e->getMessage());
        }
    }
}
