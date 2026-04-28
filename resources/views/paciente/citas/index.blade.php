@extends('paciente.layouts.app')

@section('title', 'Mis Citas')
@section('page-title', 'Mis Citas')

@section('content')

<div x-data="{ modalCancelar: false, formCancelar: null }"
     @cancelar-cita.window="modalCancelar = true; formCancelar = $event.detail.form">

    {{-- Modal confirmación cancelar --}}
    <div x-show="modalCancelar" class="fixed inset-0 z-50 flex items-center justify-center" style="display:none">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="modalCancelar = false"
             x-transition:enter="transition-opacity duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6 z-10"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Cancelar cita</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Esta acción no se puede deshacer.</p>
                </div>
            </div>

            <p class="text-sm text-gray-600 mb-6">
                ¿Estás seguro de que deseas cancelar esta cita? El cupo quedará libre para otro paciente.
            </p>

            <div class="flex justify-end gap-3">
                <button type="button" @click="modalCancelar = false"
                        class="px-4 py-2 text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                    No, mantener
                </button>
                <button type="button" @click="formCancelar.submit()"
                        class="px-4 py-2 text-sm font-bold text-white bg-red-600 rounded-xl hover:bg-red-700 transition">
                    Sí, cancelar cita
                </button>
            </div>
        </div>
    </div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <h3 class="font-bold text-gray-800 text-lg">Historial de Citas</h3>
        
        <form action="{{ route('paciente.citas') }}" method="GET" class="flex items-center gap-2">
            <select name="estado_id" onchange="this.form.submit()" 
                    class="text-xs border-gray-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition">
                <option value="">Todos los estados</option>
                @foreach ($estados as $e)
                    <option value="{{ $e->id }}" {{ request('estado_id') == $e->id ? 'selected' : '' }}>{{ $e->nombre }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-gray-50 text-gray-500 font-bold uppercase text-[10px] tracking-wider border-b border-gray-100">
                    <th class="px-6 py-4">Fecha y Hora</th>
                    <th class="px-6 py-4">Médico</th>
                    <th class="px-6 py-4">Servicio</th>
                    <th class="px-6 py-4">Modalidad</th>
                    <th class="px-6 py-4">Costo</th>
                    <th class="px-6 py-4">Estado</th>
                    <th class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($citas as $cita)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4">
                            <p class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($cita->hora)->format('h:i A') }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-800">{{ $cita->medico->usuario->nombre }}</p>
                            <p class="text-[10px] text-gray-400 font-bold uppercase">{{ $cita->medico->especialidad ?? 'IPS' }}</p>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $cita->servicio->nombre ?? 'Consulta' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded bg-gray-100 text-gray-500 text-[10px] font-bold uppercase">
                                {{ $cita->modalidad->nombre ?? 'Presencial' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $precio = $cita->servicio?->precios
                                    ->firstWhere('portafolio_id', $cita->portafolio_id)
                                    ?->precio;
                            @endphp
                            @if ($precio !== null)
                                <span class="font-semibold text-gray-800">
                                    ${{ number_format($precio, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold"
                                  style="background: {{ $cita->estado->color_hex ?? '#e2e8f0' }}22; color: {{ $cita->estado->color_hex ?? '#64748b' }}">
                                {{ $cita->estado->nombre }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if (in_array($cita->estado->nombre, ['Pendiente', 'Confirmada']) && \Carbon\Carbon::parse($cita->fecha)->isFuture())
                                <form method="POST" action="{{ route('paciente.citas.cancelar', $cita) }}" id="form-cancelar-{{ $cita->id }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="button"
                                            @click="$dispatch('cancelar-cita', { form: document.getElementById('form-cancelar-{{ $cita->id }}') })"
                                            class="text-xs font-bold text-red-500 hover:text-red-700 transition">
                                        Cancelar
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <p class="text-gray-400 italic">No se encontraron citas registradas.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($citas->hasPages())
        <div class="px-6 py-4 border-t border-gray-50 bg-gray-50/50">
            {{ $citas->links() }}
        </div>
    @endif
</div>

</div>

@endsection
