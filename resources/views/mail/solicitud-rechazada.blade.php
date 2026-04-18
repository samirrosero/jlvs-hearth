<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud rechazada — {{ $empresa->nombre }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); padding: 30px; text-align: center; color: white; border-radius: 10px 10px 0 0; }
        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
        .info-box { background: white; padding: 15px; border-left: 4px solid #ef4444; margin: 20px 0; border-radius: 4px; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #6b7280; }
        .role-badge { display: inline-block; padding: 5px 15px; background: #fee2e2; color: #991b1b; border-radius: 20px; font-size: 14px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($empresa->logo_url)
                <img src="{{ $empresa->logo_url }}" alt="{{ $empresa->nombre }}" style="max-height: 60px; margin-bottom: 15px;">
            @endif
            <h1>Solicitud rechazada</h1>
            <p>{{ $empresa->nombre }}</p>
        </div>
        
        <div class="content">
            <p>Hola <strong>{{ $solicitud->nombre_completo }}</strong>,</p>
            
            <p>Lamentamos informarte que tu solicitud para unirte a <strong>{{ $empresa->nombre }}</strong> ha sido <span style="color: #dc2626; font-weight: 600;">RECHAZADA</span>.</p>
            
            <div class="info-box">
                <p style="margin: 0;"><strong>Rol solicitado:</strong></p>
                <span class="role-badge">
                    @switch($solicitud->rol_solicitado)
                        @case('medico') Médico @break
                        @case('gestor_citas') Gestor de Citas @break
                        @case('administrador') Administrador @break
                        @default {{ $solicitud->rol_solicitado }}
                    @endswitch
                </span>
                
                @if($solicitud->observaciones)
                    <p style="margin: 15px 0 0 0;"><strong>Motivo del rechazo:</strong></p>
                    <p style="margin: 5px 0 0 0; color: #4b5563; background: #fef3c7; padding: 10px; border-radius: 6px;">
                        {{ $solicitud->observaciones }}
                    </p>
                @else
                    <p style="margin: 15px 0 0 0; color: #6b7280;">
                        No se especificó un motivo. Si tienes dudas, contacta al administrador de {{ $empresa->nombre }}.
                    </p>
                @endif
            </div>
            
            <p style="margin-top: 25px; font-size: 14px; color: #6b7280;">
                Si crees que esto fue un error o necesitas más información, puedes contactar al administrador de {{ $empresa->nombre }}.
            </p>
            
            <p style="margin-top: 20px; font-size: 14px; color: #6b7280;">
                Gracias por tu interés en formar parte de nuestro equipo.
            </p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} {{ $empresa->nombre }} — Todos los derechos reservados.</p>
            <p style="font-size: 11px; margin-top: 10px;">
                Este correo fue enviado automáticamente por el sistema de gestión de {{ config('app.name') }}.
            </p>
        </div>
    </div>
</body>
</html>
