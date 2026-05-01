<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Términos y Condiciones de Uso</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }
        .container { max-width: 800px; margin: 0 auto; padding: 40px 24px; }
        h1 { font-size: 1.6rem; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        h2 { font-size: 1.05rem; font-weight: 600; color: #0f172a; margin: 28px 0 8px; }
        p, li { font-size: 0.92rem; line-height: 1.75; color: #475569; }
        ul { padding-left: 20px; margin: 8px 0; }
        .badge { display: inline-block; font-size: 0.75rem; background: #dcfce7; color: #166534; border-radius: 6px; padding: 2px 10px; margin-bottom: 16px; font-weight: 600; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 32px 36px; margin-top: 24px; box-shadow: 0 1px 6px rgba(0,0,0,.04); }
        .footer-back { margin-top: 32px; text-align: initial; }
        .footer-back a { font-size: 0.88rem; color: #0369a1; text-decoration: none; font-weight: 500; }
        .footer-back a:hover { text-decoration: underline; }
        hr { border: none; border-top: 1px solid #e2e8f0; margin: 24px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="footer-back">
            <a href="javascript:window.close()">← Cerrar esta ventana</a>
        </div>
        <div class="card">
            <span class="badge">Portal de Salud · JLVS Hearth</span>
            <h1>Términos y Condiciones de Uso</h1>
            <p><strong>Última actualización:</strong> {{ now()->format('d/m/Y') }}</p>

            <hr>

            <h2>1. Aceptación</h2>
            <p>
                Al registrarse y utilizar este portal, usted acepta los presentes Términos y Condiciones en su totalidad. Si no está de acuerdo, deberá abstenerse de usar el servicio.
            </p>

            <h2>2. Descripción del servicio</h2>
            <p>
                La plataforma <strong>JLVS Hearth</strong> es un sistema de gestión de salud que permite a pacientes agendar citas, acceder a su historia clínica digital y comunicarse con profesionales de la salud de la institución prestadora de servicios.
            </p>

            <h2>3. Uso autorizado</h2>
            <ul>
                <li>El acceso es personal e intransferible. Usted es responsable de la confidencialidad de sus credenciales.</li>
                <li>Solo podrá utilizar el portal con fines lícitos relacionados con su atención en salud.</li>
                <li>Queda prohibido compartir, vender o ceder su cuenta a terceros.</li>
                <li>No está permitido realizar ingeniería inversa, descompilar ni intentar acceder a áreas no autorizadas del sistema.</li>
            </ul>

            <h2>4. Citas médicas y Telemedicina</h2>
            <ul>
                <li>Las citas agendadas deben cancelarse con al menos 24 horas de antelación si no puede asistir.</li>
                <li>Las consultas virtuales (Telemedicina) están sujetas a disponibilidad de conexión y a la confirmación del médico tratante.</li>
                <li>La información clínica compartida en consultas virtuales se trata con la misma confidencialidad que una consulta presencial.</li>
            </ul>

            <h2>5. Responsabilidades del usuario</h2>
            <ul>
                <li>Proporcionar información verídica y actualizada durante el registro y en cada atención.</li>
                <li>Notificar de inmediato cualquier acceso no autorizado a su cuenta.</li>
                <li>No utilizar el portal para difundir contenido ilegal, ofensivo o malicioso.</li>
            </ul>

            <h2>6. Limitación de responsabilidad</h2>
            <p>
                La plataforma actúa como herramienta tecnológica de soporte. La responsabilidad clínica recae exclusivamente en los profesionales de la salud habilitados. El portal no reemplaza la consulta médica presencial cuando esta sea necesaria.
            </p>

            <h2>7. Propiedad intelectual</h2>
            <p>
                Todo el contenido del portal (diseño, código, logotipos, textos) es propiedad de sus respectivos titulares. Queda prohibida su reproducción total o parcial sin autorización expresa.
            </p>

            <h2>8. Modificaciones</h2>
            <p>
                Nos reservamos el derecho de modificar estos términos en cualquier momento. Los cambios serán notificados mediante el portal y entrarán en vigencia en la fecha de publicación.
            </p>

            <h2>9. Ley aplicable</h2>
            <p>
                Los presentes términos se rigen por las leyes de la República de Colombia. Cualquier controversia será resuelta ante los jueces competentes de Colombia.
            </p>
        </div>
    </div>
</body>
</html>
