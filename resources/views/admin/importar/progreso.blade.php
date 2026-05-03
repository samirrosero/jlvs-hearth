@extends('admin.layouts.app')
@section('title', 'Importación en progreso')
@section('content')
<div class="max-w-3xl mx-auto" x-data="progresoImportacion({{ $importacion->id }})" x-init="iniciar()">

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.importar.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Importación de {{ ucfirst($importacion->tipo) }}</h1>
        </div>
        <p class="text-sm text-gray-600">
            Archivo: <span class="font-medium">{{ $importacion->nombre_archivo }}</span>
        </p>
    </div>

    {{-- Alerta de éxito inicial --}}
    @if(session('exito'))
    <div class="flex items-start gap-3 bg-blue-50 border border-blue-200 text-blue-800 text-sm rounded-xl px-4 py-3 mb-6">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd"/>
        </svg>
        <div>
            <p class="font-medium">{{ session('exito') }}</p>
            <p class="text-xs mt-1">Puedes salir de esta vista y seguir trabajando. La importación continúa en segundo plano.</p>
        </div>
    </div>
    @endif

    {{-- Card de progreso --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
        
        {{-- Estado --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <span x-show="estado === 'pendiente'" class="flex items-center gap-2 text-amber-700 font-medium text-sm">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    En cola esperando procesador...
                </span>
                <span x-show="estado === 'procesando'" class="flex items-center gap-2 text-blue-700 font-medium text-sm">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Procesando datos...
                </span>
                <span x-show="estado === 'completada'" class="flex items-center gap-2 text-green-700 font-medium text-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Importación completada
                </span>
                <span x-show="estado === 'fallida'" class="flex items-center gap-2 text-red-700 font-medium text-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    Error en importación
                </span>
            </div>

            <div class="text-right">
                <p class="text-3xl font-bold text-gray-900 tabular-nums" x-text="porcentaje + '%'"></p>
                <p class="text-xs text-gray-500">
                    <span x-text="procesadas"></span> de <span x-text="total || '?'"></span>
                </p>
            </div>
        </div>

        {{-- Barra de progreso --}}
        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden mb-4">
            <div class="h-full rounded-full transition-all duration-500 ease-out"
                 :class="{
                     'bg-blue-500': estado === 'procesando' || estado === 'pendiente',
                     'bg-green-500': estado === 'completada',
                     'bg-red-500': estado === 'fallida',
                 }"
                 :style="'width: ' + porcentaje + '%'">
            </div>
        </div>

        {{-- Stats mini --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-500 uppercase font-medium">Total</p>
                <p class="text-xl font-bold text-gray-900 tabular-nums" x-text="total || '—'"></p>
            </div>
            <div class="bg-green-50 rounded-lg p-3 text-center">
                <p class="text-xs text-green-700 uppercase font-medium">Exitosas</p>
                <p class="text-xl font-bold text-green-800 tabular-nums" x-text="exitosas"></p>
            </div>
            <div class="bg-red-50 rounded-lg p-3 text-center">
                <p class="text-xs text-red-700 uppercase font-medium">Fallidas</p>
                <p class="text-xl font-bold text-red-800 tabular-nums" x-text="fallidas"></p>
            </div>
        </div>

        {{-- Mensaje de error si falló --}}
        <div x-show="estado === 'fallida' && mensajeError" class="mt-4 bg-red-50 border border-red-200 rounded-lg p-3" style="display:none">
            <p class="text-sm text-red-800"><strong>Error:</strong> <span x-text="mensajeError"></span></p>
        </div>

        {{-- Botón cuando termina --}}
        <div x-show="estado === 'completada'" class="mt-6 flex flex-col sm:flex-row gap-3" style="display:none">
            <a :href="'/admin/importar/' + importacionId + '/resultados'"
               class="flex-1 inline-flex items-center justify-center gap-2 bg-gray-900 text-white font-medium px-4 py-2.5 rounded-lg hover:bg-gray-800 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Ver resultados detallados
            </a>
            <a href="{{ route('admin.importar.index') }}"
               class="flex-1 inline-flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 font-medium px-4 py-2.5 rounded-lg hover:bg-gray-50 transition">
                Nueva importación
            </a>
        </div>
    </div>

    {{-- Aviso para poder navegar --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800 flex items-start gap-3">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="font-medium">La importación corre en segundo plano</p>
            <p class="text-xs mt-1">
                Puedes navegar a cualquier sección del panel. Cuando vuelvas o consultes el <a href="{{ route('admin.importar.historial') }}" class="underline font-semibold">historial de importaciones</a>, verás el estado actualizado.
            </p>
        </div>
    </div>

</div>

@push('scripts')
<script>
function progresoImportacion(importacionId) {
    return {
        importacionId: importacionId,
        estado: 'pendiente',
        total: 0,
        procesadas: 0,
        exitosas: 0,
        fallidas: 0,
        porcentaje: 0,
        mensajeError: null,
        intervalId: null,

        iniciar() {
            this.consultar();
            this.intervalId = setInterval(() => this.consultar(), 2000);
        },

        async consultar() {
            try {
                const res = await fetch('/admin/importar/' + this.importacionId + '/estado', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();

                this.estado = data.estado;
                this.total = data.total;
                this.procesadas = data.procesadas;
                this.exitosas = data.exitosas;
                this.fallidas = data.fallidas;
                this.porcentaje = data.porcentaje;
                this.mensajeError = data.mensaje_error;

                // Detener polling si terminó
                if (data.estado === 'completada' || data.estado === 'fallida') {
                    clearInterval(this.intervalId);
                    this.intervalId = null;
                }
            } catch (e) {
                console.error('Error consultando estado:', e);
            }
        },
    };
}
</script>
@endpush
@endsection
