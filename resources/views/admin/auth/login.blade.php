<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $fv = ($empresa?->favicon_url ?? asset('favicon.ico')) . '?v=' . ($empresa?->updated_at?->timestamp ?? '1');
        $colorAccent = $empresa?->color_primario ?? '#1e40af';
        $colorSidebar = $empresa?->color_admin   ?? '#1e293b';
    @endphp
    <title>Ingresar — {{ $empresa?->nombre ?? 'JLVS Hearth' }}</title>
    <link rel="icon" href="{{ $fv }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ $fv }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body { height: 100%; margin: 0; background: #f8fafc; overflow: hidden; }
        .wrap { display: flex; height: 100vh; overflow: hidden; }

        /* ── Panel izquierdo (foto o gradiente) ── */
        .panel-left {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
        .panel-left-img {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            object-fit: cover;
        }
        .panel-left-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(0,0,0,.08) 0%,
                rgba(0,0,0,.55) 100%
            );
        }
        .panel-left-fallback {
            position: absolute; inset: 0;
            background: linear-gradient(145deg, {{ $colorSidebar }} 0%, {{ $colorAccent }} 100%);
        }
        .panel-left-content {
            position: relative;
            z-index: 2;
            padding: 40px 48px;
            color: #fff;
        }
        .panel-left-content h2 {
            font-size: 28px; font-weight: 800;
            line-height: 1.25; margin-bottom: 10px;
        }
        .panel-left-content p {
            font-size: 14px; color: rgba(255,255,255,.75);
            max-width: 340px; line-height: 1.6;
        }

        /* ── Panel derecho ── */
        .panel-right {
            flex: 1; min-width: 340px;
            background: #fff;
            display: flex; flex-direction: column;
            justify-content: space-between;
            padding: 44px 48px 28px;
            overflow-y: auto;
            box-shadow: -4px 0 32px rgba(0,0,0,.06);
        }

        /* ── Tabs ── */
        .tabs { display: flex; border-bottom: 2px solid #f1f5f9; margin-bottom: 24px; }
        .tab-btn {
            flex: 1; padding: 9px 0; font-size: 13px; font-weight: 600;
            color: #94a3b8; background: none; border: none;
            border-bottom: 2px solid transparent; margin-bottom: -2px;
            cursor: pointer; transition: color .2s, border-color .2s;
        }
        .tab-btn.on { color: {{ $colorAccent }}; border-bottom-color: {{ $colorAccent }}; }

        /* ── Campos ── */
        .field { margin-bottom: 14px; }
        .field label {
            display: block; font-size: 11px; font-weight: 700;
            color: #64748b; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 5px;
        }
        .field select,
        .field input[type="text"],
        .field input[type="password"],
        .field input[type="email"] {
            width: 100%; padding: 11px 13px;
            border: 1.5px solid #e2e8f0; border-radius: 10px;
            font-size: 14px; color: #1e293b; outline: none;
            transition: border-color .2s, box-shadow .2s;
            background: #fff; box-sizing: border-box;
            appearance: auto;
        }
        /* Fila tipo doc + número: el select es fijo, el input ocupa el resto */
        .field-row {
            display: flex; gap: 8px; align-items: stretch;
        }
        .field-row select {
            width: 90px !important; flex-shrink: 0;
        }
        .field-row input {
            flex: 1; width: auto !important; min-width: 0;
        }
        .field select:focus,
        .field input:focus {
            border-color: {{ $colorAccent }};
            box-shadow: 0 0 0 3px {{ $colorAccent }}22;
        }
        .field input.err { border-color: #f87171; }
        .pass-wrap { position: relative; }
        .pass-wrap input { padding-right: 42px; }
        .pass-eye {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: #94a3b8; padding: 0;
        }
        .pass-eye:hover { color: #475569; }

        /* ── Botón ── */
        .btn-primary {
            width: 100%; padding: 13px; margin-top: 4px;
            background: {{ $colorAccent }}; color: #fff;
            font-size: 14px; font-weight: 700; letter-spacing: .3px;
            border: none; border-radius: 10px; cursor: pointer;
            transition: opacity .2s, transform .15s;
        }
        .btn-primary:hover { opacity: .9; transform: translateY(-1px); }

        /* ── Links ── */
        .link-accent { color: {{ $colorAccent }}; font-weight: 600; text-decoration: none; }
        .link-accent:hover { text-decoration: underline; }

        /* ── Watermark ── */
        .watermark {
            text-align: center; font-size: 11px; color: #cbd5e1;
            display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 20px;
        }
        .watermark img { height: 15px; opacity: .35; }

        @media (max-width: 768px) {
            .panel-left { display: none; }
            .panel-right { width: 100%; min-width: 0; padding: 32px 24px 24px; }
            html, body { overflow: auto; }
            .wrap { height: auto; min-height: 100vh; overflow: auto; }
        }
    </style>
</head>
<body>
<div class="wrap" x-data="loginPage()">

    {{-- ── Panel izquierdo ── --}}
    <div class="panel-left">
        @if($empresa?->imagen_login_url)
            <img src="{{ $empresa->imagen_login_url }}" alt="" class="panel-left-img">
            <div class="panel-left-overlay"></div>
        @else
            <div class="panel-left-fallback"></div>
        @endif
        <div class="panel-left-content">
            <h2>Bienvenido a<br>{{ $empresa?->nombre ?? 'tu IPS' }}</h2>
            <p>{{ $empresa?->slogan_login ?? 'Ingresa de forma segura para gestionar tu información de salud, citas y más.' }}</p>
        </div>
    </div>

    {{-- ── Panel derecho ── --}}
    <div class="panel-right">
        <div>
            {{-- Logo IPS --}}
            <div class="mb-4 text-center">
                <img src="{{ $empresa?->logo_url ?? asset('img/logos/logo1.png') }}"
                     alt="{{ $empresa?->nombre }}" class="mx-auto h-10 w-auto object-contain">
            </div>

            <h2 class="text-xl font-bold text-slate-800 mb-1">Ingresa</h2>
            <p class="text-sm text-slate-400 mb-5">Selecciona tu tipo de usuario</p>

            {{-- Tabs --}}
            <div class="tabs">
                <button type="button" class="tab-btn" :class="tab==='afiliado'&&'on'" @click="tab='afiliado'">
                    Afiliados
                </button>
                <button type="button" class="tab-btn" :class="tab==='empleador'&&'on'" @click="tab='empleador'">
                    Empleadores
                </button>
            </div>

            {{-- Mensaje de éxito (registro previo) --}}
            @if(session('exito'))
                <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">
                    {{ session('exito') }}
                </div>
            @endif

            {{-- Error --}}
            @if($errors->any())
                <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-red-600 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Formulario único (ambos tabs usan el mismo endpoint) --}}
            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <input type="hidden" name="tipo_usuario" :value="tab">

                {{-- Correo o número de documento --}}
                <div class="field">
                    <label>Correo o número de identificación</label>
                    <input type="text" name="login"
                           value="{{ old('login') }}"
                           placeholder="correo@ejemplo.com o número de documento"
                           required autofocus autocomplete="username"
                           class="{{ $errors->has('login') ? 'err' : '' }}">
                </div>

                {{-- Contraseña --}}
                <div class="field">
                    <div class="flex justify-between items-center mb-1" style="margin-bottom:5px">
                        <label style="margin-bottom:0">Contraseña</label>
                        <a href="{{ route('forgot-password') }}" class="link-accent" style="font-size:11px">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                    <div class="pass-wrap">
                        <input :type="verPass ? 'text' : 'password'"
                               name="password" placeholder="••••••••" required>
                        <button type="button" class="pass-eye" @click="verPass = !verPass">
                            <svg x-show="!verPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="verPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2 mb-4">
                    <input type="checkbox" name="remember" id="rem"
                           class="rounded" style="accent-color:{{ $colorAccent }}">
                    <label for="rem" class="text-sm text-slate-500 cursor-pointer select-none">Recordarme</label>
                </div>

                <button type="submit" class="btn-primary">Ingresar</button>
            </form>

            {{-- Registrarse --}}
            <p class="text-center text-sm text-slate-400 mt-5">
                ¿Eres nuevo?
                @php
                    $registroParams = [];
                    if (request()->filled('empresa')) $registroParams['empresa'] = request('empresa');
                    if (request()->filled('nit')) $registroParams['nit'] = request('nit');
                @endphp
                <a href="{{ route('registro.show', $registroParams) }}" class="link-accent ml-1">Regístrate</a>
            </p>
        </div>

        {{-- Watermark JLVS ── marca de agua discreta ── --}}
        <div class="watermark">
            <img src="{{ asset('img/logos/logo1.png') }}" alt="JLVS">
            <span>Powered by <strong>JLVS Hearth</strong></span>
        </div>
    </div>
</div>
<script>
function loginPage() {
    return { tab: 'afiliado', verPass: false };
}
</script>
</body>
</html>
