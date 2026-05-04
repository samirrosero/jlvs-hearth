<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $empresa = auth()->user()?->empresa; @endphp
    <title>@yield('title', 'Panel Gestor') — {{ $empresa?->nombre ?? 'JLVS Hearth' }}</title>
    @php $fv = ($empresa?->favicon_url ?? asset('favicon.ico')) . '?v=' . ($empresa?->updated_at?->timestamp ?? '1'); @endphp
    <link rel="icon" href="{{ $fv }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ $fv }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/accesibilidad.js'])
    <style>
        :root {
            --color-sidebar:    {{ $empresa?->color_gestor   ?? '#4c1d95' }};
            --color-primario:   {{ $empresa?->color_primario ?? '#0369a1' }};
            --color-secundario: {{ $empresa?->color_secundario ?? '#075985' }};
            --bs-primary:       {{ $empresa?->color_primario  ?? '#0369a1' }};
            --bs-primary-rgb:   3, 105, 161;
        }
        #gestor-sidebar {
            background-color: var(--color-sidebar) !important;
            border-right-color: rgba(255,255,255,.08) !important;
        }
        #gestor-sidebar .sidebar-logo-text { color: rgba(255,255,255,.85); }
        #gestor-sidebar .sidebar-empresa   { color: rgba(255,255,255,.55); }
        #gestor-sidebar .nav-item {
            color: rgba(255,255,255,.65);
        }
        #gestor-sidebar .nav-item:hover {
            background-color: rgba(255,255,255,.1);
            color: #fff;
        }
        #gestor-sidebar .nav-item.activo {
            background-color: rgba(255,255,255,.18);
            color: #fff;
        }
        #gestor-sidebar .nav-item img { filter: brightness(0) invert(1); opacity: .7; }
        #gestor-sidebar .nav-item.activo img,
        #gestor-sidebar .nav-item:hover img { opacity: 1; }
        #gestor-sidebar .sidebar-divider   { border-color: rgba(255,255,255,.1); }
        #gestor-sidebar .sidebar-username  { color: rgba(255,255,255,.9); }
        #gestor-sidebar .sidebar-role      { color: rgba(255,255,255,.5); }
        #gestor-sidebar .sidebar-logout    { color: rgba(255,255,255,.55); }
        #gestor-sidebar .sidebar-logout:hover { color: #fff; }
        #gestor-sidebar .sidebar-logout img { filter: brightness(0) invert(1); opacity: .55; }
        #gestor-sidebar .sidebar-logout:hover img { opacity: 1; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @stack('styles')
