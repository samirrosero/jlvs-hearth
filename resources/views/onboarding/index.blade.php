<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registra tu IPS — JLVS Hearth</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/accesibilidad.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; font-family: 'Inter', sans-serif; background: #f8fafc; overflow: hidden; }
        .wrap { display: flex; height: 100vh; overflow: hidden; }

        /* ── Panel izquierdo ── */
        .panel-left {
            flex: 1;
            background: linear-gradient(145deg, #0f172a 0%, #1e40af 100%);
            position: relative; overflow: hidden;
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 48px;
        }
        .panel-left-logo { height: 36px; width: auto; object-fit: contain; filter: brightness(0) invert(1); opacity: .9; }
        .panel-left-content { color: #fff; }
        .panel-left-content .badge {
            display: inline-block; background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2);
            color: rgba(255,255,255,.85); font-size: 11px; font-weight: 600;
            letter-spacing: .5px; padding: 4px 12px; border-radius: 20px; margin-bottom: 20px;
        }
        .panel-left-content h2 { font-size: 32px; font-weight: 800; line-height: 1.2; margin: 0 0 16px; }
        .panel-left-content h2 span { color: #60a5fa; }
        .panel-left-content p { font-size: 14px; color: rgba(255,255,255,.65); line-height: 1.7; max-width: 340px; margin: 0; }
        .steps { margin-top: 40px; display: flex; flex-direction: column; gap: 14px; }
        .step { display: flex; align-items: flex-start; gap: 14px; }
        .step-num {
            width: 28px; height: 28px; border-radius: 50%;
            background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
            color: #fff; font-size: 12px; font-weight: 700;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;
        }
        .step-text { font-size: 13px; color: rgba(255,255,255,.7); line-height: 1.5; }
        .step-text strong { color: #fff; }
        .panel-left-footer { font-size: 11px; color: rgba(255,255,255,.35); }

        /* ── Panel derecho ── */
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

        /* ── Campos ── */
        .field { margin-bottom: 0; }
        .field label {
            display: block; font-size: 11px; font-weight: 700;
            color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 5px;
        }
        .field-row { display: flex; gap: 8px; align-items: stretch; }
        .field-row select { width: 100px !important; flex-shrink: 0; }
        .field-row input  { flex: 1; width: auto !important; min-width: 0; }
        .field select,
        .field input[type="text"],
        .field input[type="email"],
        .field input[type="password"],
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
        .pass-wrap { position: relative; }
        .pass-wrap input { padding-right: 42px; }
        .pass-eye {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: #94a3b8; padding: 0;
        }
        .pass-eye:hover { color: #475569; }

        /* ── Botón ── */
        .btn-submit {
            width: 100%; padding: 13px; margin-top: 8px;
            background: #1e40af; color: #fff;
            font-size: 14px; font-weight: 700; letter-spacing: .3px;
            border: none; border-radius: 10px; cursor: pointer;
            transition: opacity .2s, transform .15s;
        }
        .btn-submit:hover { opacity: .9; transform: translateY(-1px); }

        .watermark {
            text-align: center; font-size: 11px; color: #cbd5e1; margin-top: 20px;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .watermark img { height: 15px; opacity: .35; }

        @media (max-width: 860px) {
            html, body { overflow: auto; }
            .wrap { height: auto; min-height: 100vh; overflow: auto; }
            .panel-left { display: none; }
            .panel-right { padding: 28px 20px; height: auto; }
            .grid-2 { grid-template-columns: 1fr; }
            .span-2 { grid-column: span 1; }
        }
    </style>
</head>
<body>
<div class="wrap" x-data="{ verPass1: false, verPass2: false }">

    {{-- ── Panel izquierdo ── --}}
    <div class="panel-left">
        <img src="{{ asset('img/logos/logo1.png') }}" alt="JLVS Hearth" class="panel-left-logo">

        <div class="panel-left-content">
            @php
                $planSesion = session('plan_seleccionado');
                $planesNombres = ['basico' => 'Básico', 'profesional' => 'Profesional', 'empresarial' => 'Empresarial'];
                $planNombre = $planSesion ? ($planesNombres[$planSesion] ?? null) : null;
            @endphp

            @if($planNombre)
                <div class="badge">Plan {{ $planNombre }} · Nueva IPS</div>
            @else
                <div class="badge">Onboarding · Nueva IPS</div>
            @endif

            <h2>Empieza con<br><span>JLVS Hearth</span><br>hoy mismo</h2>
            <p>Registra tu IPS y en minutos tendrás acceso completo a la plataforma de gestión clínica.</p>

            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <div class="step-text"><strong>Datos de tu IPS</strong><br>Nombre, NIT y ciudad</div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div class="step-text"><strong>Cuenta de administrador</strong><br>Con ella gestionas todo</div>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div class="step-text"><strong>Listo para usar</strong><br>Personaliza y comienza</div>
                </div>
            </div>
        </div>

        <div class="panel-left-footer">JLVS Hearth &copy; {{ date('Y') }} — UNIAJC</div>
    </div>

    {{-- ── Panel derecho ── --}}
    <div class="panel-right">

        <a href="{{ session('plan_seleccionado') ? route('planes.show') : route('home') }}" class="back-link">
            <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ session('plan_seleccionado') ? 'Cambiar plan' : 'Volver al inicio' }}
        </a>

        <h2 class="text-xl font-bold text-slate-800 mb-0.5">Registra tu IPS</h2>
        <p class="text-sm text-slate-400 mb-4">Completa los datos de tu institución y del administrador</p>

        {{-- Errores --}}
        @if($errors->any())
            <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-red-600 text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('onboarding.store') }}">
            @csrf

            {{-- ── Datos de la empresa ── --}}
            <p class="section-title">Datos de la IPS</p>
            <div class="grid-2">

                <div class="field span-2">
                    <label>Nombre de la IPS <span class="text-red-400">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}"
                           placeholder="Ej: Clínica Central del Valle" required
                           class="{{ $errors->has('nombre') ? 'err' : '' }}">
                </div>

                <div class="field">
                    <label>NIT <span class="text-red-400">*</span></label>
                    <input type="text" name="nit" value="{{ old('nit') }}"
                           placeholder="Ej: 900123456-7" required
                           class="{{ $errors->has('nit') ? 'err' : '' }}">
                </div>

                <div class="field" x-data="ubicacionSelector('{{ route('ubicacion.departamentos') }}', '{{ url('ubicacion/municipios') }}')">
                    <label>Departamento</label>
                    <select x-model="depSeleccionado" @change="cargarMunicipios()">
                        <option value="" x-text="departamentos.length ? 'Selecciona un departamento' : 'Cargando...'"></option>
                        <template x-for="dep in departamentos" :key="dep.codigo">
                            <option :value="dep.nombre" x-text="dep.nombre"></option>
                        </template>
                    </select>

                    <label class="mt-3 block">Ciudad / Municipio</label>
                    <select name="ciudad" x-model="munSeleccionado" :disabled="!depSeleccionado || cargandoMun">
                        <option value="" x-text="cargandoMun ? 'Cargando...' : (depSeleccionado ? 'Selecciona un municipio' : 'Selecciona un departamento primero')"></option>
                        <template x-for="mun in municipios" :key="mun">
                            <option :value="mun" x-text="mun"></option>
                        </template>
                    </select>
                </div>

                <div class="field">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" value="{{ old('telefono') }}"
                           placeholder="Ej: 3001234567">
                </div>

                <div class="field">
                    <label>Correo institucional</label>
                    <input type="email" name="correo" value="{{ old('correo') }}"
                           placeholder="contacto@tuips.com">
                </div>

            </div>

            {{-- ── Cuenta del administrador ── --}}
            <p class="section-title">Cuenta del administrador</p>
            <div class="grid-2">

                <div class="field span-2">
                    <label>Nombre completo <span class="text-red-400">*</span></label>
                    <input type="text" name="admin_nombre" value="{{ old('admin_nombre') }}"
                           placeholder="Nombres y apellidos" required
                           class="{{ $errors->has('admin_nombre') ? 'err' : '' }}">
                </div>

                <div class="field span-2">
                    <label>Tipo y número de documento <span class="text-red-400">*</span></label>
                    <div class="field-row">
                        <select name="admin_tipo_documento">
                            @foreach(['CC','TI','CE','PP','NUIP','RC'] as $t)
                                <option value="{{ $t }}" {{ old('admin_tipo_documento', 'CC') === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="admin_identificacion"
                               value="{{ old('admin_identificacion') }}"
                               placeholder="Número de documento" required
                               class="{{ $errors->has('admin_identificacion') ? 'err' : '' }}">
                    </div>
                </div>

                <div class="field span-2">
                    <label>Correo del administrador <span class="text-red-400">*</span></label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}"
                           placeholder="admin@tuips.com" required
                           class="{{ $errors->has('admin_email') ? 'err' : '' }}">
                </div>

                <div class="field">
                    <label>Contraseña <span class="text-red-400">*</span></label>
                    <div class="pass-wrap">
                        <input :type="verPass1 ? 'text' : 'password'" name="admin_password"
                               placeholder="Mínimo 8 caracteres" required
                               class="{{ $errors->has('admin_password') ? 'err' : '' }}">
                        <button type="button" class="pass-eye" @click="verPass1 = !verPass1">
                            <svg x-show="!verPass1" style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="verPass1" style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <div class="field">
                    <label>Confirmar contraseña <span class="text-red-400">*</span></label>
                    <div class="pass-wrap">
                        <input :type="verPass2 ? 'text' : 'password'" name="admin_password_confirmation"
                               placeholder="Repite tu contraseña" required>
                        <button type="button" class="pass-eye" @click="verPass2 = !verPass2">
                            <svg x-show="!verPass2" style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="verPass2" style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <div class="span-2">
                    <button type="submit" class="btn-submit">Registrar mi IPS</button>
                </div>

            </div>
        </form>

        <p class="text-center text-sm text-slate-400 mt-4">
            ¿Ya tienes una cuenta?
            <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:underline ml-1">Iniciar sesión</a>
        </p>

        <div class="watermark">
            <img src="{{ asset('img/logos/logo1.png') }}" alt="JLVS">
            <span>Powered by <strong>JLVS Hearth</strong></span>
        </div>
    </div>
</div>
</body>
</html>
