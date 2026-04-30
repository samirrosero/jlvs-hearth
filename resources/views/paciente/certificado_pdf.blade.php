<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Afiliación</title>
    <style>
        @page { margin: 2cm; }
        body { 
            font-family: 'Helvetica', sans-serif; 
            font-size: 11px; 
            line-height: 1.5; 
            color: #333; 
        }
        
        /* === ESTILOS DEL LOGO (Texto Dinámico) === */
        .header { 
            margin-bottom: 30px; 
            width: 100%;
        }
        .logo-text-container {
            width: 100%;
        }
        /* El estilo principal solicitado: azul, negrita, cursiva */
        .logo-main-name { 
            color: #004a99; /* Azul corporativo */
            font-size: 22px; 
            font-weight: bold; 
            font-style: italic; 
            margin: 0;
            padding: 0;
            line-height: 1;
        }
        /* El estilo del subíndice IPS */
        .logo-main-name sub {
            font-size: 60%; /* Más pequeño que el nombre */
            margin-left: 2px;
            font-style: italic;
        }

        /* === RESTO DEL DISEÑO === */
        .city-date { 
            margin-bottom: 25px; 
            font-size: 11px; 
        }
        
        .recipient { margin-bottom: 25px; }
        .recipient p { margin: 2px 0; font-weight: bold; }

        .reference { 
            margin-bottom: 20px; 
            font-weight: bold; 
        }

        .main-text { 
            text-align: justify; 
            margin-bottom: 15px; 
        }

        /* Tabla Estilo Salud Total */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
            font-size: 9px; 
        }
        th { 
            background-color: #f2f2f2; 
            border: 0.5px solid #aaa; 
            padding: 6px; 
            text-align: center; 
            font-weight: bold;
        }
        td { 
            border: 0.5px solid #aaa; 
            padding: 6px; 
            text-align: center; 
        }

        .warning-box { 
            text-align: center; 
            font-weight: bold; 
            margin: 40px 0; 
            border-top: 1px solid #000; 
            border-bottom: 1px solid #000; 
            padding: 8px 0;
            letter-spacing: 1px;
        }

        .signature { margin-top: 60px; }
        
        .footer-note { 
            font-size: 9px; 
            color: #555; 
            text-align: justify; 
            margin-top: 40px;
            border-top: 0.5px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    {{-- === ENCABEZADO CON LOGO DINÁMICO (TEXTO) === --}}
    <div class="header">
        <div class="logo-text-container">
            <h1 class="logo-main-name">
                {{-- Nombre de la empresa (fallback a 'SaludTotal' si no hay) --}}
                {{ $paciente->empresa->nombre ?? 'SaludTotal' }}<sub>IPS</sub>
            </h1>
        </div>
    </div>

    {{-- === FECHA Y DATOS === --}}
    <div class="city-date">
        {{-- Forzamos Carbon a español para asegurarnos que salga 'abril' y no 'April' --}}
        CALI, {{ \Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e Y') }}
    </div>

    <div class="recipient">
        <p>Señor:</p>
        <p>{{ strtoupper($paciente->nombre_completo) }}</p>
        <p>CC. {{ $paciente->identificacion }}</p>
        <p>Ciudad</p>
    </div>

    <div class="reference">
        Ref: SOLICITUD INFORMACIÓN - AFILIACIÓN EN {{ strtoupper($paciente->empresa->nombre ?? 'NUESTRA INSTITUCIÓN') }}
    </div>

    <div class="main-text">
        En relación con el asunto de la referencia, y atendiendo su solicitud, nos permitimos informarle que a la fecha de expedición de la presente comunicación consta en nuestra base de datos que su afiliación al régimen contributivo se encuentra vigente. Los usuarios inscritos en su afiliación son:
    </div>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Documento</th>
                <th>Tipo</th>
                <th>Afiliación</th>
                <th>Parentesco</th>
                <th>Estado Afiliación</th>
                <th>Estado Actual</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ strtoupper($paciente->nombre_completo) }}</td>
                <td>{{ $paciente->identificacion }}</td>
                <td>{{ $paciente->tipo_documento ?? 'CC' }}</td>
                <td>{{ $paciente->created_at->format('M d-Y') }}</td>
                <td>COTIZANTE</td>
                <td>VIGENTE</td>
                <td>ACTIVO</td>
            </tr>
        </tbody>
    </table>

    <div class="warning-box">
        CARTA NO VALIDA PARA TRASLADO
    </div>

    <div class="main-text">
        En {{ $paciente->empresa->nombre ?? 'nuestra institución' }} apreciamos la confianza que usted ha depositado en nosotros y esperamos que usted y su familia continúen disfrutando de nuestros servicios de salud con calidad total. Cualquier información adicional, con gusto será atendida por el personal de servicio al cliente.
    </div>

    <div class="signature">
        Cordialmente,<br><br><br>
        <strong>Gerencia de Operaciones Comercial</strong><br>
        {{ strtoupper($paciente->empresa->nombre ?? 'IPS S.A.') }}<br>
        <small>Elaboró: Servicios en Línea</small>
    </div>

    <div class="footer-note">
        <strong>NOTA:</strong> En caso requerido, este certificado es válido para la atención a través del Régimen Subsidiado o como población vinculada, si el Estado Actual es afiliación cancelada, novedad de retiro de trabajo, afiliación no efectiva, siempre y cuando al momento del retiro no haya reportado mora.
    </div>
</body>
</html>