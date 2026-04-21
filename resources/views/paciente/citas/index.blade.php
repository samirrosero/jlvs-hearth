@extends('paciente.layouts.app')

@section('title', 'Mis Citas')
@section('page-title', 'Mis Citas')

@section('content')

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
                    <th class="px-6 py-4">Estado</th>
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
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold" 
                                  style="background: {{ $cita->estado->color_hex ?? '#e2e8f0' }}22; color: {{ $cita->estado->color_hex ?? '#64748b' }}">
                                {{ $cita->estado->nombre }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
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

@endsection
