@extends('admin.layouts.app')
@section('title', 'Resultados de Importación')
@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Resultados de la Importación</h1>
        <p class="text-gray-600 mt-1">
            {{ ucfirst($importacion->tipo) }} — {{ $importacion->nombre_archivo }}
            @if($importacion->finalizado_en)
                <span class="text-gray-400">· Finalizado {{ $importacion->finalizado_en->format('d/m/Y H:i') }}</span>
            @endif
        </p>
    </div>

    @php
        $resultados = [
            'exitosos' => $importacion->exitosas,
            'fallidos' => $importacion->fallidas,
            'usuarios_creados' => $importacion->usuarios_creados ?? [],
            'errores' => $importacion->errores ?? [],
        ];
    @endphp

    {{-- Resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        {{-- Exitosos --}}
        <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-green-800">{{ $resultados['exitosos'] ?? 0 }}</p>
            <p class="text-sm text-green-600">Registros importados</p>
        </div>

        {{-- Fallidos --}}
        <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-red-800">{{ $resultados['fallidos'] ?? 0 }}</p>
            <p class="text-sm text-red-600">Registros con error</p>
        </div>

        {{-- Total --}}
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center">
            <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-800">
                {{ ($resultados['exitosos'] ?? 0) + ($resultados['fallidos'] ?? 0) }}
            </p>
            <p class="text-sm text-gray-600">Total procesados</p>
        </div>
    </div>

    {{-- Usuarios creados con credenciales --}}
    @if(!empty($resultados['usuarios_creados']))
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Usuarios creados con credenciales temporales
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Se envió un correo a cada usuario con su contraseña temporal y link de acceso.
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Nombre</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Correo</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Rol</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Contraseña Temporal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($resultados['usuarios_creados'] as $usuario)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 font-medium text-gray-900">{{ $usuario['nombre'] }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $usuario['correo'] }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded font-medium">
                                {{ ucwords(str_replace('_', ' ', $usuario['rol'])) }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <code class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded font-mono">
                                {{ $usuario['password_temporal'] }}
                            </code>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Botón descargar CSV --}}
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <button onclick="descargarCredenciales()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Descargar listado de credenciales (CSV)
            </button>
        </div>
    </div>
    @endif

    {{-- Errores --}}
    @if(!empty($resultados['errores']))
    <div class="bg-red-50 border border-red-200 rounded-xl overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-red-200">
            <h2 class="font-semibold text-red-800 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                Errores encontrados
            </h2>
        </div>
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-red-100 border-b border-red-200 sticky top-0">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-red-800">Fila</th>
                        <th class="px-6 py-3 text-left font-medium text-red-800">Error</th>
                        <th class="px-6 py-3 text-left font-medium text-red-800">Datos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-red-100">
                    @foreach($resultados['errores'] as $error)
                    <tr class="hover:bg-red-100/50">
                        <td class="px-6 py-3 text-red-700 font-medium">{{ $error['fila'] }}</td>
                        <td class="px-6 py-3 text-red-600">{{ $error['error'] }}</td>
                        <td class="px-6 py-3 text-gray-600 text-xs">
                            <details>
                                <summary class="cursor-pointer hover:text-red-700">Ver datos</summary>
                                <pre class="mt-2 p-2 bg-red-100 rounded text-red-800 overflow-x-auto">{{ json_encode($error['datos'], JSON_PRETTY_PRINT) }}</pre>
                            </details>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Acciones --}}
    <div class="flex gap-4">
        <a href="{{ route('admin.importar.index') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-gray-900 text-white font-medium rounded-xl hover:bg-gray-800 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Nueva importación
        </a>
        <a href="{{ route('admin.dashboard') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Ir al Dashboard
        </a>
    </div>

</div>

<script>
function descargarCredenciales() {
    const usuarios = @json($resultados['usuarios_creados'] ?? []);
    if (usuarios.length === 0) return;

    let csv = '\uFEFF'; // BOM UTF-8
    csv += 'Nombre,Correo,Rol,Contraseña Temporal\n';

    usuarios.forEach(u => {
        csv += `"${u.nombre}","${u.correo}","${u.rol}","${u.password_temporal}"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'credenciales_importadas_{{ date('Y-m-d_H-i') }}.csv';
    link.click();
}
</script>
@endsection
