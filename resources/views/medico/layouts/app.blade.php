<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $empresa = auth()->user()?->empresa; @endphp
    <title>@yield('title', 'Panel Médico') — {{ $empresa?->nombre ?? 'JLVS Hearth' }}</title>
    @php $fv = ($empresa?->favicon_url ?? asset('favicon.ico')) . '?v=' . ($empresa?->updated_at?->timestamp ?? '1'); @endphp
    <link rel="icon" href="{{ $fv }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ $fv }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-sidebar:    {{ $empresa?->color_doctor   ?? '#0f172a' }};
            --color-primario:   {{ $empresa?->color_primario ?? '#0369a1' }};
            --color-secundario: {{ $empresa?->color_secundario ?? '#075985' }};
        }
        #medico-sidebar {
            background-color: var(--color-sidebar) !important;
            border-right-color: rgba(255,255,255,.08) !important;
        }
        #medico-sidebar .sidebar-logo-text { color: rgba(255,255,255,.85); }
        #medico-sidebar .sidebar-empresa   { color: rgba(255,255,255,.55); }
        #medico-sidebar .nav-item {
            color: rgba(255,255,255,.65);
        }
        #medico-sidebar .nav-item:hover {
            background-color: rgba(255,255,255,.1);
            color: #fff;
        }
        #medico-sidebar .nav-item.activo {
            background-color: rgba(255,255,255,.18);
            color: #fff;
        }
        #medico-sidebar .nav-item img { filter: grayscale(1) contrast(100) invert(1); opacity: .7; }
        #medico-sidebar .nav-item.activo img,
        #medico-sidebar .nav-item:hover img { opacity: 1; }
        #medico-sidebar .sidebar-divider   { border-color: rgba(255,255,255,.1); }
        #medico-sidebar .sidebar-username  { color: rgba(255,255,255,.9); }
        #medico-sidebar .sidebar-role      { color: rgba(255,255,255,.5); }
        #medico-sidebar .sidebar-logout    { color: rgba(255,255,255,.55); }
        #medico-sidebar .sidebar-logout:hover { color: #fff; }
        #medico-sidebar .sidebar-logout svg { opacity: .6; }
        #medico-sidebar .sidebar-logout:hover svg { opacity: 1; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @stack('styles')
</head>
<body class="bg-gray-100 antialiased" x-data="{ sidebarOpen: false }">

    {{-- ── Sidebar ─────────────────────────────────────────────── --}}
    <aside id="medico-sidebar"
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
                        'route' => 'medico.dashboard',
                        'icon'  => $empresa?->icono_medico_dashboard_url ?: asset('img/icons/dashboard.png'),
                        'label' => 'Dashboard',
                    ],
                    [
                        'route' => 'medico.agenda',
                        'match' => 'medico.agenda*',
                        'svg'   => '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                        'label' => 'Mi Agenda',
                    ],
                    [
                        'route' => 'medico.citas',
                        'match' => 'medico.citas*',
                        'icon'  => $empresa?->icono_medico_citas_url ?: asset('img/icons/citas-mes.png'),
                        'label' => 'Mis Citas',
                    ],
                    [
                        'route' => 'medico.pacientes',
                        'match' => 'medico.pacientes*',
                        'icon'  => $empresa?->icono_medico_pacientes_url ?: asset('img/icons/pacientes.png'),
                        'label' => 'Mis Pacientes',
                    ],
                    [
                        'route' => 'medico.horario',
                        'match' => 'medico.horario*',
                        'svg'   => '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                        'label' => 'Mi Horario',
                    ],
                    [
                        'route' => 'medico.ordenes',
                        'match' => 'medico.ordenes*',
                        'svg'   => '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
                        'label' => 'Órdenes Emitidas',
                    ],
                    [
                        'route' => 'medico.perfil',
                        'match' => 'medico.perfil*',
                        'svg'   => '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                        'label' => 'Mi Perfil',
                    ],
                ];
            @endphp

            @foreach ($nav as $item)
                @if (Route::has($item['route']))
                <a href="{{ route($item['route']) }}"
                   class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs($item['match'] ?? $item['route']) ? 'activo' : '' }}">
                    @if (!empty($item['svg']))
                        {!! $item['svg'] !!}
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
            <p class="sidebar-role text-xs mb-1">{{ auth()->user()->medico?->especialidad ?? 'Médico' }}</p>
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
                <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Panel Médico')</h1>
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
    @php
        $chatbotRutas = collect([
            ['clave' => 'dashboard',  'label' => 'Ir al Dashboard',   'ruta' => 'medico.dashboard'],
            ['clave' => 'citas',      'label' => 'Ver Mis Citas',      'ruta' => 'medico.citas'],
            ['clave' => 'pacientes',  'label' => 'Ver Mis Pacientes',  'ruta' => 'medico.pacientes'],
        ])->filter(fn ($s) => Route::has($s['ruta']))
          ->mapWithKeys(fn ($s) => [$s['clave'] => ['label' => $s['label'], 'url' => route($s['ruta'])]])
          ->toJson();
    @endphp

    <x-chatbot
        endpoint="{{ route('medico.chatbot') }}"
        storage-key="medico"
        :rutas-json="$chatbotRutas"
        mensaje-inicial="¡Hola! Soy tu asistente médico. Puedo decirte cuántas citas tienes hoy, el estado de tus pacientes o ayudarte a navegar. ¿En qué te puedo ayudar?"
    />

    @stack('scripts')

    {{-- UserWay: widget de accesibilidad --}}
    <script src="https://cdn.userway.org/widget.js" data-account="P4wy9GEOmv"></script>
</body>
</html>
