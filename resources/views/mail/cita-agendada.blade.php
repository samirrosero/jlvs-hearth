<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; color: #1a1a1a; }
        .container { max-width: 560px; margin: 32px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1e40af; padding: 28px 32px; }
        .header h1 { color: #fff; font-size: 20px; margin: 0; }
        .header p { color: #bfdbfe; font-size: 13px; margin: 4px 0 0; }
        .body { padding: 28px 32px; }
        .greeting { font-size: 15px; margin-bottom: 16px; }
        .card { background: #eff6ff; border-left: 4px solid #1e40af; border-radius: 4px; padding: 16px 20px; margin: 20px 0; }
        .card-row { display: flex; margin-bottom: 8px; font-size: 14px; }
        .card-label { font-weight: bold; color: #374151; min-width: 130px; }
        .card-value { color: #1a1a1a; }
        .note { font-size: 13px; color: #6b7280; margin-top: 20px; line-height: 1.6; }
        .footer { background: #f9fafb; padding: 16px 32px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ $cita->empresa->nombre ?? config('app.name') }}</h1>
        <p>Confirmación de cita médica</p>
    </div>

    <div class="body">
        <p class="greeting">
            Hola, <strong>{{ $cita->paciente->nombre_completo }}</strong>.
            Tu cita ha sido agendada exitosamente. Aquí están los detalles:
        </p>

        <div class="card">
            <div class="card-row">
                <span class="card-label">Fecha:</span>
                <span class="card-value">{{ \Carbon\Carbon::parse($cita->fecha)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</span>
            </div>
            <div class="card-row">
                <span class="card-label">Hora:</span>
                <span class="card-value">{{ \Carbon\Carbon::parse($cita->hora)->format('h:i A') }}</span>
            </div>
            <div class="card-row">
                <span class="card-label">Médico:</span>
                <span class="card-value">{{ $cita->medico->usuario->nombre ?? '—' }}</span>
            </div>
            <div class="card-row">
                <span class="card-label">Especialidad:</span>
                <span class="card-value">{{ $cita->medico->especialidad ?? '—' }}</span>
            </div>
            <div class="card-row">
                <span class="card-label">Modalidad:</span>
                <span class="card-value">{{ $cita->modalidad->nombre ?? '—' }}</span>
            </div>
            @if($cita->portafolio)
            <div class="card-row">
                <span class="card-label">Convenio:</span>
                <span class="card-value">{{ $cita->portafolio->nombre_convenio }}</span>
            </div>
            @endif
            @if($cita->servicio)
            <div class="card-row">
                <span class="card-label">Servicio:</span>
                <span class="card-value">{{ $cita->servicio->nombre }}</span>
            </div>
            @endif
        </div>

        <p class="note">
            Si necesitas cancelar o reprogramar tu cita, comunícate con nosotros con al menos
            24 horas de anticipación.<br><br>
            Este es un correo automático, por favor no respondas a este mensaje.
        </p>
    </div>

    <div class="footer">
        {{ $cita->empresa->nombre ?? config('app.name') }}
        @if($cita->empresa->telefono ?? null)
            &nbsp;·&nbsp; Tel: {{ $cita->empresa->telefono }}
        @endif
        @if($cita->empresa->direccion ?? null)
            &nbsp;·&nbsp; {{ $cita->empresa->direccion }}
        @endif
    </div>
</div>
</body>
</html>
