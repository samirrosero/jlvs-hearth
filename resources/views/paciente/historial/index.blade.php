@extends('paciente.layouts.app')

@section('title', 'Mi Historial')
@section('page-title', 'Mi Historial Clínico')

@section('content')

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
        <h3 class="font-bold text-gray-800 text-lg">Registros Clínicos</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-gray-50 text-gray-500 font-bold uppercase text-[10px] tracking-wider border-b border-gray-100">
                    <th class="px-6 py-4">Fecha de Registro</th>
                    <th class="px-6 py-4">Médico Tratante</th>
                    <th class="px-6 py-4">Diagnóstico</th>
                    <th class="px-6 py-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($historias as $h)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4">
                            <p class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($h->created_at)->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($h->created_at)->format('h:i A') }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-800">{{ $h->ejecucionCita?->cita?->medico?->usuario?->nombre ?? 'N/A' }}</p>
                            <p class="text-[10px] text-gray-400 font-bold uppercase">{{ $h->ejecucionCita?->cita?->medico?->especialidad ?? 'IPS' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-gray-600 line-clamp-1 max-w-xs">{{ $h->diagnostico ?? 'Ver detalle...' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('paciente.historial.show', $h) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-600 hover:text-white transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Ver
                                </a>
                                <a href="{{ route('paciente.historial.pdf', $h) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg text-xs font-bold hover:bg-gray-600 hover:text-white transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <p class="text-gray-400 italic">No tienes historias clínicas registradas aún.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($historias->hasPages())
        <div class="px-6 py-4 border-t border-gray-50 bg-gray-50/50">
            {{ $historias->links('vendor.pagination.simple-tailwind') }}
        </div>
    @endif
</div>

@endsection
