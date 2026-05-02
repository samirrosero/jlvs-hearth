@extends('paciente.layouts.app')

@section('title', 'Valorar Atención')
@section('page-title', 'Valorar Atención')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="bg-white rounded-3xl shadow-xl shadow-blue-500/5 border border-gray-100 overflow-hidden transition-all hover:shadow-blue-500/10">
        {{-- Header con gradiente suave --}}
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-10 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-8 -mb-8 w-32 h-32 bg-blue-400/20 rounded-full blur-2xl"></div>
            
            <div class="relative z-10">
                <h2 class="text-3xl font-extrabold mb-2">¡Tu opinión cuenta!</h2>
                <p class="text-blue-100 text-lg opacity-90">Ayúdanos a mejorar calificando la atención recibida.</p>
            </div>
        </div>

        <div class="p-8 md:p-12">
            {{-- Info de la cita --}}
            <div class="flex flex-col md:flex-row md:items-center gap-6 mb-10 p-6 bg-gray-50 rounded-2xl border border-gray-100">
                <div class="w-16 h-16 rounded-2xl bg-blue-100 flex items-center justify-center shrink-0">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ $cita->medico->usuario->nombre }}</h3>
                    <p class="text-blue-600 font-semibold text-sm uppercase tracking-wider">{{ $cita->medico->especialidad }}</p>
                    <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ \Carbon\Carbon::parse($cita->fecha)->format('d M, Y') }}
                        </span>
                        <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                        <span>{{ $cita->servicio->nombre ?? 'Consulta General' }}</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('paciente.citas.valorar.store', $cita) }}" method="POST" x-data="{ rating: {{ $puntuacionInicial ?? 0 }}, hoverRating: 0 }">
                @csrf
                
                {{-- Selector de Estrellas --}}
                <div class="text-center mb-10">
                    <label class="block text-gray-700 font-bold mb-4 text-lg">¿Cómo calificarías la atención?</label>
                    <div class="flex items-center justify-center gap-3">
                        <template x-for="i in 5">
                            <button type="button" 
                                    @click="rating = i"
                                    @mouseenter="hoverRating = i"
                                    @mouseleave="hoverRating = 0"
                                    class="p-2 transition-all duration-200 transform hover:scale-125 focus:outline-none"
                                    :class="i <= (hoverRating || rating) ? 'text-yellow-400 drop-shadow-sm' : 'text-gray-200'">
                                <svg class="w-12 h-12 fill-current" viewBox="0 0 24 24">
                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="puntuacion" x-model="rating">
                    @error('puntuacion')
                        <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Comentario --}}
                <div class="mb-8">
                    <label for="comentario" class="block text-gray-700 font-bold mb-3">Cuéntanos más (opcional)</label>
                    <textarea name="comentario" id="comentario" rows="4" 
                              class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-gray-400"
                              placeholder="Tu feedback nos ayuda a brindarte un mejor servicio..."></textarea>
                    @error('comentario')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Botones --}}
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('paciente.citas') }}" 
                       class="flex-1 px-8 py-4 text-center font-bold text-gray-600 bg-gray-100 rounded-2xl hover:bg-gray-200 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" 
                            :disabled="rating === 0"
                            class="flex-1 px-8 py-4 font-bold text-white bg-blue-600 rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none">
                        Enviar valoración
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
