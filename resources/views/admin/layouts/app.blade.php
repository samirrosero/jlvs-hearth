<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Admin') — JLVS Hearth</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @stack('styles')
</head>
<body class="bg-gray-100 antialiased" x-data="{ sidebarOpen: false }">

    {{-- ── Sidebar ─────────────────────────────────────────────── --}}
    <aside
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col transition-transform duration-300
               md:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >
        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-200">
            <img src="{{ asset('img/logos/logo1.png') }}" alt="JLVS Hearth"
                 class="h-10 w-auto flex-shrink-0">
            <p class="text-gray-500 text-xs truncate">{{ auth()->user()->empresa->nombre }}</p>
        </div>

        {{-- Navegación --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            @php
                $nav = [
                    [
                        'route' => 'admin.dashboard',
                        'icon'  => asset('img/icons/dashboard.png'),
                        'label' => 'Dashboard',
                    ],
                    [
                        'route' => 'admin.pacientes.index',
                        'icon'  => asset('img/icons/pacientes.png'),
                        'label' => 'Pacientes',
                    ],
                    [
                        'route' => 'admin.medicos.index',
                        'icon'  => asset('img/icons/medicos.png'),
                        'label' => 'Médicos',
                    ],
                    [
                        'route' => 'admin.reportes',
                        'icon'  => asset('img/icons/reportes.png'),
                        'label' => 'Reportes',
                    ],
                ];
            @endphp

            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs($item['route']) || request()->routeIs($item['route'].'*')
                             ? 'bg-gray-100 text-gray-900'
                             : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <img src="{{ $item['icon'] }}" alt="{{ $item['label'] }}"
                         class="w-5 h-5 flex-shrink-0 opacity-70">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Usuario + Logout --}}
        <div class="border-t border-gray-200 px-4 py-4">
            <p class="text-xs text-gray-700 font-medium truncate">{{ auth()->user()->nombre }}</p>
            <p class="text-xs text-gray-400 mb-3">Administrador</p>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit"
                    class="w-full text-left text-sm text-gray-500 hover:text-gray-900 flex items-center gap-2 transition">
                    <img src="{{ asset('img/icons/logout.png') }}" alt="Cerrar sesión" class="w-4 h-4 flex-shrink-0 opacity-70">
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

    @stack('scripts')
</body>
</html>
