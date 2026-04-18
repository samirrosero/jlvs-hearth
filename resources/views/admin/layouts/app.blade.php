<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $empresa = auth()->user()?->empresa; @endphp
    <title>@yield('title', 'Panel Admin') — {{ $empresa?->nombre ?? 'JLVS Hearth' }}</title>
    @php $fv = ($empresa?->favicon_url ?? asset('favicon.ico')) . '?v=' . ($empresa?->updated_at?->timestamp ?? '1'); @endphp
    <link rel="icon" href="{{ $fv }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ $fv }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-sidebar:    {{ $empresa?->color_admin    ?? '#1e293b' }};
            --color-primario:   {{ $empresa?->color_primario ?? '#1e40af' }};
            --color-secundario: {{ $empresa?->color_secundario ?? '#1e3a8a' }};
        }
        /* ── Sidebar temático ────────────────────────────────── */
        #app-sidebar {
            background-color: var(--color-sidebar) !important;
            border-right-color: rgba(255,255,255,.08) !important;
        }
        #app-sidebar .sidebar-logo-text { color: rgba(255,255,255,.85); }
        #app-sidebar .sidebar-empresa   { color: rgba(255,255,255,.55); }
        #app-sidebar .nav-item {
            color: rgba(255,255,255,.65);
        }
        #app-sidebar .nav-item:hover {
            background-color: rgba(255,255,255,.1);
            color: #fff;
        }
        #app-sidebar .nav-item.activo {
            background-color: rgba(255,255,255,.18);
            color: #fff;
        }
        #app-sidebar .nav-item img { filter: brightness(0) invert(1); opacity: .7; }
        #app-sidebar .nav-item.activo img,
        #app-sidebar .nav-item:hover img { opacity: 1; }
        #app-sidebar .sidebar-divider   { border-color: rgba(255,255,255,.1); }
        #app-sidebar .sidebar-username  { color: rgba(255,255,255,.9); }
        #app-sidebar .sidebar-role      { color: rgba(255,255,255,.5); }
        #app-sidebar .sidebar-logout {
            color: rgba(255,255,255,.55);
        }
        #app-sidebar .sidebar-logout:hover { color: #fff; }
        #app-sidebar .sidebar-logout img { filter: brightness(0) invert(1); opacity: .55; }
        #app-sidebar .sidebar-logout:hover img { opacity: 1; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @stack('styles')
