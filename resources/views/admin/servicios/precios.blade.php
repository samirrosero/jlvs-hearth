@extends('admin.layouts.app')
@section('title', 'Precios: ' . $servicio->nombre)
@section('page-title', 'Gestión de Precios')
@section('page-subtitle', $servicio->nombre)

@section('content')
<div class="space-y-6">

    {{-- Navegación y acciones --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <a href="{{ route('admin.servicios.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver a servicios
        </a>

        <a href="{{ route('admin.precios.matriz') }}"
           class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Ver matriz completa de precios
        </a>
    </div>

    {{-- Información del servicio --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-start gap-4">
            <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <h2 class="font-semibold text-gray-800">{{ $servicio->nombre }}</h2>
                <p class="text-sm text-gray-600 mt-1">
                    Duración: <span class="font-medium">{{ $servicio->duracion_minutos }} minutos</span>
                </p>
                @if($servicio->descripcion)
                    <p class="text-sm text-gray-500 mt-1">{{ $servicio->descripcion }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('exito'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl px-4 py-3">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('exito') }}
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Formulario de precios --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">Precios por tipo de convenio</h2>
            <span class="text-xs text-gray-500">{{ $portafolios->count() }} portafolios</span>
        </div>

        <form method="POST" action="{{ route('admin.servicios.precios.update', $servicio) }}" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                @forelse($portafolios as $portafolio)
                    @php
                        $precioActual = $preciosActuales->get($portafolio->id);
                    @endphp
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $portafolio->nombre_convenio }}
                            </label>
                            @if($portafolio->descripcion)
                                <p class="text-xs text-gray-500">{{ $portafolio->descripcion }}</p>
                            @endif
                        </div>
                        <div class="flex-shrink-0">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">$</span>
                                <input type="number"
                                       name="precios[{{ $portafolio->id }}]"
                                       value="{{ $precioActual ? number_format($precioActual->precio, 0, '', '') : '' }}"
                                       placeholder="0"
                                       min="0"
                                       step="1"
                                       class="w-40 pl-7 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-right">
                            </div>
                            @error('precios.' . $portafolio->id)
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm font-medium">No hay portafolios registrados</p>
                        <p class="text-xs mt-1">Primero debes crear portafolios en la sección de convenios.</p>
                        <a href="{{ route('admin.portafolios.index') }}"
                           class="inline-block mt-3 text-sm text-blue-600 hover:text-blue-800">
                            Ir a portafolios →
                        </a>
                    </div>
                @endforelse
            </div>

            @if($portafolios->count() > 0)
                <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500">
                        Deja el campo vacío para eliminar el precio de un portafolio.
                    </p>
                    <div class="flex gap-3">
                        <a href="{{ route('admin.servicios.index') }}"
                           class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                            Guardar precios
                        </button>
                    </div>
                </div>
            @endif
        </form>
    </div>

    {{-- Vista previa de precios actuales --}}
    @if($preciosActuales->count() > 0)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Precios actuales configurados</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($preciosActuales as $precio)
            <div class="px-6 py-3 flex items-center justify-between">
                <span class="text-sm text-gray-700">{{ $precio->portafolio->nombre_convenio }}</span>
                <span class="text-sm font-semibold text-gray-900">
                    ${{ number_format($precio->precio, 0, ',', '.') }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
