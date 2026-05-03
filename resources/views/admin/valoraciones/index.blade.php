@extends('admin.layouts.app')

@section('title', 'Valoraciones del Sistema')
@section('page-title', 'Valoraciones del Sistema')

@section('content')
<div class="grid grid-cols-1 gap-6">
    {{-- Estadísticas Generales --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="bg-blue-50 rounded-xl p-3 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $estadisticas['total'] }}</p>
                    <p class="text-sm text-gray-500 font-medium">Total valoraciones</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="bg-amber-50 rounded-xl p-3 text-amber-600">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $estadisticas['promedio_general'] }}</p>
                    <p class="text-sm text-gray-500 font-medium">Promedio general</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="bg-emerald-50 rounded-xl p-3 text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $estadisticas['excelente'] }}</p>
                    <p class="text-sm text-gray-500 font-medium">Excelentes (4-5★)</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="bg-orange-50 rounded-xl p-3 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $estadisticas['malo'] + $estadisticas['regular'] }}</p>
                    <p class="text-sm text-gray-500 font-medium">Necesitan mejora</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Médicos --}}
    @if ($topMedicos->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-800 mb-4">Top Médicos por Valoración</h3>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            @foreach ($topMedicos as $medico)
                <div class="text-center p-4 bg-gray-50 rounded-xl">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <span class="text-blue-600 font-bold text-sm">
                            {{ substr($medico->usuario->nombre ?? '?', 0, 1) }}
                        </span>
                    </div>
                    <p class="text-sm font-bold text-gray-800 truncate">{{ $medico->usuario->nombre ?? 'Médico no encontrado' }}</p>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <svg class="w-4 h-4 text-amber-400 fill-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="text-sm font-bold text-gray-700">{{ number_format($medico->promedio_valoracion, 1) }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $medico->valoraciones_count }} valoraciones</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Lista de Todas las Valoraciones --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
            <h3 class="font-bold text-gray-800">Todas las Valoraciones</h3>
            <p class="text-sm text-gray-500 mt-1">Historial completo de valoraciones del sistema.</p>
        </div>

        <div class="divide-y divide-gray-50">
            @forelse ($valoraciones as $valoracion)
                <div class="px-6 py-5 hover:bg-gray-50 transition">
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded-full font-medium">
                                        Dr. {{ $valoracion->cita->medico->usuario->nombre ?? 'Médico no encontrado' }}
                                    </span>
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <span class="text-sm font-bold text-gray-800">{{ $valoracion->cita->paciente->usuario->nombre ?? 'Paciente no encontrado' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full font-medium">
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
                                <span class="font-semibold text-gray-500">Comentario del paciente:</span>
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
                    <p class="text-gray-500 font-medium text-lg">No hay valoraciones registradas</p>
                    <p class="text-gray-400 text-sm mt-2">
                        Las valoraciones aparecerán aquí cuando los pacientes califiquen sus citas.
                    </p>
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

@endsection
