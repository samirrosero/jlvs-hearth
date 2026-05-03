@extends('medico.layouts.app')

@section('title', 'Mis Pacientes')
@section('page-title', 'Mis Pacientes')

@section('content')

{{-- ── Buscador ─────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('medico.pacientes') }}"
      class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 flex flex-col sm:flex-row gap-3 items-stretch sm:items-end">
    <div class="flex flex-col gap-1 flex-1">
        <label class="text-xs text-gray-500 font-medium">Buscar paciente</label>
        <input type="text" name="buscar" value="{{ request('buscar') }}"
               placeholder="Nombre del paciente..."
               class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
    </div>
    <div class="flex gap-2">
        <button type="submit"
                class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-700 transition flex-1 sm:flex-none">
            Buscar
        </button>
        @if (request('buscar'))
            <a href="{{ route('medico.pacientes') }}"
               class="text-sm text-gray-500 hover:text-gray-800 px-3 py-2 rounded-lg border border-gray-200 transition text-center">
                Limpiar
            </a>
        @endif
    </div>
</form>

{{-- ── Lista de pacientes ───────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[700px]">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Paciente</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Identificación</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Última consulta</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Consultas</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($pacientes as $paciente)
                @php $ultimaCita = $paciente->citas->first(); @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-blue-700 font-bold text-xs">
                                    {{ strtoupper(substr($paciente->nombre_completo ?? 'P', 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $paciente->nombre_completo }}</p>
                                <p class="text-xs text-gray-400">{{ $paciente->sexo ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-gray-600">{{ $paciente->identificacion ?? '—' }}</td>
                    <td class="px-5 py-4 text-gray-600">
                        {{ $ultimaCita ? \Carbon\Carbon::parse($ultimaCita->fecha)->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-5 py-4">
                        <span class="bg-gray-100 text-gray-700 text-xs font-semibold px-2 py-1 rounded-full">
                            {{ $paciente->total_consultas }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <a href="{{ route('medico.pacientes.show', $paciente) }}"
                           class="inline-flex items-center gap-1 text-xs bg-gray-900 text-white px-3 py-1.5 rounded-lg hover:bg-gray-700 transition">
                            Ver historial
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center text-gray-400 text-sm">
                        No tienes pacientes registrados aún.
                    </td>
                </tr>
            @endforelse
        </tbody>
        </table>
    </div>

    @if ($pacientes->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $pacientes->links('vendor.pagination.simple-tailwind') }}
        </div>
    @endif
</div>

@endsection
