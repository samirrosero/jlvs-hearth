<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planes — JLVS Hearth</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/accesibilidad.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }

        /* ── Header ── */
        .header {
            position: sticky; top: 0; z-index: 50;
            background: rgba(255,255,255,.9); backdrop-filter: blur(12px);
            border-bottom: 1px solid #e2e8f0;
            padding: 0 48px; height: 64px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .header-logo img { height: 32px; object-fit: contain; }
        .header-back {
            display: inline-flex; align-items: center; gap: 6px;
            color: #64748b; font-size: 13px; font-weight: 600;
            text-decoration: none; transition: color .2s;
        }
        .header-back:hover { color: #1e293b; }
        .header-login {
            font-size: 13px; font-weight: 600; color: #1e40af;
            text-decoration: none;
        }
        .header-login:hover { text-decoration: underline; }

        /* ── Hero ── */
        .hero {
            text-align: center; padding: 72px 24px 48px;
        }
        .hero-eyebrow {
            display: inline-block;
            background: #eff6ff; color: #1e40af;
            font-size: 11px; font-weight: 700; letter-spacing: .7px;
            text-transform: uppercase; padding: 4px 14px; border-radius: 20px;
            margin-bottom: 20px; border: 1px solid #bfdbfe;
        }
        .hero h1 { font-size: 42px; font-weight: 900; margin: 0 0 16px; line-height: 1.15; }
        .hero h1 span { background: linear-gradient(135deg, #1e40af, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero p { font-size: 16px; color: #64748b; max-width: 480px; margin: 0 auto; line-height: 1.7; }

        /* ── Toggle anual/mensual ── */
        .toggle-wrap {
            display: flex; align-items: center; justify-content: center;
            gap: 12px; margin: 36px 0 48px;
        }
        .toggle-label { font-size: 13px; font-weight: 600; color: #64748b; }
        .toggle-label.activo { color: #1e293b; }
        .toggle {
            width: 44px; height: 24px; background: #e2e8f0; border-radius: 99px;
            position: relative; cursor: pointer; border: none; padding: 0;
            transition: background .2s;
        }
        .toggle.on { background: #1e40af; }
        .toggle-dot {
            position: absolute; top: 3px; left: 3px;
            width: 18px; height: 18px; background: #fff; border-radius: 50%;
            transition: transform .2s;
        }
        .toggle.on .toggle-dot { transform: translateX(20px); }
        .toggle-badge {
            background: #dcfce7; color: #16a34a;
            font-size: 10px; font-weight: 700; letter-spacing: .4px;
            padding: 2px 8px; border-radius: 20px; border: 1px solid #bbf7d0;
        }

        /* ── Cards ── */
        .planes-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            max-width: 980px;
            margin: 0 auto;
            padding: 0 24px 80px;
        }
        .plan-card {
            background: #fff; border: 1.5px solid #e2e8f0; border-radius: 20px;
            padding: 28px 28px 24px; display: flex; flex-direction: column;
            transition: box-shadow .2s, transform .2s, border-color .2s;
        }
        .plan-card:hover {
            box-shadow: 0 12px 40px #1e40af18;
            transform: translateY(-4px);
            border-color: #bfdbfe;
        }
        .plan-card.destacado {
            border-color: #1e40af;
            background: linear-gradient(180deg, #eff6ff 0%, #fff 60%);
            box-shadow: 0 8px 32px #1e40af22;
        }
        .plan-badge {
            display: inline-block; align-self: flex-start;
            background: #1e40af; color: #fff;
            font-size: 10px; font-weight: 700; letter-spacing: .5px;
            padding: 3px 10px; border-radius: 20px; margin-bottom: 16px;
            text-transform: uppercase;
        }
        .plan-nombre { font-size: 18px; font-weight: 800; margin: 0 0 6px; }
        .plan-desc { font-size: 13px; color: #64748b; margin: 0 0 20px; line-height: 1.5; }
        .plan-precio {
            display: flex; align-items: flex-end; gap: 4px; margin-bottom: 6px;
        }
        .plan-precio .moneda { font-size: 16px; font-weight: 700; color: #64748b; margin-bottom: 6px; }
        .plan-precio .monto { font-size: 38px; font-weight: 900; line-height: 1; }
        .plan-precio .periodo { font-size: 13px; color: #94a3b8; margin-bottom: 6px; }
        .plan-precio-anual { font-size: 11px; color: #16a34a; font-weight: 600; margin-bottom: 24px; min-height: 16px; }

        .divider { border: none; border-top: 1px solid #f1f5f9; margin: 0 0 20px; }

        .features-list { list-style: none; padding: 0; margin: 0 0 28px; flex: 1; }
        .features-list li {
            display: flex; align-items: flex-start; gap: 10px;
            font-size: 13px; color: #475569; padding: 5px 0;
            line-height: 1.4;
        }
        .features-list li .check {
            width: 18px; height: 18px; border-radius: 50%;
            background: #eff6ff; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; margin-top: 1px;
        }
        .features-list li .check svg { width: 10px; height: 10px; stroke: #1e40af; stroke-width: 3; }
        .features-list li.dimmed { opacity: .4; }
        .features-list li.dimmed .check { background: #f1f5f9; }
        .features-list li.dimmed .check svg { stroke: #94a3b8; }

        .btn-elegir {
            display: block; width: 100%; padding: 12px;
            background: #1e40af; color: #fff;
            font-size: 14px; font-weight: 700; letter-spacing: .2px;
            border: none; border-radius: 10px; cursor: pointer;
            text-decoration: none; text-align: center;
            transition: opacity .2s, transform .15s;
        }
        .btn-elegir:hover { opacity: .88; transform: translateY(-1px); }
        .btn-elegir.outline {
            background: transparent; color: #1e40af;
            border: 1.5px solid #bfdbfe;
        }
        .btn-elegir.outline:hover { background: #eff6ff; }

        /* ── FAQ rápido ── */
        .garantia {
            text-align: center; padding: 0 24px 64px;
            font-size: 13px; color: #94a3b8;
        }
        .garantia strong { color: #475569; }

        /* ── Footer ── */
        .footer {
            border-top: 1px solid #f1f5f9; padding: 24px;
            text-align: center; font-size: 11px; color: #cbd5e1;
        }

        @media (max-width: 860px) {
            .header { padding: 0 20px; }
            .planes-grid { grid-template-columns: 1fr; max-width: 440px; }
            .hero h1 { font-size: 30px; }
        }
    </style>
</head>
<body x-data="{ anual: false }">

    <header class="header">
        <a href="{{ route('home') }}" class="header-back">
            <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver al inicio
        </a>
        <a href="{{ route('home') }}" class="header-logo">
            <img src="{{ asset('img/logos/logo1.png') }}" alt="JLVS Hearth">
        </a>
        <a href="{{ route('login') }}" class="header-login">Iniciar sesión</a>
    </header>

    <section class="hero">
        <div class="hero-eyebrow">Planes y precios</div>
        <h1>Elige el plan ideal<br>para tu <span>IPS</span></h1>
        <p>Sin contratos de permanencia. Cancela cuando quieras. Todos los planes incluyen acceso completo desde el primer día.</p>
    </section>

    {{-- Toggle mensual / anual --}}
    <div class="toggle-wrap">
        <span class="toggle-label" :class="{ activo: !anual }">Mensual</span>
        <button class="toggle" :class="{ on: anual }" @click="anual = !anual" type="button">
            <span class="toggle-dot"></span>
        </button>
        <span class="toggle-label" :class="{ activo: anual }">Anual</span>
        <span class="toggle-badge">Ahorra 20%</span>
    </div>

    {{-- Grilla de planes --}}
    <div class="planes-grid">

        {{-- ── Básico ── --}}
        <div class="plan-card">
            <p class="plan-nombre">Básico</p>
            <p class="plan-desc">Ideal para consultorios pequeños que quieren empezar a digitalizar su gestión.</p>
            <div class="plan-precio">
                <span class="moneda">$</span>
                <span class="monto" x-text="anual ? '120.000' : '150.000'"></span>
                <span class="periodo">/mes</span>
            </div>
            <p class="plan-precio-anual" x-show="anual">Cobrado anualmente · $1.440.000/año</p>
            <p class="plan-precio-anual" x-show="!anual">&nbsp;</p>

            <hr class="divider">

            <ul class="features-list">
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Hasta <strong>3 médicos</strong>
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Hasta <strong>500 pacientes</strong>
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Gestión de citas
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Historias clínicas
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Soporte por correo
                </li>
                <li class="dimmed">
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Reportes PDF y Excel
                </li>
                <li class="dimmed">
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Chatbot asistente
                </li>
            </ul>

            <a href="{{ route('checkout.show') }}?plan=basico"
               class="btn-elegir outline">
                Elegir Básico
            </a>
        </div>

        {{-- ── Profesional (destacado) ── --}}
        <div class="plan-card destacado">
            <span class="plan-badge">Más popular</span>
            <p class="plan-nombre">Profesional</p>
            <p class="plan-desc">Para clínicas en crecimiento que necesitan todo el potencial de la plataforma.</p>
            <div class="plan-precio">
                <span class="moneda">$</span>
                <span class="monto" x-text="anual ? '280.000' : '350.000'"></span>
                <span class="periodo">/mes</span>
            </div>
            <p class="plan-precio-anual" x-show="anual">Cobrado anualmente · $3.360.000/año</p>
            <p class="plan-precio-anual" x-show="!anual">&nbsp;</p>

            <hr class="divider">

            <ul class="features-list">
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Hasta <strong>15 médicos</strong>
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    <strong>Pacientes ilimitados</strong>
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Gestión de citas
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Historias clínicas
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Reportes PDF y Excel
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Chatbot asistente
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Soporte prioritario
                </li>
            </ul>

            <a href="{{ route('checkout.show') }}?plan=profesional"
               class="btn-elegir">
                Elegir Profesional
            </a>
        </div>

        {{-- ── Empresarial ── --}}
        <div class="plan-card">
            <p class="plan-nombre">Empresarial</p>
            <p class="plan-desc">Para grandes instituciones que exigen capacidad sin límites y soporte total.</p>
            <div class="plan-precio">
                <span class="moneda">$</span>
                <span class="monto" x-text="anual ? '600.000' : '750.000'"></span>
                <span class="periodo">/mes</span>
            </div>
            <p class="plan-precio-anual" x-show="anual">Cobrado anualmente · $7.200.000/año</p>
            <p class="plan-precio-anual" x-show="!anual">&nbsp;</p>

            <hr class="divider">

            <ul class="features-list">
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    <strong>Médicos ilimitados</strong>
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    <strong>Pacientes ilimitados</strong>
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Todo lo del plan Profesional
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Personalización avanzada
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Soporte 24/7
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Multi-sede <span style="font-size:10px;color:#94a3b8;margin-left:4px">Próximamente</span>
                </li>
                <li>
                    <span class="check"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Gestor de cuenta dedicado
                </li>
            </ul>

            <a href="{{ route('checkout.show') }}?plan=empresarial"
               class="btn-elegir outline">
                Elegir Empresarial
            </a>
        </div>

    </div>

    <div class="garantia">
        <strong>Garantía de 14 días</strong> · Si no estás satisfecho, te devolvemos tu dinero sin preguntas.<br>
        Todos los precios en COP. IVA no incluido.
    </div>

    <footer class="footer">
        JLVS Hearth &copy; {{ date('Y') }} &mdash; Desarrollado por estudiantes de <strong>UNIAJC</strong>
    </footer>

</body>
</html>
