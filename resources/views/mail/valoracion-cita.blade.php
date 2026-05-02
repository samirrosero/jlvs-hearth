<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuéntanos tu experiencia</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 40px 20px;
            text-align: center;
        }
        .header img {
            max-height: 50px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 40px;
            color: #334155;
            line-height: 1.6;
        }
        .content h2 {
            color: #1e293b;
            font-size: 20px;
            margin-top: 0;
        }
        .details {
            background-color: #f1f5f9;
            border-radius: 12px;
            padding: 20px;
            margin: 24px 0;
        }
        .details p {
            margin: 8px 0;
            font-size: 14px;
        }
        .details strong {
            color: #475569;
        }
        .button-container {
            text-align: center;
            margin-top: 32px;
        }
        .button {
            display: inline-block;
            padding: 16px 32px;
            background-color: #3b82f6;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            transition: background-color 0.2s;
        }
        .footer {
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            background-color: #f8fafc;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($cita->empresa->logo_url)
                <img src="{{ $cita->empresa->logo_url }}" alt="{{ $cita->empresa->nombre }}">
            @endif
            <h1>¡Gracias por confiar en nosotros!</h1>
        </div>
        <div class="content">
            <h2>Hola, {{ $cita->paciente->nombre_completo }}</h2>
            <p>Esperamos que tu atención médica haya sido satisfactoria. Tu opinión es muy importante para nosotros y nos ayuda a mejorar cada día.</p>
            
            <div class="details">
                <p><strong>Médico:</strong> {{ $cita->medico->usuario->nombre }}</p>
                <p><strong>Especialidad:</strong> {{ $cita->medico->especialidad }}</p>
                <p><strong>Fecha:</strong> {{ $cita->fecha->format('d/m/Y') }}</p>
                <p><strong>Servicio:</strong> {{ $cita->servicio->nombre ?? 'Consulta Médica' }}</p>
            </div>

            <p>¿Podrías dedicarnos un minuto para valorar la atención recibida?</p>

            <p>¿Qué te pareció la atención? Haz clic en una estrella para calificar:</p>

            <div class="stars-container" style="text-align: center; margin: 30px 0;">
                @foreach([1, 2, 3, 4, 5] as $score)
                    <a href="{{ URL::signedRoute('public.valorar.atencion', ['id' => $cita->id, 'puntuacion' => $score]) }}" 
                       style="text-decoration: none; font-size: 40px; color: #c7a34a; margin: 0 5px;">★</a>
                @endforeach
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $cita->empresa->nombre ?? config('app.name') }}. Todos los derechos reservados.</p>
            <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
        </div>
    </div>
</body>
</html>