</head>
<body class="bg-gray-100 antialiased" x-data="{ sidebarOpen: false }">

    {{-- ── Sidebar ─────────────────────────────────────────────── --}}
    <aside id="gestor-sidebar"
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
                    // ── Dashboard ──────────────────────────────
                    [
                        'route' => 'gestor.dashboard',
                        'icon'  => $empresa?->icono_gestor_dashboard_url ?? asset('img/icons/dashboard.png'),
                        'label' => 'Agenda semanal',
                        'match' => 'gestor.dashboard',
                    ],
                    // ── Sección citas ──────────────────────────
                    ['divider' => 'Citas'],
                    [
                        'route' => 'gestor.citas.create',
                        'icon'  => $empresa?->icono_gestor_nueva_cita_url ?? asset('img/icons/citas-mes.png'),
                        'label' => 'Nueva cita',
                        'match' => 'gestor.citas.create',
                    ],
                    [
                        'route' => 'gestor.citas',
                        'match' => 'gestor.citas.index',
                        'icon'  => $empresa?->icono_gestor_citas_url ?? asset('img/icons/citas-mes.png'),
                        'label' => 'Ver todas',
                    ],
                    // ── Recepción y Cobro ──────────────────────
                    ['divider' => 'Recepción'],
                    [
                        'route' => 'gestor.recepcion.index',
                        'icon'  => $empresa?->icono_gestor_recepcion_url ?? asset('img/icons/citas-total.png'),
                        'label' => 'Recepción / Cobro',
                        'match' => 'gestor.recepcion.*',
                    ],
                    // ── Lista de espera ───────────────────────
                    ['divider' => 'Espera'],
                    [
                        'route' => 'gestor.lista-espera',
                        'match' => 'gestor.lista-espera',
                        'icon'  => $empresa?->icono_gestor_espera_url ?? asset('img/icons/pacientes.png'),
                        'label' => 'Lista de espera',
                    ],
                    // ── Sección pacientes ──────────────────────
                    ['divider' => 'Pacientes'],
                    [
                        'route' => 'gestor.pacientes.create',
                        'icon'  => $empresa?->icono_gestor_registrar_url ?? asset('img/icons/pacientes.png'),
                        'label' => 'Registrar paciente',
                        'match' => 'gestor.pacientes.create',
                    ],
                    [
                        'route' => 'gestor.pacientes',
                        'match' => 'gestor.pacientes.index',
                        'icon'  => $empresa?->icono_gestor_pacientes_url ?? asset('img/icons/pacientes.png'),
                        'label' => 'Directorio',
                    ],
                ];
            @endphp

            @foreach ($nav as $item)
                @if(isset($item['divider']))
                    <p class="px-4 pt-4 pb-1 text-xs font-bold uppercase tracking-widest" style="color:rgba(255,255,255,.3)">
                        {{ $item['divider'] }}
                    </p>
                @elseif(Route::has($item['route']))
                    <a href="{{ route($item['route']) }}"
                       class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition
                              {{ request()->routeIs($item['match'] ?? $item['route']) ? 'activo' : '' }}">
                        @if(isset($item['svg']))
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $item['svg'] !!}
                            </svg>
                        @else
                            <img src="{{ $item['icon'] }}" alt="{{ $item['label'] }}" class="w-5 h-5 flex-shrink-0">
                        @endif
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- Usuario + Logout --}}
        <div class="sidebar-divider border-t px-4 py-4">
            <p class="sidebar-username text-xs font-medium truncate">{{ auth()->user()->nombre }}</p>
            <p class="sidebar-role text-xs mb-1">{{ ucwords(str_replace('_', ' ', auth()->user()->rol?->nombre ?? 'Gestor de Citas')) }}</p>
            <p class="sidebar-role text-xs mb-3 opacity-70">{{ $empresa?->nombre }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="sidebar-logout w-full text-left text-sm flex items-center gap-2 transition">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
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
                <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Panel Gestor')</h1>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-500">{{ now()->format('d M Y') }}</span>

                {{-- Notificación slots liberados --}}
                <div x-data="notifSlots()" x-init="init()" class="relative">

                    <button @click="abierto = !abierto"
                            class="relative p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span x-show="slots.length > 0" style="display:none"
                              x-text="slots.length"
                              class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-amber-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1 animate-pulse">
                        </span>
                    </button>

                    <div x-show="abierto" @click.outside="abierto = false" style="display:none"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden">

                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-800">Slots liberados hoy</p>
                            <span x-show="slots.length > 0"
                                  x-text="slots.length + ' disponible' + (slots.length !== 1 ? 's' : '')"
                                  class="text-xs font-semibold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full"
                                  style="display:none"></span>
                        </div>

                        <template x-if="slots.length === 0">
                            <div class="px-4 py-8 text-center">
                                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm text-gray-400">Sin citas liberadas por ahora</p>
                            </div>
                        </template>

                        <template x-if="slots.length > 0">
                            <div class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                                <template x-for="slot in slots" :key="slot.id">
                                    <a href="{{ route('gestor.lista-espera') }}"
                                       class="flex items-start gap-3 px-4 py-3 hover:bg-amber-50 transition-colors">
                                        <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 bg-amber-100 text-amber-600 rounded-full mt-0.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-800 truncate"
                                               x-text="slot.paciente?.nombre_completo ?? '—'"></p>
                                            <p class="text-xs text-gray-500 truncate"
                                               x-text="nombreMedico(slot) + ' · ' + formatHora(slot.hora)"></p>
                                            <p class="text-xs text-amber-600 font-medium mt-0.5">No asistió — slot disponible</p>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>

                        <div class="px-4 py-3 border-t border-gray-100">
                            <a href="{{ route('gestor.lista-espera') }}"
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                Ir a lista de espera →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
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
    @if (Route::has('gestor.chatbot'))
    @php
        $chatbotRutas = collect([
            ['clave' => 'dashboard',          'label' => 'Ir al Dashboard',     'ruta' => 'gestor.dashboard'],
            ['clave' => 'citas',              'label' => 'Ver Citas',           'ruta' => 'gestor.citas'],
            ['clave' => 'nueva-cita',         'label' => 'Nueva Cita',          'ruta' => 'gestor.citas.create'],
            ['clave' => 'pacientes',          'label' => 'Ver Pacientes',       'ruta' => 'gestor.pacientes'],
            ['clave' => 'registrar-paciente', 'label' => 'Registrar Paciente',  'ruta' => 'gestor.pacientes.create'],
            ['clave' => 'lista-espera',       'label' => 'Ver Lista de Espera', 'ruta' => 'gestor.lista-espera'],
        ])->filter(fn ($s) => Route::has($s['ruta']))
          ->mapWithKeys(fn ($s) => [$s['clave'] => ['label' => $s['label'], 'url' => route($s['ruta'])]])
          ->toJson();
    @endphp

    <x-chatbot
        endpoint="{{ route('gestor.chatbot') }}"
        storage-key="gestor"
        :rutas-json="$chatbotRutas"
        mensaje-inicial="¡Hola! Soy tu asistente. Puedo decirte cuántas citas hay hoy, quién está en lista de espera, buscar pacientes o llevarte a cualquier sección del panel. ¿En qué te ayudo?"
    />
    @endif


    <script>
    function notifSlots() {
        return {
            slots:   [],
            abierto: false,

            async init() {
                await this.cargar();
                setInterval(() => this.cargar(), 30000);
            },

            async cargar() {
                try {
                    const res = await fetch('/citas/liberadas-hoy', { headers: { 'Accept': 'application/json' } });
                    if (res.ok) this.slots = await res.json();
                } catch (e) {}
            },

            nombreMedico(slot) {
                return slot?.medico?.usuario?.nombre
                    ?? slot?.medico?.usuario?.name
                    ?? 'Dr. —';
            },

            formatHora(h) {
                return h ? String(h).substring(0, 5) : '—';
            },
        };
    }
    </script>

    @stack('scripts')

</body>
</html>
