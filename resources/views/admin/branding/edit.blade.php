@extends('admin.layouts.app')

@section('title', 'Identidad Visual')
@section('page-title', 'Identidad Visual')

@push('styles')
<style>
    /* ── Color picker ── */
    input[type="color"].cpick {
        width: 40px; height: 40px;
        border: none; border-radius: 10px;
        padding: 2px; cursor: pointer;
        background: none; flex-shrink: 0;
    }
    input[type="color"].cpick::-webkit-color-swatch-wrapper { padding: 0; border-radius: 8px; }
    input[type="color"].cpick::-webkit-color-swatch { border: none; border-radius: 8px; }

    /* ── Upload zone ── */
    .upload-zone {
        border: 2px dashed #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
    }
    .upload-zone:hover { border-color: #9ca3af; background: #f9fafb; }

    /* ── Preview sidebar ── */
    .sb-preview {
        width: 160px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0,0,0,.18);
        flex-shrink: 0;
    }
    .sb-preview-head {
        padding: 10px 12px 8px;
        border-bottom: 1px solid rgba(255,255,255,.12);
        display: flex; align-items: center; gap: 8px;
    }
    .sb-preview-logo {
        width: 26px; height: 26px; border-radius: 5px;
        object-fit: contain; background: rgba(255,255,255,.15);
    }
    .sb-preview-title {
        font-size: 9.5px; font-weight: 700;
        color: rgba(255,255,255,.85);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        max-width: 85px;
    }
    .sb-nav-item {
        margin: 3px 8px;
        padding: 6px 10px;
        border-radius: 7px;
        font-size: 10px;
        display: flex; align-items: center; gap: 7px;
        color: rgba(255,255,255,.6);
    }
    .sb-nav-item.on { background: rgba(255,255,255,.18); color: #fff; }
    .sb-dot { width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,.35); flex-shrink:0; }
    .sb-dot.on { background: #fff; }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto" x-data="branding()">

    <form method="POST" action="{{ route('admin.branding.update') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        {{-- ══ FILA 0: Textos de pantallas públicas ════════════════════════════ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-800">Textos de bienvenida</h2>
                <p class="text-xs text-gray-400 mt-0.5">Mensaje que aparece en el panel izquierdo de las pantallas de ingreso y registro. Si lo dejas vacío se usa el texto por defecto.</p>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Slogan pantalla de ingreso</label>
                    <textarea name="slogan_login" rows="3"
                              class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700 outline-none focus:border-blue-400 resize-none"
                              placeholder="Ingresa de forma segura para gestionar tu información de salud, citas y más.">{{ old('slogan_login', $empresa->slogan_login) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Slogan pantalla de registro</label>
                    <textarea name="slogan_registro" rows="3"
                              class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700 outline-none focus:border-blue-400 resize-none"
                              placeholder="Regístrate para acceder a tus servicios de salud en línea de forma segura.">{{ old('slogan_registro', $empresa->slogan_registro) }}</textarea>
                </div>
            </div>
        </div>

        {{-- ══ FILA 1: Imágenes ══════════════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-800">Imágenes</h2>
                <p class="text-xs text-gray-400 mt-0.5">Logo que aparece en el sidebar y encabezado de documentos. Favicon que aparece en la pestaña del navegador.</p>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- Logo --}}
                <div>
                    <p class="text-xs font-medium text-gray-600 mb-3">Logo principal</p>
                    <div class="upload-zone" onclick="document.getElementById('logo-input').click()">
                        <img id="logo-preview" src="{{ $empresa->logo_url }}"
                             alt="Logo" class="h-14 mx-auto object-contain mb-3">
                        <p class="text-xs text-gray-500 font-medium">Haz clic para cambiar</p>
                        <p class="text-xs text-gray-400 mt-0.5">PNG, JPG, SVG, WEBP · máx. 2 MB</p>
                    </div>
                    <input type="file" name="logo" id="logo-input" accept="image/*" class="hidden"
                           onchange="previewImg(this,'logo-preview')">
                </div>

                {{-- Favicon --}}
                <div>
                    <p class="text-xs font-medium text-gray-600 mb-3">Favicon <span class="text-gray-400 font-normal">(ícono de pestaña)</span></p>
                    <div class="upload-zone" onclick="document.getElementById('favicon-input').click()">
                        <img id="favicon-preview" src="{{ $empresa->favicon_url }}"
                             alt="Favicon" class="h-14 mx-auto object-contain mb-3">
                        <p class="text-xs text-gray-500 font-medium">Haz clic para cambiar</p>
                        <p class="text-xs text-gray-400 mt-0.5">PNG, ICO, SVG · máx. 512 KB · cuadrado ideal</p>
                    </div>
                    <input type="file" name="favicon" id="favicon-input" accept="image/png,image/svg+xml,.ico" class="hidden"
                           onchange="previewImg(this,'favicon-preview')">
                </div>

                {{-- Imagen login --}}
                <div>
                    <p class="text-xs font-medium text-gray-600 mb-3">Foto panel de <span class="font-semibold">login</span></p>
                    <div class="upload-zone" onclick="document.getElementById('img-login-input').click()">
                        @if($empresa->imagen_login_url)
                            <img id="img-login-preview" src="{{ $empresa->imagen_login_url }}"
                                 alt="Imagen login" class="h-20 mx-auto object-cover rounded-lg mb-3">
                        @else
                            <img id="img-login-preview" src="" alt=""
                                 class="h-20 mx-auto object-cover rounded-lg mb-3 hidden">
                            <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                            </svg>
                        @endif
                        <p class="text-xs text-gray-500 font-medium">Haz clic para subir</p>
                        <p class="text-xs text-gray-400 mt-0.5">PNG, JPG, WEBP · máx. 4 MB</p>
                    </div>
                    <input type="file" name="imagen_login" id="img-login-input" accept="image/*" class="hidden"
                           onchange="previewImg(this,'img-login-preview')">
                </div>

                {{-- Imagen registro --}}
                <div>
                    <p class="text-xs font-medium text-gray-600 mb-3">Foto panel de <span class="font-semibold">registro</span></p>
                    <div class="upload-zone" onclick="document.getElementById('img-registro-input').click()">
                        @if($empresa->imagen_registro_url)
                            <img id="img-registro-preview" src="{{ $empresa->imagen_registro_url }}"
                                 alt="Imagen registro" class="h-20 mx-auto object-cover rounded-lg mb-3">
                        @else
                            <img id="img-registro-preview" src="" alt=""
                                 class="h-20 mx-auto object-cover rounded-lg mb-3 hidden">
                            <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                            </svg>
                        @endif
                        <p class="text-xs text-gray-500 font-medium">Haz clic para subir</p>
                        <p class="text-xs text-gray-400 mt-0.5">PNG, JPG, WEBP · máx. 4 MB</p>
                    </div>
                    <input type="file" name="imagen_registro" id="img-registro-input" accept="image/*" class="hidden"
                           onchange="previewImg(this,'img-registro-preview')">
                </div>

            </div>
        </div>

        {{-- ══ FILA 2: Colores + Preview ═══════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-800">Paleta de colores</h2>
                <p class="text-xs text-gray-400 mt-0.5">Cada vista del sistema tiene su propio color de sidebar. Los PDFs y reportes usan el color de documentos.</p>
            </div>
            <div class="p-6 flex flex-col lg:flex-row gap-8">

                {{-- Controles --}}
                <div class="flex-1 space-y-5">

                    {{-- Documentos --}}
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Documentos</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @php
                                $colores = [
                                    'color_primario'   => ['label' => 'Color primario',    'desc' => 'Botones y acentos',         'model' => ''],
                                    'color_pdf'        => ['label' => 'Color documentos',  'desc' => 'PDFs, reportes, fórmulas',  'model' => ''],
                                ];
                            @endphp
                            @foreach($colores as $key => $c)
                            <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 px-3 py-3">
                                <div class="relative">
                                    <input type="color" name="{{ $key }}" value="{{ $empresa->$key }}"
                                           id="cp-{{ $key }}" class="cpick"
                                           @input="vals['{{ $key }}'] = $event.target.value">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-700 leading-tight">{{ $c['label'] }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $c['desc'] }}</p>
                                </div>
                                <code class="text-[10px] text-gray-400 tabular-nums" x-text="vals['{{ $key }}']"></code>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Sidebars por rol --}}
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Sidebar por vista</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @php
                                $sidebars = [
                                    'color_admin'    => ['label' => 'Administrador',    'desc' => 'Panel de administración'],
                                    'color_doctor'   => ['label' => 'Médico',           'desc' => 'Panel del doctor'],
                                    'color_gestor'   => ['label' => 'Gestor de citas',  'desc' => 'Panel de gestión'],
                                    'color_paciente' => ['label' => 'Paciente',         'desc' => 'Portal del paciente'],
                                ];
                            @endphp
                            @foreach($sidebars as $key => $c)
                            <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 px-3 py-3">
                                <input type="color" name="{{ $key }}" value="{{ $empresa->$key }}"
                                       id="cp-{{ $key }}" class="cpick"
                                       @input="vals['{{ $key }}'] = $event.target.value">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-700 leading-tight">{{ $c['label'] }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $c['desc'] }}</p>
                                </div>
                                <code class="text-[10px] text-gray-400 tabular-nums" x-text="vals['{{ $key }}']"></code>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- Preview live --}}
                <div class="flex flex-col items-center gap-2 pt-1">
                    <p class="text-[11px] text-gray-400 font-medium">Vista previa</p>

                    {{-- Selector de qué rol previsualizar --}}
                    <div class="flex gap-1 mb-1">
                        @foreach(['color_admin' => 'Admin', 'color_doctor' => 'Médico', 'color_gestor' => 'Gestor', 'color_paciente' => 'Paciente'] as $k => $lbl)
                        <button type="button"
                                class="text-[10px] px-2 py-0.5 rounded-full border transition"
                                :class="previewRol === '{{ $k }}'
                                    ? 'border-gray-800 bg-gray-800 text-white'
                                    : 'border-gray-200 text-gray-500 hover:border-gray-400'"
                                @click="previewRol = '{{ $k }}'">
                            {{ $lbl }}
                        </button>
                        @endforeach
                    </div>

                    <div class="sb-preview" :style="'background-color:' + vals[previewRol]">
                        <div class="sb-preview-head">
                            <img id="sb-logo" src="{{ $empresa->logo_url }}" class="sb-preview-logo" alt="">
                            <span class="sb-preview-title">{{ $empresa->nombre }}</span>
                        </div>
                        <div class="py-2">
                            <div class="sb-nav-item on"><div class="sb-dot on"></div>Dashboard</div>
                            <div class="sb-nav-item"><div class="sb-dot"></div>Pacientes</div>
                            <div class="sb-nav-item"><div class="sb-dot"></div>Médicos</div>
                            <div class="sb-nav-item"><div class="sb-dot"></div>Reportes</div>
                        </div>
                    </div>

                    <p class="text-[10px] text-gray-300 mt-1">Los cambios se aplican al guardar</p>
                </div>

            </div>
        </div>

        {{-- ══ FILA 3: Iconos del Panel Administrativo ═════════════════════════ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-800">Iconos del panel administrativo</h2>
                <p class="text-xs text-gray-400 mt-0.5">Personaliza los iconos del menú lateral y las tarjetas del dashboard. Formatos: PNG, SVG o WEBP.</p>
            </div>
            <div class="p-6 space-y-8">

                {{-- Iconos del Sidebar --}}
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Menú lateral (Sidebar)</p>
                    <p class="text-xs text-gray-500 mb-4">Tamaño recomendado: 24x24 o 32x32 píxeles</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                        @php
                            $iconosSidebar = [
                                'icono_dashboard'   => ['label' => 'Dashboard',        'preview' => $empresa->icono_dashboard_url],
                                'icono_pacientes'   => ['label' => 'Pacientes',        'preview' => $empresa->icono_pacientes_url],
                                'icono_medicos'     => ['label' => 'Médicos',          'preview' => $empresa->icono_medicos_url],
                                'icono_reportes'    => ['label' => 'Reportes',         'preview' => $empresa->icono_reportes_url],
                                'icono_solicitudes' => ['label' => 'Solicitudes',      'preview' => $empresa->icono_solicitudes_url],
                                'icono_identidad'   => ['label' => 'Identidad Visual', 'preview' => $empresa->icono_identidad_url],
                                'icono_horarios'    => ['label' => 'Horarios',         'preview' => $empresa->icono_horarios_url],
                                'icono_servicios'   => ['label' => 'Servicios',        'preview' => $empresa->icono_servicios_url],
                                'icono_convenios'   => ['label' => 'Convenios',        'preview' => $empresa->icono_convenios_url],
                                'icono_auditoria'   => ['label' => 'Auditoría',        'preview' => $empresa->icono_auditoria_url],
                            ];
                        @endphp
                        @foreach($iconosSidebar as $key => $info)
                        <div>
                            <p class="text-xs font-medium text-gray-600 mb-2">{{ $info['label'] }}</p>
                            <div class="upload-zone-sm" onclick="document.getElementById('{{ $key }}-input').click()">
                                @if($info['preview'])
                                    <img id="{{ $key }}-preview" src="{{ $info['preview'] }}" alt="{{ $info['label'] }}" class="h-8 w-8 mx-auto object-contain">
                                @else
                                    <img id="{{ $key }}-preview" src="" alt="" class="h-8 w-8 mx-auto object-contain hidden">
                                    <svg class="w-6 h-6 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                                    </svg>
                                @endif
                            </div>
                            <input type="file" name="{{ $key }}" id="{{ $key }}-input" accept="image/png,image/svg+xml,image/webp" class="hidden"
                                   onchange="previewIcon(this,'{{ $key }}-preview')">
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Iconos de las Tarjetas del Dashboard --}}
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Tarjetas del Dashboard</p>
                    <p class="text-xs text-gray-500 mb-4">Tamaño recomendado: 40x40 o 48x48 píxeles. Se usan en las estadísticas del inicio.</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @php
                            $iconosCards = [
                                'icono_card_pacientes' => ['label' => 'Pacientes', 'preview' => $empresa->icono_card_pacientes_url],
                                'icono_card_medicos'   => ['label' => 'Médicos',   'preview' => $empresa->icono_card_medicos_url],
                                'icono_card_citas'     => ['label' => 'Citas',     'preview' => $empresa->icono_card_citas_url],
                                'icono_card_total'     => ['label' => 'Total',     'preview' => $empresa->icono_card_total_url],
                            ];
                        @endphp
                        @foreach($iconosCards as $key => $info)
                        <div>
                            <p class="text-xs font-medium text-gray-600 mb-2">{{ $info['label'] }}</p>
                            <div class="upload-zone-sm" onclick="document.getElementById('{{ $key }}-input').click()">
                                @if($info['preview'])
                                    <img id="{{ $key }}-preview" src="{{ $info['preview'] }}" alt="{{ $info['label'] }}" class="h-10 w-10 mx-auto object-contain">
                                @else
                                    <img id="{{ $key }}-preview" src="" alt="" class="h-10 w-10 mx-auto object-contain hidden">
                                    <svg class="w-6 h-6 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                                    </svg>
                                @endif
                            </div>
                            <input type="file" name="{{ $key }}" id="{{ $key }}-input" accept="image/png,image/svg+xml,image/webp" class="hidden"
                                   onchange="previewIcon(this,'{{ $key }}-preview')">
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        <style>
            .upload-zone-sm {
                border: 2px dashed #e5e7eb;
                border-radius: 0.75rem;
                padding: 12px;
                text-align: center;
                cursor: pointer;
                transition: all 0.2s;
            }
            .upload-zone-sm:hover {
                border-color: #9ca3af;
                background: #f9fafb;
            }
        </style>

        {{-- ══ FILA 4: Iconos del Portal del Paciente ═════════════════════════ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-800">Iconos del portal del paciente</h2>
                <p class="text-xs text-gray-400 mt-0.5">Iconos exclusivos del menú lateral que ve el paciente. Independientes del panel administrativo. PNG, SVG o WEBP · máx. 512 KB.</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @php
                        $iconosPacPortal = [
                            'icono_pac_inicio'    => ['label' => 'Inicio',       'sub' => 'Dashboard del paciente',  'preview' => $empresa->icono_pac_inicio_url],
                            'icono_pac_citas'     => ['label' => 'Mis Citas',    'sub' => 'Mis Citas y Agendar',     'preview' => $empresa->icono_pac_citas_url],
                            'icono_pac_historial' => ['label' => 'Mi Historial', 'sub' => 'Historial clínico',       'preview' => $empresa->icono_pac_historial_url],
                            'icono_pac_perfil'    => ['label' => 'Mi Perfil',    'sub' => 'Datos del paciente',      'preview' => $empresa->icono_pac_perfil_url],
                        ];
                    @endphp
                    @foreach($iconosPacPortal as $key => $info)
                    <div>
                        <p class="text-xs font-medium text-gray-700 mb-0.5">{{ $info['label'] }}</p>
                        <p class="text-[10px] text-gray-400 mb-2">{{ $info['sub'] }}</p>
                        <div class="upload-zone-sm" onclick="document.getElementById('{{ $key }}-input').click()">
                            @if($info['preview'])
                                <img id="{{ $key }}-preview" src="{{ $info['preview'] }}" alt="{{ $info['label'] }}" class="h-10 w-10 mx-auto object-contain">
                            @else
                                <img id="{{ $key }}-preview" src="" alt="" class="h-10 w-10 mx-auto object-contain hidden">
                                <svg class="w-6 h-6 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                                </svg>
                            @endif
                        </div>
                        <input type="file" name="{{ $key }}" id="{{ $key }}-input"
                               accept="image/png,image/svg+xml,image/webp" class="hidden"
                               onchange="previewIcon(this,'{{ $key }}-preview')">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ══ FILA 5: Iconos del Panel del Médico ════════════════════════════ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-800">Iconos del panel del médico</h2>
                <p class="text-xs text-gray-400 mt-0.5">Iconos exclusivos del menú lateral que ve el médico. Independientes del panel administrativo. PNG, SVG o WEBP · máx. 512 KB.</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @php
                        $iconosMedico = [
                            'icono_medico_dashboard'  => ['label' => 'Dashboard',      'sub' => 'Inicio del médico',         'preview' => $empresa->icono_medico_dashboard_url],
                            'icono_medico_citas'      => ['label' => 'Mis Citas',       'sub' => 'Agenda del médico',          'preview' => $empresa->icono_medico_citas_url],
                            'icono_medico_pacientes'  => ['label' => 'Mis Pacientes',   'sub' => 'Lista de pacientes',         'preview' => $empresa->icono_medico_pacientes_url],
                        ];
                    @endphp
                    @foreach($iconosMedico as $key => $info)
                    <div>
                        <p class="text-xs font-medium text-gray-700 mb-0.5">{{ $info['label'] }}</p>
                        <p class="text-[10px] text-gray-400 mb-2">{{ $info['sub'] }}</p>
                        <div class="upload-zone-sm" onclick="document.getElementById('{{ $key }}-input').click()">
                            @if($info['preview'])
                                <img id="{{ $key }}-preview" src="{{ $info['preview'] }}" alt="{{ $info['label'] }}" class="h-10 w-10 mx-auto object-contain">
                            @else
                                <img id="{{ $key }}-preview" src="" alt="" class="h-10 w-10 mx-auto object-contain hidden">
                                <svg class="w-6 h-6 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                                </svg>
                            @endif
                        </div>
                        <input type="file" name="{{ $key }}" id="{{ $key }}-input"
                               accept="image/png,image/svg+xml,image/webp" class="hidden"
                               onchange="previewIcon(this,'{{ $key }}-preview')">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ══ GUARDAR ══════════════════════════════════════════════════════════ --}}
        <div class="flex items-center justify-between">
            <p class="text-xs text-gray-400">Los cambios se aplican de inmediato en todas las vistas de esta IPS.</p>
            <button type="submit"
                    class="bg-gray-900 hover:bg-gray-700 text-white text-sm font-semibold px-7 py-2.5 rounded-xl transition shadow-sm">
                Guardar cambios
            </button>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
function previewImg(input, targetId) {
    if (!input.files?.[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById(targetId).src = e.target.result;
        if (targetId === 'logo-preview') {
            document.getElementById('sb-logo').src = e.target.result;
        }
    };
    reader.readAsDataURL(input.files[0]);
}

function previewIcon(input, previewId) {
    if (!input.files?.[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById(previewId);
        img.src = e.target.result;
        img.classList.remove('hidden');
        // Ocultar el SVG placeholder si existe
        const parent = img.parentElement;
        const svg = parent.querySelector('svg');
        if (svg) svg.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}

function branding() {
    return {
        previewRol: 'color_admin',
        vals: {
            color_primario:   '{{ $empresa->color_primario }}',
            color_pdf:        '{{ $empresa->color_pdf }}',
            color_admin:      '{{ $empresa->color_admin }}',
            color_doctor:     '{{ $empresa->color_doctor }}',
            color_gestor:     '{{ $empresa->color_gestor }}',
            color_paciente:   '{{ $empresa->color_paciente }}',
        },
    };
}
</script>
@endpush
