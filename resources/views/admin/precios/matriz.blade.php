@extends('admin.layouts.app')
@section('title', 'Matriz de Precios')
@section('page-title', 'Matriz de Precios')
@section('page-subtitle', 'Servicios × Portafolios')

@section('content')
<div class="space-y-6">

    {{-- Navegación --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <a href="{{ route('admin.servicios.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver a servicios
        </a>

        <div class="flex gap-2">
            <a href="{{ route('admin.portafolios.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Portafolios
            </a>
        </div>
    </div>

    {{-- Resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $servicios->count() }}</p>
                    <p class="text-sm text-gray-600">Servicios activos</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 rounded-lg text-green-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $portafolios->count() }}</p>
                    <p class="text-sm text-gray-600">Portafolios</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 rounded-lg text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    @php
                        $totalPrecios = 0;
                        foreach ($precios as $servicioPrecios) {
                            $totalPrecios += $servicioPrecios->count();
                        }
                    @endphp
                    <p class="text-2xl font-bold text-gray-800">{{ $totalPrecios }}</p>
                    <p class="text-sm text-gray-600">Precios configurados</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de matriz --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">Tabla de precios por servicio y portafolio</h2>
            <span class="text-xs text-gray-500">Clic en el ícono de editar para modificar precios</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 sticky left-0 bg-gray-50 min-w-[200px]">
                            Servicio / Duración
                        </th>
                        @foreach($portafolios as $portafolio)
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 min-w-[120px]">
                                {{ $portafolio->nombre_convenio }}
                            </th>
                        @endforeach
                        <th class="px-4 py-3 text-center font-semibold text-gray-700 min-w-[80px]">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($servicios as $servicio)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 sticky left-0 bg-white border-r border-gray-100">
                            <p class="font-medium text-gray-800">{{ $servicio->nombre }}</p>
                            <p class="text-xs text-gray-500">{{ $servicio->duracion_minutos }} min</p>
                        </td>
                        @foreach($portafolios as $portafolio)
                            @php
                                $precio = $precios->get($servicio->id)?->get($portafolio->id);
                            @endphp
                            <td class="px-4 py-3 text-center">
                                @if($precio)
                                    <span class="font-medium text-gray-900">
                                        ${{ number_format($precio->precio, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.servicios.precios', $servicio) }}"
                               class="inline-flex items-center p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition"
                               title="Editar precios">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $portafolios->count() + 2 }}" class="px-4 py-8 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm font-medium">No hay servicios registrados</p>
                            <p class="text-xs mt-1">Registra servicios para configurar sus precios.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Nota informativa --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h4 class="font-medium text-amber-900 text-sm">¿Cómo funcionan los precios?</h4>
                <p class="text-sm text-amber-700 mt-1">
                    Cada servicio puede tener precios diferentes según el tipo de convenio (portafolio).
                    Los precios se utilizan para mostrar estimados al momento de agendar citas y en la facturación.
                    <a href="{{ route('admin.servicios.index') }}" class="underline hover:text-amber-900">Gestionar servicios →</a>
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
