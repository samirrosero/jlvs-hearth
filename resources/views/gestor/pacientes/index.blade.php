@extends('gestor.layouts.app')

@section('title', 'Pacientes')
@section('page-title', 'Pacientes')

@section('content')
<div class="space-y-5">

    {{-- ── Encabezado ── --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h2 class="text-xl font-bold text-gray-900">Directorio de pacientes</h2>
        <a href="{{ route('gestor.pacientes.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Registrar paciente
        </a>
    </div>

    {{-- ── Barra de búsqueda ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
        <form method="GET" action="{{ route('gestor.pacientes') }}" class="flex items-end gap-3">
            <div class="flex-1">
                <label for="buscar" class="block text-sm font-medium text-gray-700 mb-1">Buscar paciente</label>
                <input type="text"
                       id="buscar"
                       name="buscar"
                       value="{{ request('buscar') }}"
                       placeholder="Nombre completo o número de identificación…"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
                    Buscar
                </button>
                @if(request('buscar'))
                    <a href="{{ route('gestor.pacientes') }}"
                       class="text-sm text-gray-600 hover:text-gray-800 font-medium px-3 py-2.5 transition-colors">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── Tabla ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        @if($pacientes->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-sm font-medium text-gray-500">
                    @if(request('buscar'))
                        No se encontraron pacientes para "{{ request('buscar') }}"
                    @else
                        Aún no hay pacientes registrados
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    <a href="{{ route('gestor.pacientes.create') }}" class="text-blue-600 hover:underline">Registra el primer paciente</a>.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre completo</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Identificación</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Teléfono</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Correo</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($pacientes as $paciente)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5">
                                    <p class="font-medium text-gray-800">{{ $paciente->nombre_completo }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 tabular-nums whitespace-nowrap">
                                    {{ $paciente->identificacion }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 whitespace-nowrap">
                                    {{ $paciente->telefono ?: '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-600">
                                    {{ $paciente->correo ?: '—' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <a href="{{ route('gestor.citas.create') }}?paciente_id={{ $paciente->id }}"
                                       class="inline-flex items-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Agendar cita
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($pacientes->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $pacientes->withQueryString()->links('vendor.pagination.simple-tailwind') }}
                </div>
            @endif
        @endif

    </div>

</div>
@endsection
