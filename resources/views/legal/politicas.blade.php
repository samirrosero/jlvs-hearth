<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Tratamiento de Datos Personales</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/accesibilidad.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }

        h2 {
            font-size: 1.05rem;
            font-weight: 600;
            color: #0f172a;
            margin: 28px 0 8px;
        }

        p,
        li {
            font-size: 0.92rem;
            line-height: 1.75;
            color: #475569;
        }

        ul {
            padding-left: 20px;
            margin: 8px 0;
        }

        .badge {
            display: inline-block;
            font-size: 0.75rem;
            background: #dbeafe;
            color: #1d4ed8;
            border-radius: 6px;
            padding: 2px 10px;
            margin-bottom: 16px;
            font-weight: 600;
        }

        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 32px 36px;
            margin-top: 24px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .04);
        }

        .footer-back {
            margin-top: 32px;
            text-align: initial;
        }

        .footer-back a {
            font-size: 0.88rem;
            color: #0369a1;
            text-decoration: none;
            font-weight: 500;
        }

        .footer-back a:hover {
            text-decoration: underline;
        }

        hr {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 24px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="footer-back">
            <a href="javascript:window.close()">← Cerrar esta ventana</a>
        </div>
        <div class="card">
            <span class="badge">Ley 1581 de 2012 · Decreto 1377 de 2013</span>
            <h1>Política de Tratamiento de Datos Personales</h1>
            <p><strong>Última actualización:</strong> {{ now()->format('d/m/Y') }}</p>

            <hr>

            <h2>1. Responsable del tratamiento</h2>
            <p>
                La presente política es aplicada por la institución prestadora de servicios de salud que opera a través
                de la plataforma <strong>JLVS Hearth</strong>, quien actúa como responsable del tratamiento de datos
                personales recolectados mediante este portal.
            </p>

            <h2>2. Datos personales que se recolectan</h2>
            <ul>
                <li>Nombre completo, tipo y número de documento de identidad.</li>
                <li>Fecha de nacimiento, sexo y datos de contacto (correo electrónico, teléfono).</li>
                <li>Información de cobertura en salud (aseguradora, póliza o afiliación).</li>
                <li>Datos clínicos registrados durante la atención médica (historia clínica, signos vitales,
                    diagnósticos, tratamientos, órdenes médicas).</li>
            </ul>

            <h2>3. Finalidades del tratamiento</h2>
            <ul>
                <li>Gestión de citas médicas presenciales y virtuales (Telemedicina).</li>
                <li>Creación, consulta y actualización de historias clínicas.</li>
                <li>Emisión de recetas, certificados y órdenes médicas.</li>
                <li>Comunicación con el paciente sobre el estado de sus trámites.</li>
                <li>Cumplimiento de obligaciones legales ante entidades de salud.</li>
                <li>Generación de reportes estadísticos internos (datos anonimizados).</li>
            </ul>

            <h2>4. Derechos del titular</h2>
            <p>De conformidad con la Ley 1581 de 2012, usted tiene derecho a:</p>
            <ul>
                <li>Conocer, actualizar y rectificar sus datos personales.</li>
                <li>Solicitar prueba de la autorización otorgada.</li>
                <li>Ser informado sobre el uso que se da a sus datos.</li>
                <li>Revocar la autorización y/o solicitar la supresión de sus datos cuando no exista obligación legal de
                    conservarlos.</li>
                <li>Acceder gratuitamente a sus datos personales que hayan sido objeto de tratamiento.</li>
            </ul>

            <h2>5. Transferencia y transmisión de datos</h2>
            <p>
                Sus datos no serán cedidos ni vendidos a terceros. Solo se compartirán con entidades que presten
                servicios dentro de su proceso de atención médica (aseguradoras, laboratorios, entes reguladores),
                siempre bajo acuerdos de confidencialidad y en cumplimiento de la normativa vigente.
            </p>

            <h2>6. Seguridad de la información</h2>
            <p>
                Se aplican medidas técnicas, humanas y administrativas para proteger sus datos contra pérdida, uso
                indebido, acceso no autorizado, divulgación, alteración o destrucción.
            </p>

            <h2>7. Vigencia</h2>
            <p>
                Los datos se conservarán durante el tiempo necesario para cumplir las finalidades descritas y las
                obligaciones legales aplicables, en particular las definidas en la normativa de historia clínica
                (Resolución 1995 de 1999).
            </p>

            <h2>8. Contacto para ejercer derechos</h2>
            <p>
                Para ejercer sus derechos como titular, comuníquese directamente con el área administrativa de la
                institución de salud donde se encuentra registrado.
            </p>
        </div>

    </div>
</body>

</html>
