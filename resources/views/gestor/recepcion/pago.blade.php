@extends('gestor.layouts.app')
@section('title', 'Registrar Pago')
@section('page-title', 'Registrar Pago')
@section('page-subtitle', 'Cita: ' . $cita->hora . ' - ' . $cita->servicio?->nombre)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Navegación --}}
    <a href="{{ route('gestor.recepcion.index') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Volver a recepción
    </a>

    {{-- Info del paciente --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">{{ $paciente->nombre_completo }}</h3>
                <p class="text-sm text-gray-600">
                    {{ $paciente->identificacion }} · 
                    Convenio: <span class="font-medium">{{ $paciente->portafolio?->nombre_convenio ?? 'Particular' }}</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Detalle de la cita --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Detalle de la cita</h2>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Fecha y hora</p>
                    <p class="font-medium text-gray-800">{{ $cita->fecha->format('d/m/Y') }} · {{ $cita->hora }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Modalidad</p>
                    <p class="font-medium text-gray-800">{{ $cita->modalidad?->nombre }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Servicio</p>
                    <p class="font-medium text-gray-800">{{ $cita->servicio?->nombre }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Médico</p>
                    <p class="font-medium text-gray-800">Dr(a). {{ $cita->medico?->usuario?->nombre }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulario de pago --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="font-semibold text-gray-800">Registrar pago</h2>
        </div>
        
        <form method="POST" action="{{ route('gestor.recepcion.pago.store', $cita) }}" class="p-6 space-y-6">
            @csrf

            {{-- Monto --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Monto a pagar <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-lg font-semibold">$</span>
                    <input type="number"
                           name="monto"
                           value="{{ old('monto', $montoSugerido) }}"
                           required
                           min="0"
                           step="100"
                           class="w-full pl-10 pr-4 py-3 text-xl font-bold border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                </div>
                @if($montoSugerido > 0)
                <p class="text-xs text-gray-500 mt-1">
                    Precio sugerido según convenio: ${{ number_format($montoSugerido, 0, ',', '.') }}
                </p>
                @endif
                @error('monto')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Método de pago --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Método de pago <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach(['efectivo' => 'Efectivo', 'tarjeta' => 'Tarjeta', 'transferencia' => 'Transferencia', 'prepagada' => 'Prepagada', 'seguro' => 'Seguro', 'empresarial' => 'Empresarial'] as $value => $label)
                    <label class="relative">
                        <input type="radio" 
                               name="metodo_pago" 
                               value="{{ $value }}"
                               {{ old('metodo_pago') == $value ? 'checked' : '' }}
                               required
                               class="peer sr-only">
                        <div class="p-3 rounded-lg border-2 border-gray-200 text-center cursor-pointer transition peer-checked:border-gray-900 peer-checked:bg-gray-900 peer-checked:text-white hover:border-gray-300">
                            <p class="text-sm font-medium">{{ $label }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('metodo_pago')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Referencia --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Referencia / N° de transacción
                    <span class="text-gray-400 font-normal">(opcional)</span>
                </label>
                <input type="text"
                       name="referencia"
                       value="{{ old('referencia') }}"
                       placeholder="Ej: FACT-001, transacción 12345..."
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                <p class="text-xs text-gray-500 mt-1">
                    Número de factura, comprobante de transferencia, etc.
                </p>
            </div>

            {{-- Observaciones --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Observaciones
                    <span class="text-gray-400 font-normal">(opcional)</span>
                </label>
                <textarea name="observaciones"
                          rows="2"
                          placeholder="Notas adicionales sobre el pago..."
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 resize-none">{{ old('observaciones') }}</textarea>
            </div>

            {{-- Botones --}}
            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('gestor.recepcion.index') }}"
                   class="flex-1 px-4 py-3 text-center text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition font-medium">
                    Cancelar
                </a>
                <button type="submit"
                        class="flex-[2] px-4 py-3 bg-gray-900 hover:bg-gray-800 text-white rounded-lg transition font-semibold">
                    💰 Registrar Pago y Confirmar Llegada
                </button>
            </div>
        </form>
    </div>

    {{-- Nota --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h4 class="font-medium text-amber-900 text-sm">Importante</h4>
                <p class="text-sm text-amber-700 mt-1">
                    Al registrar el pago, se confirmará automáticamente la llegada del paciente y podrá pasar a consulta.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
