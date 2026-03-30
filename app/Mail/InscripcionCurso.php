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

class InscripcionCurso extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $curso;
    public $cursoUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Curso $curso, string $cursoUrl)
    {
        $this->user = $user;
        $this->curso = $curso;
        $this->cursoUrl = $cursoUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Inscripción exitosa - ' . $this->curso->nombre,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.inscripcion-curso',
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
