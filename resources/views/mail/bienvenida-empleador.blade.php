<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a {{ $empresa->nombre }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; color: white; border-radius: 10px 10px 0 0; }
        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; padding: 12px 30px; background: #4f46e5; color: white; text-decoration: none; border-radius: 6px; margin-top: 20px; }
        .info-box { background: white; padding: 15px; border-left: 4px solid #4f46e5; margin: 20px 0; border-radius: 4px; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #6b7280; }
        .role-badge { display: inline-block; padding: 5px 15px; background: #dbeafe; color: #1e40af; border-radius: 20px; font-size: 14px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($empresa->logo_url)
                <img src="{{ $empresa->logo_url }}" alt="{{ $empresa->nombre }}" style="max-height: 60px; margin-bottom: 15px;">
            @endif
            <h1>¡Bienvenido a {{ $empresa->nombre }}!</h1>
            <p>Tu solicitud ha sido aprobada</p>
        </div>
        
        <div class="content">
            <p>Hola <strong>{{ $solicitud->nombre_completo }}</strong>,</p>
            
            <p>Nos complace informarte que tu solicitud para unirte a <strong>{{ $empresa->nombre }}</strong> ha sido <span style="color: #059669; font-weight: 600;">APROBADA</span>.</p>
            
            <div class="info-box">
                <p style="margin: 0;"><strong>Rol asignado:</strong></p>
                <span class="role-badge">
                    @switch($solicitud->rol_solicitado)
                        @case('medico') Médico @break
                        @case('gestor_citas') Gestor de Citas @break
                        @case('administrador') Administrador @break
                        @default {{ $solicitud->rol_solicitado }}
                    @endswitch
                </span>
                
                @if($solicitud->especialidad)
                    <p style="margin: 10px 0 0 0;"><strong>Especialidad:</strong> {{ $solicitud->especialidad }}</p>
                @endif
            </div>
            
            <p><strong>Datos de acceso al sistema:</strong></p>
            <ul>
                <li><strong>Correo:</strong> {{ $solicitud->correo }}</li>
                <li><strong>Contraseña:</strong> La que registraste en tu solicitud</li>
            </ul>
            
            <div style="text-align: center;">
                <a href="{{ route('login') }}" class="button">Acceder al Sistema</a>
            </div>
            
            <p style="margin-top: 25px; font-size: 14px; color: #6b7280;">
                Si tienes alguna pregunta o necesitas ayuda, no dudes en contactar al administrador de {{ $empresa->nombre }}.
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
