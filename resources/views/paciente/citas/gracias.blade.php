<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Gracias por tu valoración! — JLVS Hearth</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 antialiased font-sans">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="max-w-xl w-full">
            <div class="bg-white rounded-3xl shadow-2xl shadow-blue-500/10 p-10 md:p-16 border border-gray-100 text-center transform transition-all hover:scale-[1.01]">
                {{-- Icono de éxito --}}
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-8 shadow-inner">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h2 class="text-4xl font-black text-gray-900 mb-4 tracking-tight">¡Muchas Gracias!</h2>
                <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                    Hemos registrado tu calificación de <span class="text-blue-600 font-bold">{{ $puntuacion }} estrellas</span> para la atención del 
                    <span class="block font-semibold text-gray-800 mt-1">Dr. {{ $cita->medico->usuario->nombre }}</span>
                </p>

                {{-- Visualización de estrellas --}}
                <div class="flex items-center justify-center gap-2 mb-12">
                    @for($i=1; $i<=5; $i++)
                        <svg class="w-12 h-12 {{ $i <= $puntuacion ? 'text-yellow-400' : 'text-gray-200' }} fill-current drop-shadow-sm" viewBox="0 0 24 24">
                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                        </svg>
                    @endfor
                </div>

                <div class="space-y-6">
                    <p class="text-gray-500 italic text-sm px-8">Tu opinión es fundamental para ayudarnos a elevar la calidad de nuestro servicio médico.</p>
                    
                    <div class="pt-8 border-t border-gray-100">
                        <p class="text-xs text-gray-400 mb-4 uppercase tracking-widest font-bold">Puedes cerrar esta ventana ahora</p>
                        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-blue-600 font-bold hover:text-blue-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Volver al inicio
                        </a>
                    </div>
                </div>
            </div>
            
            {{-- Footer minimalista --}}
            <p class="text-center mt-8 text-gray-400 text-xs font-medium">
                &copy; {{ date('Y') }} {{ $cita->empresa->nombre ?? config('app.name') }}. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
