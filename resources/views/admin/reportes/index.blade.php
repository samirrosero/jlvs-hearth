@extends('admin.layouts.app')

@section('title', 'Reportes')
@section('page-title', 'Reportes')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- ── Reporte de Citas ──────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" x-data="{
        fecha_desde: '',
        fecha_hasta: '',
        estado_id: '',
        medico_id: ''
    }">
        <div class="flex items-center gap-3 mb-5">
            <img src="{{ asset('img/icons/citas-total.png') }}" alt="Citas" class="w-8 h-8 flex-shrink-0 opacity-80">
            <div>
                <h3 class="font-semibold text-gray-800">Reporte de Citas</h3>
                <p class="text-xs text-gray-500">Exporta citas con filtros por fechas, estado o médico</p>
            </div>
        </div>

        <div class="space-y-3 mb-5">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
                    <input type="date" x-model="fecha_desde"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
                    <input type="date" x-model="fecha_hasta"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <a :href="'/reportes/citas/pdf?' + new URLSearchParams({
                    fecha_desde, fecha_hasta, estado_id, medico_id
                }).toString().replace(/=[&]|=$/g, '').replace(/[?&](\w+)=(?=&|$)/g, '')"
               target="_blank"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-red-400 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition">
                <img src="{{ asset('img/icons/pdf.png') }}" alt="PDF" class="w-4 h-4 flex-shrink-0"> Descargar PDF
            </a>
            <a :href="'/reportes/citas/excel?' + new URLSearchParams({
                    fecha_desde, fecha_hasta, estado_id, medico_id
                }).toString()"
               target="_blank"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition">
                <img src="{{ asset('img/icons/excel.png') }}" alt="Excel" class="w-4 h-4 flex-shrink-0"> Descargar Excel
            </a>
        </div>

        <p class="text-xs text-gray-400 mt-3">
            Si no seleccionas fechas, se exportan todas las citas de la empresa.
        </p>
    </div>

    {{-- ── Reporte de Pacientes ──────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" x-data="{
        buscar: '',
        sexo: ''
    }">
        <div class="flex items-center gap-3 mb-5">
            <img src="{{ asset('img/icons/pacientes.png') }}" alt="Pacientes" class="w-8 h-8 flex-shrink-0 opacity-80">
            <div>
                <h3 class="font-semibold text-gray-800">Reporte de Pacientes</h3>
                <p class="text-xs text-gray-500">Exporta el padrón de pacientes con filtros opcionales</p>
            </div>
        </div>

        <div class="space-y-3 mb-5">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Buscar nombre o cédula</label>
                <input type="text" x-model="buscar" placeholder="Ej: López..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sexo</label>
                <select x-model="sexo"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                </select>
            </div>
        </div>

        <div class="flex gap-3">
            <a :href="'/reportes/pacientes/pdf?' + new URLSearchParams({ buscar, sexo }).toString()"
               target="_blank"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-red-400 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition">
                <img src="{{ asset('img/icons/pdf.png') }}" alt="PDF" class="w-4 h-4 flex-shrink-0"> Descargar PDF
            </a>
            <a :href="'/reportes/pacientes/excel?' + new URLSearchParams({ buscar, sexo }).toString()"
               target="_blank"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition">
                <img src="{{ asset('img/icons/excel.png') }}" alt="Excel" class="w-4 h-4 flex-shrink-0"> Descargar Excel
            </a>
        </div>

        <p class="text-xs text-gray-400 mt-3">
            Si no aplicas filtros, se exportan todos los pacientes de la empresa.
        </p>
    </div>

    {{-- ── Reporte de Médicos ───────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-3 mb-5">
            <img src="{{ asset('img/icons/medicos.png') }}" alt="Médicos" class="w-8 h-8 flex-shrink-0 opacity-80">
            <div>
                <h3 class="font-semibold text-gray-800">Reporte de Médicos</h3>
                <p class="text-xs text-gray-500">Exporta el directorio de médicos de la IPS</p>
            </div>
        </div>

        <p class="text-xs text-gray-400 mb-5">
            Incluye nombre, identificación, correo, especialidad, registro médico y estado de cuenta.
        </p>

        <div class="flex gap-3">
            <a href="{{ route('reportes.medicos.pdf') }}" target="_blank"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-red-400 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition">
                <img src="{{ asset('img/icons/pdf.png') }}" alt="PDF" class="w-4 h-4 flex-shrink-0"> Descargar PDF
            </a>
            <a href="{{ route('reportes.medicos.excel') }}" target="_blank"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition">
                <img src="{{ asset('img/icons/excel.png') }}" alt="Excel" class="w-4 h-4 flex-shrink-0"> Descargar Excel
            </a>
        </div>
    </div>

</div>

{{-- ── Nota legal ──────────────────────────────────────────────── --}}
<div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
    <img src="{{ asset('img/icons/legal.png') }}" alt="Normativa" class="w-6 h-6 flex-shrink-0 opacity-80">
    <div>
        <p class="text-sm font-medium text-blue-800">Normativa colombiana</p>
        <p class="text-xs text-blue-600 mt-0.5">
            Los reportes de historias clínicas y datos de pacientes están sujetos a la
            <strong>Resolución 1995 de 1999</strong> del Ministerio de Salud de Colombia.
            Estos documentos son de uso interno y confidencial.
        </p>
    </div>
</div>

@endsection
