<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Curso;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AsignacionCurso extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $curso;
    public $inscripcionUrl;
    public $fechaLimite;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Curso $curso, string $inscripcionUrl, $fechaLimite = null)
    {
        $this->user = $user;
        $this->curso = $curso;
        $this->inscripcionUrl = $inscripcionUrl;
        $this->fechaLimite = $fechaLimite;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Te han asignado un curso - ' . $this->curso->titulo,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.asignacion-curso',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
