<?php

namespace App\Mail;

use App\Models\Prepostulante;
use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PostulanteConfirmado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Usuario $usuario,
        public readonly Prepostulante $prepostulante
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Postulación Confirmada — Bienvenido al CUP FICCT',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.postulante-confirmado',
        );
    }
}
