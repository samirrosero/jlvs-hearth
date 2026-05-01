<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Token CSRF: necesario para todas las peticiones POST/PUT/DELETE --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'JLVS Hearth')</title>

    {{-- Favicon simple con emoji de salud --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    {{-- Carga Tailwind CSS + Alpine.js compilados por Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Espacio para estilos adicionales de cada página --}}
    @stack('styles')
</head>
<body class="bg-white antialiased text-gray-800">

    {{-- Contenido de cada página --}}
    @yield('content')

    {{-- Espacio para scripts adicionales de cada página --}}
    @stack('scripts')

    {{-- UserWay: widget de accesibilidad --}}
    <script src="https://cdn.userway.org/widget.js" data-account="P4wy9GEOmv"></script>
</body>
</html>
