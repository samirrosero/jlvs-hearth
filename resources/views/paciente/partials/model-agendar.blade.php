<div x-data="{ openModal: {{ $errors->any() ? 'true' : 'false' }} }"
     x-show="openModal"
     class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">

    <!-- Fondo -->
    <div x-show="openModal"
         class="fixed inset-0 backdrop-blur-sm"
         style="background-color: oklch(0.21 0.03 264.67 / 0.7)"
         @click="openModal = false"></div>

    <!-- Modal -->
    <div x-show="openModal"
         class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 mx-4 my-8 z-10 max-h-[90vh] overflow-y-auto">

        <!-- Header -->
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-bold text-gray-900">Agendar Nueva Cita</h3>
            <button @click="openModal = false" class="text-gray-400 hover:text-gray-500">
                ✖
            </button>
        </div>

        <!-- 🔴 ERRORES GENERALES -->
        @if ($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
                <div class="font-bold mb-1">❌ No se pudo agendar la cita:</div>
                <ul class="text-xs ml-4 list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- FORMULARIO -->
        <form action="{{ route('paciente.citas.store') }}" method="POST">
            @csrf

            <div class="space-y-4">

                <!-- MÉDICO -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Médico</label>
                    <select name="medico_id" required
                        class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl p-2.5">
                        <option value="">Seleccione un médico...</option>
                        @foreach($medicos as $medico)
                            <option value="{{ $medico->id }}">
                                {{ $medico->usuario->nombre }}
                            </option>
                        @endforeach
                    </select>

                    <!-- 🔴 ERROR ESPECÍFICO -->
                    @error('medico_id')
                        <div class="text-red-500 text-xs mt-1">
                            ❌ {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- SERVICIO -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Servicio</label>
                    <select name="servicio_id" required
                        class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl p-2.5">
                        <option value="">Seleccione un servicio...</option>
                        @foreach($servicios as $servicio)
                            <option value="{{ $servicio->id }}">{{ $servicio->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- MODALIDAD -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Modalidad</label>
                    <select name="modalidad_id" required
                        class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl p-2.5">
                        <option value="">Seleccione...</option>
                        @foreach($modalidades as $modalidad)
                            <option value="{{ $modalidad->id }}">{{ $modalidad->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- FECHA Y HORA -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha</label>
                        <input type="date" name="fecha" required min="{{ date('Y-m-d') }}"
                            class="w-full bg-gray-50 border border-gray-200 text-sm rounded-xl p-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Hora</label>
                        <input type="time" name="hora" required
                            class="w-full bg-gray-50 border border-gray-200 text-sm rounded-xl p-2.5">
                    </div>
                </div>

            </div>

            <!-- BOTONES -->
            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                    @click="openModal = false"
                    class="bg-white border border-gray-200 px-4 py-2 rounded-xl text-sm font-bold">
                    Cancelar
                </button>

                <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold">
                    Confirmar Cita
                </button>
            </div>
        </form>
    </div>
</div>
