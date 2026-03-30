<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Curso;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionInstructorAsignacion extends Mailable
{
    use Queueable, SerializesModels;

    public $instructor;
    public $estudiante;
    public $curso;

    /**
     * Create a new message instance.
     */
    public function __construct(User $instructor, User $estudiante, Curso $curso)
    {
        $this->instructor = $instructor;
        $this->estudiante = $estudiante;
        $this->curso = $curso;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo estudiante asignado a tu curso',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notificacion-instructor-asignacion',
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
