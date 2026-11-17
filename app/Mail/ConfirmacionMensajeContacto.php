<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ConfirmacionMensajeContacto extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $nombre,
        public readonly string $mensaje,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Recibimos tu consulta - Technology Cell');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contacto.confirmacion');
    }
}
