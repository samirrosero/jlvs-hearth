<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CredencialesImportacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $nombre;
    public string $correo;
    public string $passwordTemporal;
    public string $rol;

    /**
     * Create a new message instance.
     */
    public function __construct(string $nombre, string $correo, string $passwordTemporal, string $rol)
    {
        $this->nombre = $nombre;
        $this->correo = $correo;
        $this->passwordTemporal = $passwordTemporal;
        $this->rol = $rol;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $empresa = auth()->user()?->empresa;
        
        return $this->subject('🎉 Bienvenido a ' . ($empresa?->nombre ?? 'JLVS Health') . ' - Tus credenciales de acceso')
            ->markdown('emails.credenciales-importacion', [
                'nombre' => $this->nombre,
                'correo' => $this->correo,
                'passwordTemporal' => $this->passwordTemporal,
                'rol' => $this->rol,
                'empresa' => $empresa,
                'urlLogin' => url('/login'),
            ]);
    }
}
