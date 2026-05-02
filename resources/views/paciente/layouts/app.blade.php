<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $empresa = auth()->user()?->empresa; @endphp
    <title>@yield('title', 'Portal Paciente') — {{ $empresa?->nombre ?? 'JLVS Hearth' }}</title>
    @php $fv = ($empresa?->favicon_url ?? asset('favicon.ico')) . '?v=' . ($empresa?->updated_at?->timestamp ?? '1'); @endphp
    <link rel="icon" href="{{ $fv }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ $fv }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/accesibilidad.js'])
    <style>
        :root {
            --color-sidebar:    {{ $empresa?->color_paciente   ?: '#0f172a' }};
            --color-primario:   {{ $empresa?->color_primario   ?: '#0369a1' }};
            --color-secundario: {{ $empresa?->color_secundario ?: '#075985' }};
        }
        #paciente-sidebar {
            background-color: var(--color-sidebar) !important;
            border-right-color: rgba(255,255,255,.08) !important;
        }
        #paciente-sidebar .sidebar-empresa   { color: rgba(255,255,255,.55); }
        #paciente-sidebar .nav-item          { color: rgba(255,255,255,.65); }
        #paciente-sidebar .nav-item:hover    { background-color: rgba(255,255,255,.1); color: #fff; }
        #paciente-sidebar .nav-item.activo   { background-color: rgba(255,255,255,.18); color: #fff; }
        #paciente-sidebar .nav-item img      { filter: grayscale(1) contrast(100) invert(1); opacity: .7; }
        #paciente-sidebar .nav-item.activo img,
        #paciente-sidebar .nav-item:hover img { opacity: 1; }
        #paciente-sidebar .sidebar-divider   { border-color: rgba(255,255,255,.1); }
        #paciente-sidebar .sidebar-username  { color: rgba(255,255,255,.9); }
        #paciente-sidebar .sidebar-role      { color: rgba(255,255,255,.5); }
        #paciente-sidebar .sidebar-logout    { color: rgba(255,255,255,.55); }
        #paciente-sidebar .sidebar-logout:hover { color: #fff; }
        #paciente-sidebar .sidebar-logout svg { opacity: .6; }
        #paciente-sidebar .sidebar-logout:hover svg { opacity: 1; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 antialiased" x-data="{ sidebarOpen: false }">

    {{-- ── Sidebar ─────────────────────────────────────────────── --}}
    <aside id="paciente-sidebar"
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
                        'route' => 'paciente.dashboard',
                        'icon'  => $empresa?->icono_pac_inicio_url ?: asset('img/icons/dashboard.png'),
                        'label' => 'Inicio',
                    ],
                    [
                        'route' => 'paciente.citas',
                        'match' => 'paciente.citas*',
                        'icon'  => $empresa?->icono_pac_citas_url ?: asset('img/icons/citas-mes.png'),
                        'label' => 'Mis Citas',
                    ],
                    [
                        'route' => 'paciente.agendar',
                        'match' => 'paciente.agendar*',
                        'icon'  => $empresa?->icono_pac_citas_url ?: asset('img/icons/citas-mes.png'),
                        'label' => 'Agendar Cita',
                    ],
                    [
                        'route' => 'paciente.historial',
                        'match' => 'paciente.historial*',
                        'icon'  => $empresa?->icono_pac_historial_url ?: asset('img/icons/pacientes.png'),
                        'label' => 'Mi Historial',
                    ],
                    [
                        'route' => 'paciente.ordenes',
                        'match' => 'paciente.ordenes*',
                        'icon'  => asset('img/icons/citas-mes.png'),
                        'label' => 'Mis Órdenes',
                    ],
                    [
                        'route' => 'paciente.certificados',
                        'match' => 'paciente.certificados*',
                        'svg'   => '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                        'label' => 'Certificados',
                    ],
                    [
                        'route' => 'paciente.perfil',
                        'match' => 'paciente.perfil*',
                        'icon'  => $empresa?->icono_pac_perfil_url ?: asset('img/icons/pacientes.png'),
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
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white text-xs font-bold">
                    {{ substr(auth()->user()->nombre, 0, 1) }}
                </div>
                <div class="overflow-hidden">
                    <p class="sidebar-username text-xs font-medium truncate">{{ auth()->user()->nombre }}</p>
                    <p class="sidebar-role text-[10px]">Paciente</p>
                </div>
            </div>
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
                <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Portal del Paciente')</h1>
            </div>
            <span class="text-sm text-gray-500">{{ now()->format('d M Y') }}</span>
        </header>

        {{-- Flash messages --}}
        @if (session('exito') || session('success'))
            <div class="mx-6 mt-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm flex items-center gap-2"
                 x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                <span>✅</span> {{ session('exito') ?? session('success') }}
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

    {{-- Chatbot flotante --}}
    @php
        $chatbotRutas = collect([
            ['clave' => 'dashboard',  'label' => 'Ir al Inicio',       'ruta' => 'paciente.dashboard'],
            ['clave' => 'citas',      'label' => 'Ver Mis Citas',       'ruta' => 'paciente.citas'],
            ['clave' => 'historial',  'label' => 'Ver Mi Historial',    'ruta' => 'paciente.historial'],
        ])->filter(fn ($s) => Route::has($s['ruta']))
          ->mapWithKeys(fn ($s) => [$s['clave'] => ['label' => $s['label'], 'url' => route($s['ruta'])]])
          ->toJson();
    @endphp

    <x-chatbot
        endpoint="{{ route('paciente.chatbot') }}"
        storage-key="paciente"
        :rutas-json="$chatbotRutas"
        mensaje-inicial="¡Hola! Soy tu asistente personal. Puedo decirte cuántas citas tienes o ayudarte a navegar. ¿En qué te puedo ayudar?"
    />

    @stack('scripts')

</body>
</html>
