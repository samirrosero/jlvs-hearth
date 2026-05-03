@extends('admin.layouts.app')
@section('title', 'Historial de Importaciones')
@section('content')
<div class="max-w-6xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Historial de Importaciones</h1>
            <p class="text-sm text-gray-600 mt-1">Registro de todas las importaciones realizadas.</p>
        </div>
        <a href="{{ route('admin.importar.index') }}"
           class="inline-flex items-center gap-2 bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva importación
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Tipo</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Archivo</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Estado</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Progreso</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Fecha</th>
                        <th class="px-6 py-3 text-right font-medium text-gray-700">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($importaciones as $imp)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 font-medium text-gray-900">{{ ucfirst($imp->tipo) }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $imp->nombre_archivo }}</td>
                        <td class="px-6 py-3">
                            @php
                                $badges = [
                                    'pendiente'   => 'bg-amber-100 text-amber-700',
                                    'procesando'  => 'bg-blue-100 text-blue-700',
                                    'completada'  => 'bg-green-100 text-green-700',
                                    'fallida'     => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badges[$imp->estado] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($imp->estado) }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2 min-w-[140px]">
                                <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-full bg-blue-500 rounded-full" style="width: {{ $imp->porcentaje }}%"></div>
                                </div>
                                <span class="text-xs text-gray-600 font-medium tabular-nums">{{ $imp->procesadas }}/{{ $imp->total_filas ?: '?' }}</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                <span class="text-green-600">✓ {{ $imp->exitosas }}</span> ·
                                <span class="text-red-600">✗ {{ $imp->fallidas }}</span>
                            </p>
                        </td>
                        <td class="px-6 py-3 text-gray-600 text-xs">
                            {{ $imp->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-3 text-right">
                            @if(in_array($imp->estado, ['pendiente', 'procesando']))
                                <a href="{{ route('admin.importar.progreso', $imp->id) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Ver progreso →</a>
                            @else
                                <a href="{{ route('admin.importar.resultados', $imp->id) }}" class="text-gray-700 hover:text-gray-900 text-xs font-medium">Ver resultados →</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No hay importaciones registradas aún.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($importaciones->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $importaciones->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