</head>
<body class="bg-gray-100 antialiased" x-data="{ sidebarOpen: false }">

    {{-- ── Sidebar ─────────────────────────────────────────────── --}}
    <aside id="app-sidebar"
        class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col transition-transform duration-300 md:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >
        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-4 sidebar-divider border-b">
            <img src="{{ $empresa?->logo_url ?? asset('img/logos/logo1.png') }}"
                 alt="{{ $empresa?->nombre ?? 'JLVS Hearth' }}"
                 class="h-10 w-auto flex-shrink-0 object-contain">
            <p class="sidebar-empresa text-xs truncate">{{ $empresa?->nombre }}</p>
        </div>

        {{-- Navegación --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            @php
                $nav = [
                    [
                        'route' => 'admin.dashboard',
                        'icon'  => $empresa?->icono_dashboard_url ?? asset('img/icons/dashboard.png'),
                        'label' => 'Dashboard',
                    ],
                    [
                        'route' => 'admin.pacientes.index',
                        'icon'  => $empresa?->icono_pacientes_url ?? asset('img/icons/pacientes.png'),
                        'label' => 'Pacientes',
                    ],
                    [
                        'route' => 'admin.medicos.index',
                        'icon'  => $empresa?->icono_medicos_url ?? asset('img/icons/medicos.png'),
                        'label' => 'Médicos',
                    ],
                    [
                        'route' => 'admin.reportes',
                        'icon'  => $empresa?->icono_reportes_url ?? asset('img/icons/reportes.png'),
                        'label' => 'Reportes',
                    ],
                    [
                        'route' => 'admin.solicitudes.index',
                        'icon'  => $empresa?->icono_solicitudes_url ?? asset('img/icons/pacientes.png'),
                        'label' => 'Solicitudes personal',
                    ],
                    [
                        'route' => 'admin.branding',
                        'icon'  => $empresa?->icono_identidad_url ?? asset('img/icons/dashboard.png'),
                        'label' => 'Identidad Visual',
                    ],
                ];
            @endphp

            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}"
                   class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs($item['route']) || request()->routeIs($item['route'].'*') ? 'activo' : '' }}">
                    <img src="{{ $item['icon'] }}" alt="{{ $item['label'] }}"
                         class="w-5 h-5 flex-shrink-0">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Usuario + Logout --}}
        <div class="sidebar-divider border-t px-4 py-4">
            <p class="sidebar-username text-xs font-medium truncate">{{ auth()->user()->nombre }}</p>
            <p class="sidebar-role text-xs mb-3">Administrador</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="sidebar-logout w-full text-left text-sm flex items-center gap-2 transition">
                    <img src="{{ asset('img/icons/logout.png') }}" alt="Cerrar sesión" class="w-4 h-4 flex-shrink-0">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Overlay móvil ──────────────────────────────────────── --}}
    <div class="fixed inset-0 z-40 bg-black/50 md:hidden"
         x-show="sidebarOpen" @click="sidebarOpen = false"
         x-transition:enter="transition-opacity duration-200"
         x-transition:leave="transition-opacity duration-200"
         style="display:none"></div>

    {{-- ── Contenido principal ─────────────────────────────────── --}}
    <div class="md:ml-64 min-h-screen flex flex-col">

        {{-- Header --}}
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Panel de Administración')</h1>
            </div>
            <span class="text-sm text-gray-500">{{ now()->format('d M Y') }}</span>
        </header>

        {{-- Flash messages --}}
        @if (session('exito'))
            <div class="mx-6 mt-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm flex items-center gap-2"
                 x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
                <span>✅</span> {{ session('exito') }}
                <button @click="show = false" class="ml-auto text-green-600 hover:text-green-800">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="mx-6 mt-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm flex items-center gap-2">
                <span>❌</span> {{ session('error') }}
            </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>

    {{-- ── Chatbot flotante ────────────────────────────────────── --}}
    <div x-data="chatbot()" class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3">

        {{-- Ventana del chat --}}
        <div x-show="abierto" x-transition
             class="w-80 bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden"
             style="height: 420px;">

            {{-- Cabecera --}}
            <div class="bg-gray-900 px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                    <span class="text-white text-sm font-medium">Asistente JLVS</span>
                </div>
                <button @click="abierto = false" class="text-gray-400 hover:text-white transition text-lg leading-none">&times;</button>
            </div>

            {{-- Mensajes --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-3 text-sm" x-ref="mensajes">
                <template x-for="(m, i) in mensajes" :key="i">
                    <div :class="m.rol === 'user' ? 'flex justify-end' : 'flex justify-start flex-col items-start gap-1'">
                        <div :class="m.rol === 'user'
                                ? 'bg-gray-900 text-white rounded-2xl rounded-br-sm px-3 py-2 max-w-[75%]'
                                : 'bg-gray-100 text-gray-800 rounded-2xl rounded-bl-sm px-3 py-2 max-w-[85%]'"
                             x-text="m.texto">
                        </div>
                        {{-- Botones de navegación --}}
                        <template x-if="m.acciones && m.acciones.length > 0">
                            <div class="flex flex-wrap gap-1 ml-1">
                                <template x-for="(a, j) in m.acciones" :key="j">
                                    <a :href="a.url"
                                       class="inline-flex items-center gap-1 text-xs bg-white border border-gray-300 hover:border-gray-900 hover:bg-gray-900 hover:text-white text-gray-700 rounded-lg px-2 py-1 transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                        </svg>
                                        <span x-text="a.label"></span>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
                <div x-show="cargando" class="flex justify-start">
                    <div class="bg-gray-100 text-gray-400 rounded-2xl rounded-bl-sm px-3 py-2 text-xs italic">
                        Escribiendo...
                    </div>
                </div>
            </div>

            {{-- Input --}}
            <div class="border-t border-gray-200 p-3 flex gap-2">
                <input
                    x-model="mensaje"
                    @keydown.enter.prevent="enviar()"
                    :disabled="cargando"
                    type="text"
                    placeholder="Escribe tu pregunta..."
                    class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-900 disabled:opacity-50"
                >
                <button
                    @click="enviar()"
                    :disabled="cargando || mensaje.trim() === ''"
                    class="bg-gray-900 text-white rounded-lg px-3 py-2 hover:bg-gray-700 transition disabled:opacity-40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Botón flotante --}}
        <button @click="abierto = !abierto"
                class="w-14 h-14 bg-gray-900 hover:bg-gray-700 text-white rounded-full shadow-lg flex items-center justify-center transition">
            <svg x-show="!abierto" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/>
            </svg>
            <svg x-show="abierto" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <script>
    function chatbot() {

        const rutas = {
            'dashboard': { label: 'Ir al Dashboard',  url: '{{ route('admin.dashboard') }}' },
            'pacientes': { label: 'Ver Pacientes',    url: '{{ route('admin.pacientes.index') }}' },
            'medicos':   { label: 'Ver Médicos',      url: '{{ route('admin.medicos.index') }}' },
            'reportes':  { label: 'Ver Reportes',     url: '{{ route('admin.reportes') }}' },
        };

        const descargas = {
            'citas-pdf':       { label: 'Descargar Citas PDF',        url: '{{ route('reportes.citas.pdf') }}' },
            'citas-excel':     { label: 'Descargar Citas Excel',      url: '{{ route('reportes.citas.excel') }}' },
            'pacientes-pdf':   { label: 'Descargar Pacientes PDF',    url: '{{ route('reportes.pacientes.pdf') }}' },
            'pacientes-excel': { label: 'Descargar Pacientes Excel',  url: '{{ route('reportes.pacientes.excel') }}' },
        };

        function parsearRespuesta(raw) {
            const acciones = [];
            let navegar   = null;
            let descarga  = null;

            // [NAVEGAR:ruta] — navegación automática
            const matchNav = raw.match(/\[NAVEGAR:(\w+)\]/);
            if (matchNav && rutas[matchNav[1]]) {
                navegar = rutas[matchNav[1]];
            }

            // [DESCARGAR:tipo] — descarga automática en nueva pestaña
            const matchDl = raw.match(/\[DESCARGAR:([\w-]+)\]/);
            if (matchDl && descargas[matchDl[1]]) {
                descarga = descargas[matchDl[1]];
            }

            // [IR:ruta] — botones de sugerencia
            const marcador = /\[IR:(\w+)\]/g;
            let match;
            while ((match = marcador.exec(raw)) !== null) {
                const clave = match[1];
                if (rutas[clave]) acciones.push(rutas[clave]);
            }

            const texto = raw
                .replace(/\[NAVEGAR:\w+\]/g, '')
                .replace(/\[DESCARGAR:[\w-]+\]/g, '')
                .replace(/\[IR:\w+\]/g, '')
                .replace(/\s{2,}/g, ' ')
                .trim();

            return { texto, acciones, navegar, descarga };
        }

        const STORAGE_KEY = 'jlvs_chatbot_historial';
        const mensajeInicial = [{ rol: 'bot', texto: '¡Hola! Soy el asistente de JLVS Hearth. Puedo contarte el estado actual de tu IPS o ayudarte a navegar por el sistema. ¿En qué te puedo ayudar?', acciones: [] }];

        function cargarHistorial() {
            try {
                const guardado = localStorage.getItem(STORAGE_KEY);
                return guardado ? JSON.parse(guardado) : mensajeInicial;
            } catch { return mensajeInicial; }
        }

        function guardarHistorial(mensajes) {
            try { localStorage.setItem(STORAGE_KEY, JSON.stringify(mensajes)); } catch {}
        }

        const historialPrevio = cargarHistorial();
        const veniaDeNavegacion = localStorage.getItem('jlvs_chatbot_nav') === '1';
        localStorage.removeItem('jlvs_chatbot_nav');

        return {
            abierto: veniaDeNavegacion,
            mensaje: '',
            mensajes: historialPrevio,
            cargando: false,

            async enviar() {
                const texto = this.mensaje.trim();
                if (!texto || this.cargando) return;

                this.mensajes.push({ rol: 'user', texto, acciones: [] });
                guardarHistorial(this.mensajes);
                this.mensaje = '';
                this.cargando = true;
                this.$nextTick(() => this.scrollAbajo());

                try {
                    const res = await fetch('{{ route('admin.chatbot') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ mensaje: texto }),
                    });

                    const data = await res.json();
                    const raw = data.respuesta ?? data.error ?? 'Error al obtener respuesta.';
                    const { texto: textoLimpio, acciones, navegar, descarga } = parsearRespuesta(raw);
                    this.mensajes.push({ rol: 'bot', texto: textoLimpio, acciones });
                    guardarHistorial(this.mensajes);

                    if (navegar) {
                        const msgNavegar = { rol: 'bot', texto: `Redirigiendo a ${navegar.label.replace('Ir al ', '').replace('Ver ', '')}...`, acciones: [] };
                        this.mensajes.push(msgNavegar);
                        guardarHistorial(this.mensajes);
                        localStorage.setItem('jlvs_chatbot_nav', '1');
                        setTimeout(() => { window.location.href = navegar.url; }, 1500);
                    }

                    if (descarga) {
                        const msgDl = { rol: 'bot', texto: `Generando ${descarga.label.replace('Descargar ', '')}...`, acciones: [] };
                        this.mensajes.push(msgDl);
                        guardarHistorial(this.mensajes);
                        setTimeout(() => { window.open(descarga.url, '_blank'); }, 800);
                    }
                } catch (e) {
                    this.mensajes.push({ rol: 'bot', texto: 'No se pudo conectar con el asistente.', acciones: [] });
                } finally {
                    this.cargando = false;
                    this.$nextTick(() => this.scrollAbajo());
                }
            },

            scrollAbajo() {
                const c = this.$refs.mensajes;
                if (c) c.scrollTop = c.scrollHeight;
            },
        };
    }
    </script>

    @stack('scripts')
</body>
</html>
