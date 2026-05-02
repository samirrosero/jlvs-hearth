<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout — JLVS Hearth</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/accesibilidad.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; font-family: 'Inter', sans-serif; background: #f8fafc; overflow: hidden; }
        .wrap { display: flex; height: 100vh; overflow: hidden; }

        /* ── Panel izquierdo — resumen del pedido ── */
        .panel-left {
            width: 380px; flex-shrink: 0;
            background: linear-gradient(145deg, #0f172a 0%, #1e40af 100%);
            position: relative; overflow: hidden;
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 48px;
        }
        .panel-left-logo { height: 36px; width: auto; object-fit: contain; filter: brightness(0) invert(1); opacity: .9; }
        .panel-left-footer { font-size: 11px; color: rgba(255,255,255,.35); }

        .resumen-title {
            font-size: 10px; font-weight: 700; color: rgba(255,255,255,.45);
            text-transform: uppercase; letter-spacing: .8px; margin-bottom: 20px;
        }
        .resumen-plan-nombre {
            font-size: 26px; font-weight: 800; color: #fff; margin: 0 0 4px;
        }
        .resumen-plan-desc {
            font-size: 13px; color: rgba(255,255,255,.55); margin: 0 0 28px; line-height: 1.5;
        }
        .resumen-precio-wrap {
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
            border-radius: 14px; padding: 18px 20px; margin-bottom: 28px;
        }
        .resumen-precio-label { font-size: 11px; color: rgba(255,255,255,.45); font-weight: 600; margin-bottom: 4px; }
        .resumen-precio {
            display: flex; align-items: flex-end; gap: 4px;
        }
        .resumen-precio .moneda { font-size: 16px; font-weight: 700; color: rgba(255,255,255,.7); margin-bottom: 5px; }
        .resumen-precio .monto { font-size: 36px; font-weight: 900; color: #fff; line-height: 1; }
        .resumen-precio .periodo { font-size: 13px; color: rgba(255,255,255,.45); margin-bottom: 5px; }

        .resumen-features { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
        .resumen-features li {
            display: flex; align-items: center; gap: 10px;
            font-size: 13px; color: rgba(255,255,255,.75);
        }
        .resumen-features li .icon {
            width: 20px; height: 20px; border-radius: 50%;
            background: rgba(255,255,255,.12); display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .resumen-features li .icon svg { width: 10px; height: 10px; stroke: #60a5fa; stroke-width: 3; }

        /* ── Panel derecho — formulario ── */
        .panel-right {
            flex: 1;
            background: #fff;
            display: flex; flex-direction: column;
            padding: 36px 48px 24px;
            overflow-y: auto;
        }
        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            color: #94a3b8; font-size: 12px; font-weight: 600;
            text-decoration: none; margin-bottom: 28px;
        }
        .back-link:hover { color: #475569; }

        .section-title {
            font-size: 10px; font-weight: 700; color: #94a3b8;
            text-transform: uppercase; letter-spacing: .8px;
            margin: 20px 0 12px; padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
        }
        .section-title:first-of-type { margin-top: 0; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .span-2  { grid-column: span 2; }

        .field { margin-bottom: 0; }
        .field label {
            display: block; font-size: 11px; font-weight: 700;
            color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 5px;
        }
        .field select,
        .field input[type="text"],
        .field input[type="email"],
        .field input[type="tel"] {
            width: 100%; padding: 10px 13px;
            border: 1.5px solid #e2e8f0; border-radius: 10px;
            font-size: 13.5px; color: #1e293b; outline: none;
            transition: border-color .2s, box-shadow .2s;
            background: #fff;
        }
        .field select:focus,
        .field input:focus {
            border-color: #1e40af;
            box-shadow: 0 0 0 3px #1e40af22;
        }
        .field input.err, .field select.err { border-color: #f87171; }

        /* Métodos de pago */
        .metodos-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;
        }
        .metodo-btn {
            border: 1.5px solid #e2e8f0; border-radius: 10px;
            padding: 12px 8px; text-align: center; cursor: pointer;
            transition: border-color .2s, background .2s;
            display: flex; flex-direction: column; align-items: center; gap: 6px;
        }
        .metodo-btn input { display: none; }
        .metodo-btn .metodo-icono {
            font-size: 22px; line-height: 1;
        }
        .metodo-btn .metodo-nombre {
            font-size: 11px; font-weight: 700; color: #475569;
            text-transform: uppercase; letter-spacing: .4px;
        }
        .metodo-btn:has(input:checked) {
            border-color: #1e40af;
            background: #eff6ff;
        }
        .metodo-btn:has(input:checked) .metodo-nombre { color: #1e40af; }

        .btn-submit {
            width: 100%; padding: 13px; margin-top: 8px;
            background: #1e40af; color: #fff;
            font-size: 14px; font-weight: 700; letter-spacing: .3px;
            border: none; border-radius: 10px; cursor: pointer;
            transition: opacity .2s, transform .15s;
        }
        .btn-submit:hover { opacity: .9; transform: translateY(-1px); }

        .seguro-badge {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            font-size: 11px; color: #94a3b8; margin-top: 12px;
        }
        .seguro-badge svg { width: 13px; height: 13px; stroke: #94a3b8; }

        .watermark {
            text-align: center; font-size: 11px; color: #cbd5e1; margin-top: 16px;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .watermark img { height: 15px; opacity: .35; }

        @media (max-width: 860px) {
            html, body { overflow: auto; }
            .wrap { height: auto; min-height: 100vh; overflow: auto; flex-direction: column; }
            .panel-left { width: 100%; padding: 28px 20px; }
            .panel-right { padding: 28px 20px; }
            .grid-2 { grid-template-columns: 1fr; }
            .span-2 { grid-column: span 1; }
        }
    </style>
</head>
<body>
<div class="wrap">

    {{-- ── Panel izquierdo — resumen ── --}}
    <div class="panel-left">
        <img src="{{ asset('img/logos/logo1.png') }}" alt="JLVS Hearth" class="panel-left-logo">

        <div>
            <p class="resumen-title">Resumen de tu pedido</p>
            <p class="resumen-plan-nombre">Plan {{ $planInfo['nombre'] }}</p>
            <p class="resumen-plan-desc">
                @if($plan === 'basico')
                    Hasta {{ $planInfo['medicos'] }} médicos y {{ $planInfo['pacientes'] }} pacientes.
                @elseif($plan === 'profesional')
                    Hasta {{ $planInfo['medicos'] }} médicos y pacientes ilimitados. La opción más popular.
                @else
                    Médicos y pacientes ilimitados. Para grandes instituciones.
                @endif
            </p>

            <div class="resumen-precio-wrap">
                <p class="resumen-precio-label">Total mensual</p>
                <div class="resumen-precio">
                    <span class="moneda">$</span>
                    <span class="monto">{{ number_format($planInfo['precio'], 0, ',', '.') }}</span>
                    <span class="periodo">/mes</span>
                </div>
            </div>

            <ul class="resumen-features">
                <li>
                    <span class="icon"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    {{ $planInfo['medicos'] }} médico{{ $planInfo['medicos'] === 'Ilimitados' ? 's ilimitados' : ($planInfo['medicos'] == 1 ? '' : 's') }}
                </li>
                <li>
                    <span class="icon"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    {{ $planInfo['pacientes'] }} paciente{{ $planInfo['pacientes'] === 'Ilimitados' ? 's ilimitados' : 's' }}
                </li>
                <li>
                    <span class="icon"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Gestión de citas e historias
                </li>
                @if($plan !== 'basico')
                <li>
                    <span class="icon"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Reportes PDF y Excel
                </li>
                <li>
                    <span class="icon"><svg fill="none" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                    Chatbot asistente
                </li>
                @endif
            </ul>
        </div>

        <div class="panel-left-footer">JLVS Hearth &copy; {{ date('Y') }} — UNIAJC</div>
    </div>

    {{-- ── Panel derecho — formulario ── --}}
    <div class="panel-right">

        <a href="{{ route('planes.show') }}" class="back-link">
            <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
            Cambiar plan
        </a>

        <h2 class="text-xl font-bold text-slate-800 mb-0.5">Completa tu compra</h2>
        <p class="text-sm text-slate-400 mb-4">Ingresa tus datos de contacto y elige un método de pago</p>

        {{-- Errores --}}
        @if($errors->any())
            <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-red-600 text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}">
            @csrf
            <input type="hidden" name="plan" value="{{ $plan }}">

            {{-- ── Datos de contacto ── --}}
            <p class="section-title">Datos de contacto</p>
            <div class="grid-2">

                <div class="field span-2">
                    <label>Nombre completo <span class="text-red-400">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}"
                           placeholder="Nombre de quien realiza la compra" required
                           class="{{ $errors->has('nombre') ? 'err' : '' }}">
                </div>

                <div class="field">
                    <label>Correo electrónico <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="correo@ejemplo.com" required
                           class="{{ $errors->has('email') ? 'err' : '' }}">
                </div>

                <div class="field">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" value="{{ old('telefono') }}"
                           placeholder="Ej: 3001234567">
                </div>

            </div>

            {{-- ── Método de pago ── --}}
            <p class="section-title">Método de pago</p>

            <div class="metodos-grid">
                <label class="metodo-btn">
                    <input type="radio" name="metodo_pago" value="pse"
                           {{ old('metodo_pago', 'pse') === 'pse' ? 'checked' : '' }}>
                    <span class="metodo-icono">🏦</span>
                    <span class="metodo-nombre">PSE</span>
                </label>
                <label class="metodo-btn">
                    <input type="radio" name="metodo_pago" value="nequi"
                           {{ old('metodo_pago') === 'nequi' ? 'checked' : '' }}>
                    <span class="metodo-icono">📱</span>
                    <span class="metodo-nombre">Nequi</span>
                </label>
                <label class="metodo-btn">
                    <input type="radio" name="metodo_pago" value="tarjeta"
                           {{ old('metodo_pago') === 'tarjeta' ? 'checked' : '' }}>
                    <span class="metodo-icono">💳</span>
                    <span class="metodo-nombre">Tarjeta</span>
                </label>
            </div>

            <div class="span-2" style="margin-top: 20px;">
                <button type="submit" class="btn-submit">
                    Continuar con el registro de mi IPS →
                </button>
            </div>

            <div class="seguro-badge">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Pago seguro · Los datos de tu tarjeta están cifrados
            </div>

        </form>

        <div class="watermark">
            <img src="{{ asset('img/logos/logo1.png') }}" alt="JLVS">
            <span>Powered by <strong>JLVS Hearth</strong></span>
        </div>
    </div>
</div>
</body>
</html>
