@extends('paciente.layouts.app')

@section('title', 'Mis Valoraciones')
@section('page-title', 'Mis Valoraciones')

@section('content')
<div class="grid grid-cols-1 gap-6">
    {{-- Mis Valoraciones --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
            <h3 class="font-bold text-gray-800">Historial de Valoraciones</h3>
            <p class="text-sm text-gray-500 mt-1">Aquí puedes ver todas las valoraciones que has realizado después de tus citas.</p>
        </div>

        <div class="divide-y divide-gray-50">
            @forelse ($valoraciones as $valoracion)
                <div class="px-6 py-5 hover:bg-gray-50 transition">
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <p class="text-sm font-bold text-gray-800">Dr. {{ $valoracion->cita->medico->usuario->nombre }}</p>
                                <span class="text-xs bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full font-medium">
                                    {{ $valoracion->cita->servicio->nombre ?? 'Consulta' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500">
                                <svg class="w-3.5 h-3.5 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.5 17a4.5 4.5 0 01-1.44-8.765 4.5 4.5 0 018.302-3.046 3.5 3.5 0 014.504 4.272A4 4 0 1015.5 17H5.5z" clip-rule="evenodd" />
                                </svg>
                                {{ $valoracion->created_at->format('d/m/Y') }} · {{ $valoracion->created_at->format('h:i A') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-1 shrink-0 bg-amber-50 px-3 py-2 rounded-lg">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $valoracion->puntuacion ? 'text-amber-400 fill-current' : 'text-gray-300' }}"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                    </div>
                    @if ($valoracion->comentario)
                        <div class="mt-3 p-3 bg-gray-50 border border-gray-100 rounded-lg">
                            <p class="text-sm text-gray-700">
                                <span class="font-semibold text-gray-500">Comentario:</span>
                            </p>
                            <p class="text-sm text-gray-600 mt-1 italic">
                                "{{ $valoracion->comentario }}"
                            </p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="px-6 py-16 text-center">
                    <svg class="w-16 h-16 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <p class="text-gray-500 font-medium text-lg">Aún no has realizado valoraciones</p>
                    <p class="text-gray-400 text-sm mt-2">
                        Después de completar tus citas podrás calificar el servicio y dejar tus comentarios.
                    </p>
                    <a href="{{ route('paciente.citas') }}" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                        Ver mis citas
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        @if ($valoraciones->hasPages())
            <div class="px-6 py-4 border-t border-gray-50 bg-gray-50">
                {{ $valoraciones->links('vendor.pagination.simple-tailwind') }}
            </div>
        @endif
    </div>
</div>

{{-- Información adicional --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6">
        <div class="flex gap-4">
            <div class="text-blue-600 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-blue-900 mb-1">¿Cómo valorar?</h4>
                <p class="text-sm text-blue-700">Después de cada cita completada, te llegara un correo con la opción de valorar al médico y el servicio recibido.</p>
            </div>
        </div>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6">
        <div class="flex gap-4">
            <div class="text-amber-600 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-amber-900 mb-1">Tu opinión importa</h4>
                <p class="text-sm text-amber-700">Tus valoraciones nos ayudan a mejorar continuamente la calidad del servicio.</p>
            </div>
        </div>
    </div>
</div>

@endsection
