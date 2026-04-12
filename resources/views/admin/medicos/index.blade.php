@extends('admin.layouts.app')

@section('title', 'Médicos')
@section('page-title', 'Médicos')

@section('content')

{{-- ── Cabecera: filtros + botón nuevo ─────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-end gap-3 mb-5">

    <form method="GET" action="{{ route('admin.medicos.index') }}"
          class="flex flex-wrap items-end gap-3 flex-1">

        <div class="flex-1 min-w-48">
            <label class="block text-xs font-medium text-gray-600 mb-1">Buscar nombre o especialidad</label>
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Ej: Pediatría..."
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <button type="submit"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
            🔍 Filtrar
        </button>

        @if (request()->filled('buscar'))
            <a href="{{ route('admin.medicos.index') }}"
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                ✕ Limpiar
            </a>
        @endif
    </form>

    <a href="{{ route('admin.medicos.create') }}"
       class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2 whitespace-nowrap">
        ➕ Nuevo médico
    </a>
</div>

{{-- ── Tabla ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-left text-gray-600 font-semibold text-xs uppercase tracking-wide">
                    <th class="px-5 py-3">Médico</th>
                    <th class="px-5 py-3">Especialidad</th>
                    <th class="px-5 py-3">Registro médico</th>
                    <th class="px-5 py-3">Correo</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($medicos as $m)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    {{ strtoupper(substr($m->usuario->nombre ?? '?', 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-800">{{ $m->usuario->nombre ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2.5 py-0.5 bg-violet-100 text-violet-700 rounded-full text-xs font-medium">
                                {{ $m->especialidad }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-600 font-mono text-xs">{{ $m->registro_medico }}</td>
                        <td class="px-5 py-3 text-gray-600 max-w-[180px] truncate">{{ $m->usuario->email ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @if ($m->usuario?->activo)
                                <span class="px-2.5 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Activo</span>
                            @else
                                <span class="px-2.5 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs font-medium">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-center gap-2"
                                 x-data="{ confirmar: false }">
                                <a href="{{ route('admin.medicos.edit', $m) }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium text-xs px-2 py-1 rounded bg-blue-50 hover:bg-blue-100 transition">
                                    ✏️ Editar
                                </a>
                                <button @click="confirmar = !confirmar"
                                    class="text-red-500 hover:text-red-700 font-medium text-xs px-2 py-1 rounded bg-red-50 hover:bg-red-100 transition">
                                    🗑️ Eliminar
                                </button>

                                <div x-show="confirmar" x-transition class="absolute z-10" style="display:none">
                                    <div class="bg-white border border-red-200 rounded-xl shadow-lg p-4 w-60">
                                        <p class="text-sm text-gray-700 mb-3">¿Eliminar al médico <strong>{{ $m->usuario->nombre ?? '' }}</strong>?</p>
                                        <p class="text-xs text-gray-500 mb-3">El usuario quedará desactivado y no podrá iniciar sesión.</p>
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('admin.medicos.destroy', $m) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg font-medium transition">
                                                    Sí, eliminar
                                                </button>
                                            </form>
                                            <button @click="confirmar = false"
                                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs rounded-lg transition">
                                                Cancelar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                            <div class="text-4xl mb-2">🩺</div>
                            <p>No se encontraron médicos.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if ($medicos->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-sm text-gray-500">
                Mostrando {{ $medicos->firstItem() }}–{{ $medicos->lastItem() }}
                de {{ $medicos->total() }} médicos
            </p>
            <div class="flex items-center gap-1">
                @if ($medicos->onFirstPage())
                    <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed">← Ant.</span>
                @else
                    <a href="{{ $medicos->previousPageUrl() }}"
                       class="px-3 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">← Ant.</a>
                @endif

                @foreach ($medicos->getUrlRange(max(1, $medicos->currentPage()-2), min($medicos->lastPage(), $medicos->currentPage()+2)) as $page => $url)
                    @if ($page == $medicos->currentPage())
                        <span class="px-3 py-1.5 text-sm font-semibold bg-blue-600 text-white border border-blue-600 rounded-lg">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="px-3 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($medicos->hasMorePages())
                    <a href="{{ $medicos->nextPageUrl() }}"
                       class="px-3 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Sig. →</a>
                @else
                    <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed">Sig. →</span>
                @endif
            </div>
        </div>
    @endif
</div>

@endsection
