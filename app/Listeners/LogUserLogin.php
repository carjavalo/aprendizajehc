<?php

namespace App\Listeners;

use App\Models\UserLogin;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Request;

class LogUserLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle successful login events.
     */
    public function handleSuccessfulLogin(Login $event): void
    {
        $this->logLoginAttempt(
            user: $event->user,
            email: $event->user->email,
            status: 'success',
            emailVerified: $event->user->email_verified_at ? 'verified' : 'unverified'
        );
    }

    /**
     * Handle failed login events.
     */
    public function handleFailedLogin(Failed $event): void
    {
        // Buscar usuario por email para determinar estado de verificación
        $user = User::where('email', $event->credentials['email'] ?? '')->first();

        $this->logLoginAttempt(
            user: $user,
            email: $event->credentials['email'] ?? 'unknown',
            status: 'failed',
            emailVerified: $user && $user->email_verified_at ? 'verified' : 'unverified',
            failureReason: 'Credenciales inválidas'
        );
    }

    /**
     * Log the login attempt.
     */
    private function logLoginAttempt(
        ?User $user = null,
        string $email = '',
        string $status = 'failed',
        string $emailVerified = 'unverified',
        ?string $failureReason = null
    ): void {
        UserLogin::create([
            'user_id' => $user?->id,
            'email' => $email,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'status' => $status,
            'email_verified' => $emailVerified,
            'failure_reason' => $failureReason,
            'attempted_at' => now(),
        ]);
    }
}
