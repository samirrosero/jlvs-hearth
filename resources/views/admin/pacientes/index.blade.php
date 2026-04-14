@extends('admin.layouts.app')

@section('title', 'Pacientes')
@section('page-title', 'Pacientes')

@section('content')

{{-- ── Cabecera: filtros + botón nuevo ────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-end gap-3 mb-5">

    <form method="GET" action="{{ route('admin.pacientes.index') }}"
          class="flex flex-wrap items-end gap-3 flex-1">

        {{-- Buscador --}}
        <div class="flex-1 min-w-48">
            <label class="block text-xs font-medium text-gray-600 mb-1">Buscar nombre o cédula</label>
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Ej: María López..."
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Filtro sexo --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Sexo</label>
            <select name="sexo"
                class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="M" {{ request('sexo') === 'M' ? 'selected' : '' }}>Masculino</option>
                <option value="F" {{ request('sexo') === 'F' ? 'selected' : '' }}>Femenino</option>
                <option value="Otro" {{ request('sexo') === 'Otro' ? 'selected' : '' }}>Otro</option>
            </select>
        </div>

        {{-- Filtro edad --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Grupo de edad</label>
            <select name="edad"
                class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="0-17"  {{ request('edad') === '0-17'  ? 'selected' : '' }}>Menores (0–17)</option>
                <option value="18-40" {{ request('edad') === '18-40' ? 'selected' : '' }}>Adultos jóvenes (18–40)</option>
                <option value="41-65" {{ request('edad') === '41-65' ? 'selected' : '' }}>Adultos (41–65)</option>
                <option value="65+"   {{ request('edad') === '65+'   ? 'selected' : '' }}>Adultos mayores (65+)</option>
            </select>
        </div>

        <button type="submit"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
            🔍 Filtrar
        </button>

        @if (request()->hasAny(['buscar', 'sexo', 'edad']))
            <a href="{{ route('admin.pacientes.index') }}"
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                ✕ Limpiar
            </a>
        @endif
    </form>

    <a href="{{ route('admin.pacientes.create') }}"
       class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2 whitespace-nowrap">
        ➕ Nuevo paciente
    </a>
</div>

{{-- ── Tabla ─────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-left text-gray-600 font-semibold text-xs uppercase tracking-wide">
                    <th class="px-5 py-3">Nombre completo</th>
                    <th class="px-5 py-3">Identificación</th>
                    <th class="px-5 py-3">Sexo</th>
                    <th class="px-5 py-3">Fecha nac.</th>
                    <th class="px-5 py-3">Teléfono</th>
                    <th class="px-5 py-3">Correo</th>
                    <th class="px-5 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($pacientes as $p)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $p->nombre_completo }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $p->identificacion }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $p->sexo === 'F' ? 'bg-pink-100 text-pink-700' : ($p->sexo === 'M' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ $p->sexo }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-600">
                            {{ \Carbon\Carbon::parse($p->fecha_nacimiento)->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $p->telefono ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600 max-w-[180px] truncate">{{ $p->correo ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-center gap-2"
                                 x-data="{ confirmar: false }">
                                <a href="{{ route('admin.pacientes.edit', $p) }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium transition text-xs px-2 py-1 rounded bg-blue-50 hover:bg-blue-100">
                                    ✏️ Editar
                                </a>

                                {{-- Botón eliminar con confirmación inline --}}
                                <button @click="confirmar = !confirmar"
                                    class="text-red-500 hover:text-red-700 font-medium transition text-xs px-2 py-1 rounded bg-red-50 hover:bg-red-100">
                                    🗑️ Eliminar
                                </button>

                                <div x-show="confirmar" x-transition class="absolute z-10 mt-1" style="display:none">
                                    <div class="bg-white border border-red-200 rounded-xl shadow-lg p-4 w-56">
                                        <p class="text-sm text-gray-700 mb-3">¿Eliminar a <strong>{{ $p->nombre_completo }}</strong>?</p>
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('admin.pacientes.destroy', $p) }}">
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
                        <td colspan="7" class="px-5 py-10 text-center text-gray-400">
                            <div class="text-4xl mb-2">👥</div>
                            <p>No se encontraron pacientes.</p>
                            @if (request()->hasAny(['buscar', 'sexo', 'edad']))
                                <a href="{{ route('admin.pacientes.index') }}" class="text-blue-600 hover:underline text-sm mt-1 inline-block">
                                    Ver todos
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if ($pacientes->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-sm text-gray-500">
                Mostrando {{ $pacientes->firstItem() }}–{{ $pacientes->lastItem() }}
                de {{ $pacientes->total() }} pacientes
            </p>
            <div class="flex items-center gap-1">
                {{-- Anterior --}}
                @if ($pacientes->onFirstPage())
                    <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed">← Ant.</span>
                @else
                    <a href="{{ $pacientes->previousPageUrl() }}"
                       class="px-3 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">← Ant.</a>
                @endif

                {{-- Páginas --}}
                @foreach ($pacientes->getUrlRange(max(1, $pacientes->currentPage()-2), min($pacientes->lastPage(), $pacientes->currentPage()+2)) as $page => $url)
                    @if ($page == $pacientes->currentPage())
                        <span class="px-3 py-1.5 text-sm font-semibold bg-blue-600 text-white border border-blue-600 rounded-lg">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="px-3 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Siguiente --}}
                @if ($pacientes->hasMorePages())
                    <a href="{{ $pacientes->nextPageUrl() }}"
                       class="px-3 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Sig. →</a>
                @else
                    <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed">Sig. →</span>
                @endif
            </div>
        </div>
    @endif
</div>

@endsection
