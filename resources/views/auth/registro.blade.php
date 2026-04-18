<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $fv = ($empresa?->favicon_url ?? asset('favicon.ico')) . '?v=' . ($empresa?->updated_at?->timestamp ?? '1');
        $colorAccent  = $empresa?->color_primario ?? '#1e40af';
        $colorSidebar = $empresa?->color_admin    ?? '#1e293b';
    @endphp
    <title>Regístrate — {{ $empresa?->nombre ?? 'JLVS Hearth' }}</title>
    <link rel="icon" href="{{ $fv }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body { height: 100%; margin: 0; background: #f8fafc; overflow: hidden; }
        .wrap { display: flex; height: 100vh; overflow: hidden; }

        /* ── Panel izquierdo ── */
        .panel-left {
            flex: 1;
            position: relative; overflow: hidden;
            display: flex; flex-direction: column; justify-content: flex-end;
        }
        .panel-left-img {
            position: absolute; inset: 0;
            width: 100%; height: 100%; object-fit: cover;
        }
        .panel-left-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,.05) 0%, rgba(0,0,0,.6) 100%);
        }
        .panel-left-fallback {
            position: absolute; inset: 0;
            background: linear-gradient(145deg, {{ $colorSidebar }} 0%, {{ $colorAccent }} 100%);
        }
        .panel-left-content {
            position: relative; z-index: 2;
            padding: 36px 40px; color: #fff;
        }
        .panel-left-content h2 { font-size: 24px; font-weight: 800; line-height: 1.3; margin-bottom: 8px; }
        .panel-left-content p  { font-size: 13px; color: rgba(255,255,255,.7); line-height: 1.6; }
        .panel-left-content .back-link {
            display: inline-flex; align-items: center; gap: 5px;
            color: rgba(255,255,255,.8); font-size: 12px; font-weight: 600;
            text-decoration: none; margin-bottom: 20px;
        }
        .panel-left-content .back-link:hover { color: #fff; }

        /* ── Panel derecho ── */
        .panel-right {
            flex: 1; background: #fff;
            display: flex; flex-direction: column;
            padding: 32px 48px 24px;
            overflow-y: auto;
            height: 100%;
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

        /* ── Grid de campos ── */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .span-2  { grid-column: span 2; }

        /* ── Campo ── */
        .field label {
            display: block; font-size: 11px; font-weight: 700;
            color: #64748b; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 5px;
        }
        .field-row { display: flex; gap: 8px; align-items: stretch; }
        .field-row select { width: 110px !important; flex-shrink: 0; }
        .field-row input  { flex: 1; width: auto !important; min-width: 0; }
        .field select,
        .field input[type="text"],
        .field input[type="email"],
        .field input[type="password"] {
            width: 100%; padding: 10px 13px;
            border: 1.5px solid #e2e8f0; border-radius: 10px;
            font-size: 13.5px; color: #1e293b; outline: none;
            transition: border-color .2s, box-shadow .2s;
            background: #fff; box-sizing: border-box;
        }
        .field select:focus,
        .field input:focus {
            border-color: {{ $colorAccent }};
            box-shadow: 0 0 0 3px {{ $colorAccent }}22;
        }
        .field input.err { border-color: #f87171; }
        .pass-wrap { position: relative; }
        .pass-wrap input { padding-right: 42px !important; }
        .pass-eye {
            position: absolute; right: 11px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: #94a3b8; padding: 0;
        }
        .pass-eye:hover { color: #475569; }

        /* ── Upload zona documento ── */
        .upload-doc {
            border: 2px dashed #e2e8f0; border-radius: 10px;
            padding: 16px; text-align: center; cursor: pointer;
            transition: border-color .2s, background .2s;
        }
        .upload-doc:hover { border-color: {{ $colorAccent }}; background: #f8fafc; }
        .upload-doc input { display: none; }
        #doc-preview { max-height: 80px; margin: 0 auto 8px; display: none; border-radius: 6px; }

        /* ── Botón ── */
        .btn-primary {
            width: 100%; padding: 13px; margin-top: 6px;
            background: {{ $colorAccent }}; color: #fff;
            font-size: 14px; font-weight: 700; letter-spacing: .3px;
            border: none; border-radius: 10px; cursor: pointer;
            transition: opacity .2s, transform .15s;
        }
        .btn-primary:hover { opacity: .9; transform: translateY(-1px); }

        .link-accent { color: {{ $colorAccent }}; font-weight: 600; text-decoration: none; }
        .link-accent:hover { text-decoration: underline; }

        /* ── Nota info ── */
        .nota-info {
            background: #eff6ff; border: 1px solid #bfdbfe;
            border-radius: 10px; padding: 10px 14px;
            font-size: 12px; color: #1e40af; line-height: 1.5;
        }

        /* ── Watermark ── */
        .watermark {
            text-align: center; font-size: 11px; color: #cbd5e1; margin-top: 24px;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .watermark img { height: 15px; opacity: .35; }

        @media (max-width: 860px) {
            .panel-left { display: none; }
            .panel-right { padding: 24px 20px; }
            .grid-2 { grid-template-columns: 1fr; }
            .span-2 { grid-column: span 1; }
            html, body { overflow: auto; }
            .wrap { height: auto; min-height: 100vh; overflow: auto; }
            .panel-right { height: auto; }
        }
    </style>
</head>
<body>
<div class="wrap" x-data="registroPage()">

    {{-- ── Panel izquierdo ── --}}
    <div class="panel-left">
        @if($empresa?->imagen_registro_url)
            <img src="{{ $empresa->imagen_registro_url }}" alt="" class="panel-left-img">
            <div class="panel-left-overlay"></div>
        @else
            <div class="panel-left-fallback"></div>
        @endif
        <div class="panel-left-content">
            <a href="{{ route('login', request()->only(['empresa', 'nit'])) }}" class="back-link">
                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver al inicio
            </a>
            <h2>Crea tu cuenta en<br>{{ $empresa?->nombre ?? 'la IPS' }}</h2>
            <p>{{ $empresa?->slogan_registro ?? 'Regístrate para acceder a tus servicios de salud en línea de forma segura.' }}</p>
        </div>
    </div>

    {{-- ── Panel derecho ── --}}
    <div class="panel-right">

        <h2 class="text-xl font-bold text-slate-800 mb-1">Regístrate</h2>
        <p class="text-sm text-slate-400 mb-5">Selecciona el tipo de usuario</p>

        {{-- Tabs --}}
        <div class="tabs">
            <button type="button" class="tab-btn" :class="tab==='afiliado'&&'on'" @click="tab='afiliado'">
                Afiliados
            </button>
            <button type="button" class="tab-btn" :class="tab==='empleador'&&'on'" @click="tab='empleador'">
                Empleadores
            </button>
        </div>

        {{-- Errores --}}
        @if($errors->any())
            <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-red-600 text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- ══ TAB AFILIADOS ══════════════════════════════════════════════════ --}}
        <div x-show="tab==='afiliado'" x-cloak>
            <form method="POST" action="{{ route('registro.afiliado', request()->only(['empresa', 'nit'])) }}" class="space-y-0">
                @csrf
                <div class="grid-2">

                    {{-- Tipo + Número documento --}}
                    <div class="field span-2">
                        <label>Tipo y número de documento</label>
                        <div class="field-row">
                            <select name="tipo_documento">
                                @foreach(['CC','TI','CE','PP','NUIP','RC'] as $t)
                                    <option value="{{ $t }}" {{ old('tipo_documento')===$t?'selected':'' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="numero_documento" value="{{ old('numero_documento') }}"
                                   placeholder="Número de documento" required>
                        </div>
                    </div>

                    {{-- Nombres --}}
                    <div class="field">
                        <label>Nombres</label>
                        <input type="text" name="nombres" value="{{ old('nombres') }}"
                               placeholder="Nombres" required>
                    </div>

                    {{-- Apellidos --}}
                    <div class="field">
                        <label>Apellidos</label>
                        <input type="text" name="apellidos" value="{{ old('apellidos') }}"
                               placeholder="Apellidos" required>
                    </div>

                    {{-- Correo --}}
                    <div class="field">
                        <label>Correo electrónico</label>
                        <input type="email" name="correo" value="{{ old('correo') }}"
                               placeholder="correo@ejemplo.com" required>
                    </div>

                    {{-- Confirmar correo --}}
                    <div class="field">
                        <label>Confirmar correo</label>
                        <input type="email" name="correo_confirmation"
                               placeholder="Repite tu correo" required>
                    </div>

                    {{-- Contraseña --}}
                    <div class="field">
                        <label>Contraseña</label>
                        <div class="pass-wrap">
                            <input :type="verPass1?'text':'password'" name="password"
                                   placeholder="Mínimo 8 caracteres" required>
                            <button type="button" class="pass-eye" @click="verPass1=!verPass1">
                                <svg x-show="!verPass1" style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="verPass1" style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Confirmar contraseña --}}
                    <div class="field">
                        <label>Confirmar contraseña</label>
                        <div class="pass-wrap">
                            <input :type="verPass2?'text':'password'" name="password_confirmation"
                                   placeholder="Repite tu contraseña" required>
                            <button type="button" class="pass-eye" @click="verPass2=!verPass2">
                                <svg x-show="!verPass2" style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="verPass2" style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="span-2">
                        <button type="submit" class="btn-primary">Registrarme como Afiliado</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══ TAB EMPLEADORES ════════════════════════════════════════════════ --}}
        <div x-show="tab==='empleador'" x-cloak>
            <div class="nota-info mb-4">
                Tu solicitud quedará <strong>pendiente de aprobación</strong> por el administrador de la IPS. Recibirás confirmación una vez sea revisada.
            </div>
            <form method="POST" action="{{ route('registro.empleador', request()->only(['empresa', 'nit'])) }}" enctype="multipart/form-data">
                @csrf
                <div class="grid-2">

                    {{-- Tipo + Número documento --}}
                    <div class="field span-2">
                        <label>Tipo y número de documento</label>
                        <div class="field-row">
                            <select name="tipo_documento">
                                @foreach(['CC','TI','CE','PP','NUIP','RC'] as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="numero_documento" placeholder="Número de documento" required>
                        </div>
                    </div>

                    {{-- Rol --}}
                    <div class="field span-2">
                        <label>Tipo de rol</label>
                        <select name="rol_solicitado" required>
                            <option value="">Selecciona un rol...</option>
                            <option value="medico">Médico</option>
                            <option value="gestor_citas">Gestor de citas</option>
                            <option value="administrador">Administrador</option>
                        </select>
                    </div>

                    {{-- Nombres --}}
                    <div class="field">
                        <label>Nombres</label>
                        <input type="text" name="nombres" placeholder="Nombres" required>
                    </div>

                    {{-- Apellidos --}}
                    <div class="field">
                        <label>Apellidos</label>
                        <input type="text" name="apellidos" placeholder="Apellidos" required>
                    </div>

                    {{-- Departamento --}}
                    <div class="field">
                        <label>Departamento</label>
                        <input type="text" name="departamento" placeholder="Ej: Valle del Cauca">
                    </div>

                    {{-- Municipio --}}
                    <div class="field">
                        <label>Municipio</label>
                        <input type="text" name="municipio" placeholder="Ej: Cali">
                    </div>

                    {{-- Correo --}}
                    <div class="field">
                        <label>Correo electrónico</label>
                        <input type="email" name="correo" placeholder="correo@ejemplo.com" required>
                    </div>

                    {{-- Confirmar correo --}}
                    <div class="field">
                        <label>Confirmar correo</label>
                        <input type="email" name="correo_confirmation" placeholder="Repite tu correo" required>
                    </div>

                    {{-- Contraseña --}}
                    <div class="field">
                        <label>Contraseña</label>
                        <div class="pass-wrap">
                            <input :type="verPass3?'text':'password'" name="password"
                                   placeholder="Mínimo 8 caracteres" required>
                            <button type="button" class="pass-eye" @click="verPass3=!verPass3">
                                <svg x-show="!verPass3" style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="verPass3" style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Confirmar contraseña --}}
                    <div class="field">
                        <label>Confirmar contraseña</label>
                        <div class="pass-wrap">
                            <input :type="verPass4?'text':'password'" name="password_confirmation"
                                   placeholder="Repite tu contraseña" required>
                            <button type="button" class="pass-eye" @click="verPass4=!verPass4">
                                <svg x-show="!verPass4" style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="verPass4" style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Foto del documento ── --}}
                    <div class="field span-2">
                        <label>Foto del documento de identidad</label>
                        <div class="upload-doc" onclick="document.getElementById('foto_documento').click()">
                            <img id="doc-preview" src="" alt="Vista previa">
                            <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="doc-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-xs text-slate-500 font-medium" id="doc-text">Haz clic para subir</p>
                            <p class="text-xs text-slate-400 mt-0.5">JPG, PNG, WEBP · máx. 3 MB</p>
                            <input type="file" id="foto_documento" name="foto_documento"
                                   accept="image/jpg,image/jpeg,image/png,image/webp"
                                   onchange="previewDoc(this)">
                        </div>
                    </div>

                    <div class="span-2">
                        <button type="submit" class="btn-primary">Enviar solicitud</button>
                    </div>
                </div>
            </form>
        </div>

        <p class="text-center text-sm text-slate-400 mt-5">
            ¿Ya tienes cuenta?
            <a href="{{ route('login', request()->only(['empresa', 'nit'])) }}" class="link-accent ml-1">Inicia sesión</a>
        </p>

        <div class="watermark">
            <img src="{{ asset('img/logos/logo1.png') }}" alt="JLVS">
            <span>Powered by <strong>JLVS Hearth</strong></span>
        </div>
    </div>
</div>
<script>
function registroPage() {
    return { tab: 'afiliado', verPass1: false, verPass2: false, verPass3: false, verPass4: false };
}
function previewDoc(input) {
    if (!input.files?.[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('doc-preview');
        const icon = document.getElementById('doc-icon');
        const txt  = document.getElementById('doc-text');
        img.src = e.target.result;
        img.style.display = 'block';
        icon.style.display = 'none';
        txt.textContent = input.files[0].name;
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
</body>
</html>
