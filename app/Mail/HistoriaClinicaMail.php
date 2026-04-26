<?php

namespace App\Mail;

use App\Models\HistoriaClinica;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HistoriaClinicaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public HistoriaClinica $historia) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu historia clínica — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.historia-clinica',
        );
    }

    public function attachments(): array
    {
        $historia      = $this->historia;
        $paciente      = $historia->paciente;
        $empresa       = $paciente->empresa;
        $signosVitales = $historia->ejecucionCita?->signosVitales;

        $pdfContent = Pdf::loadView('pdf.historia_clinica', compact(
            'historia',
            'paciente',
            'empresa',
            'signosVitales',
        ))->setPaper('letter', 'portrait')->output();

        $nombreArchivo = 'historia-clinica-' . str_pad($historia->id, 8, '0', STR_PAD_LEFT) . '.pdf';

        return [
            Attachment::fromData(fn () => $pdfContent, $nombreArchivo)
                ->withMime('application/pdf'),
        ];
    }
}
