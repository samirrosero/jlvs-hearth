<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña — JLVS Hearth</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/accesibilidad.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <img src="{{ asset('img/logos/logo1.png') }}" alt="JLVS Hearth" class="mx-auto mb-3 h-24 w-auto">
            <p class="text-gray-500 mt-1">Panel de Administración</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-2">Recuperar contraseña</h2>
            <p class="text-sm text-gray-500 mb-6">Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.</p>

            @if (session('exito'))
                <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                    {{ session('exito') }}
                </div>
            @endif

            @if ($errors->has('email'))
                <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    @if (str_contains($errors->first('email'), 'wait') || str_contains($errors->first('email'), 'retrying'))
                        Demasiados intentos. Espera un momento antes de volver a intentarlo.
                    @else
                        {{ $errors->first('email') }}
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('forgot-password.send') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2.5 rounded-lg transition text-sm">
                    Enviar enlace
                </button>
            </form>

            <div class="text-center mt-5">
                <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">
                    ← Volver al inicio de sesión
                </a>
            </div>
        </div>

        <p class="text-center text-gray-400 text-xs mt-4">
            JLVS Hearth &copy; {{ date('Y') }} — UNIAJC
        </p>
    </div>

</body>
</html>
