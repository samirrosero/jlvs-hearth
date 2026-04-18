<?php

namespace App\Mail;

use App\Models\Empresa;
use App\Models\SolicitudEmpleador;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BienvenidaEmpleadorMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public SolicitudEmpleador $solicitud,
        public Empresa $empresa
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "¡Bienvenido a {$this->empresa->nombre}! Tu solicitud fue aprobada",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.bienvenida-empleador',
        );
    }
}
