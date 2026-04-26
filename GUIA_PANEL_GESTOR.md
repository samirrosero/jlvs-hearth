# Panel Gestor de Citas — Guía de implementación (Julián y José)

## Contexto del proyecto

Sistema multi-tenant para IPS colombianas. El gestor de citas puede crear/editar citas,
registrar pacientes y ver la disponibilidad de los médicos.
**Regla fundamental:** todo query filtrado por `empresa_id = auth()->user()->empresa_id`.
Stack: Laravel + Blade + Tailwind + Alpine.js

---

## 1. Rutas — agregar en `routes/web.php`

```php
// Importar al inicio del archivo
use App\Http\Controllers\Gestor\GestorDashboardController;
use App\Http\Controllers\Gestor\GestorCitasController;
use App\Http\Controllers\Gestor\GestorPacientesController;

// Agregar ANTES del bloque del panel admin
Route::prefix('gestor')->name('gestor.')->middleware(['auth', 'role:gestor_citas'])->group(function () {
    Route::get('/',          fn () => redirect()->route('gestor.dashboard'));
    Route::get('/dashboard', GestorDashboardController::class)->name('dashboard');

    Route::get('/citas',          [GestorCitasController::class, 'index'])->name('citas');
    Route::get('/citas/crear',    [GestorCitasController::class, 'create'])->name('citas.create');
    Route::post('/citas',         [GestorCitasController::class, 'store'])->name('citas.store');
    Route::get('/citas/{cita}',   [GestorCitasController::class, 'show'])->name('citas.show');
    Route::get('/citas/{cita}/editar', [GestorCitasController::class, 'edit'])->name('citas.edit');
    Route::put('/citas/{cita}',   [GestorCitasController::class, 'update'])->name('citas.update');

    Route::get('/pacientes',             [GestorPacientesController::class, 'index'])->name('pacientes');
    Route::get('/pacientes/registrar',   [GestorPacientesController::class, 'create'])->name('pacientes.create');
    Route::post('/pacientes',            [GestorPacientesController::class, 'store'])->name('pacientes.store');
});
```

---

## 2. Redirect del login — editar `LoginController.php`

En `app/Http/Controllers/Admin/LoginController.php`:

```php
// Cambiar:
'gestor_citas' => redirect()->route('admin.dashboard'),

// Por:
'gestor_citas' => redirect()->route('gestor.dashboard'),
```

---

## 3. Controladores — crear en `app/Http/Controllers/Gestor/`

### GestorDashboardController.php

```php
<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Medico;

class GestorDashboardController extends Controller
{
    public function __invoke()
    {
        $empresaId = auth()->user()->empresa_id;
        $hoy       = now()->toDateString();

        $citasHoy       = Cita::where('empresa_id', $empresaId)->where('fecha', $hoy)->count();
        $citasPendientes = Cita::where('empresa_id', $empresaId)
            ->whereHas('estado', fn ($q) => $q->where('nombre', 'like', '%pendiente%'))
            ->count();
        $totalPacientes = Paciente::where('empresa_id', $empresaId)->count();
        $totalMedicos   = Medico::where('empresa_id', $empresaId)->count();

        $proximasCitas = Cita::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->where('fecha', '>=', $hoy)
            ->with('paciente', 'medico.usuario', 'estado', 'servicio')
            ->orderBy('fecha')->orderBy('hora')
            ->limit(8)
            ->get();

        return view('gestor.dashboard', compact(
            'citasHoy', 'citasPendientes', 'totalPacientes', 'totalMedicos', 'proximasCitas'
        ));
    }
}
```

### GestorCitasController.php

```php
<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\Medico;
use App\Models\ModalidadCita;
use App\Models\Paciente;
use App\Models\Servicio;
use Illuminate\Http\Request;

class GestorCitasController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $citas = Cita::where('empresa_id', $empresaId)
            ->with('paciente', 'medico.usuario', 'estado', 'servicio')
            ->when(request('fecha'), fn ($q) => $q->where('fecha', request('fecha')))
            ->when(request('medico_id'), fn ($q) => $q->where('medico_id', request('medico_id')))
            ->when(request('estado_id'), fn ($q) => $q->where('estado_id', request('estado_id')))
            ->orderByDesc('fecha')->orderByDesc('hora')
            ->paginate(15)
            ->withQueryString();

        $estados = EstadoCita::all();
        $medicos = Medico::where('empresa_id', $empresaId)->with('usuario')->get();

        return view('gestor.citas.index', compact('citas', 'estados', 'medicos'));
    }

    public function create()
    {
        $empresaId = auth()->user()->empresa_id;

        // Datos para poblar el formulario
        $pacientes  = Paciente::where('empresa_id', $empresaId)->orderBy('nombre_completo')->get();
        $medicos    = Medico::where('empresa_id', $empresaId)->with('usuario')->get();
        $servicios  = Servicio::where('empresa_id', $empresaId)->get();
        $modalidades = ModalidadCita::all();
        $estados    = EstadoCita::all();

        return view('gestor.citas.create', compact(
            'pacientes', 'medicos', 'servicios', 'modalidades', 'estados'
        ));
    }

    public function store(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'paciente_id'  => ['required', 'exists:pacientes,id'],
            'medico_id'    => ['required', 'exists:medicos,id'],
            'servicio_id'  => ['nullable', 'exists:servicios,id'],
            'modalidad_id' => ['nullable', 'exists:modalidades_cita,id'],
            'estado_id'    => ['required', 'exists:estados_cita,id'],
            'fecha'        => ['required', 'date'],
            'hora'         => ['required', 'date_format:H:i'],
        ]);

        // Verificar que médico y paciente son de esta empresa
        abort_unless(
            Medico::where('id', $data['medico_id'])->where('empresa_id', $empresaId)->exists() &&
            Paciente::where('id', $data['paciente_id'])->where('empresa_id', $empresaId)->exists(),
            403
        );

        Cita::create(array_merge($data, ['empresa_id' => $empresaId, 'activo' => true]));

        return redirect()->route('gestor.citas')->with('exito', 'Cita agendada correctamente.');
    }

    public function edit(Cita $cita)
    {
        abort_if($cita->empresa_id !== auth()->user()->empresa_id, 403);

        $empresaId   = auth()->user()->empresa_id;
        $pacientes   = Paciente::where('empresa_id', $empresaId)->orderBy('nombre_completo')->get();
        $medicos     = Medico::where('empresa_id', $empresaId)->with('usuario')->get();
        $servicios   = Servicio::where('empresa_id', $empresaId)->get();
        $modalidades = ModalidadCita::all();
        $estados     = EstadoCita::all();
        $cita->load('paciente', 'medico', 'estado');

        return view('gestor.citas.edit', compact(
            'cita', 'pacientes', 'medicos', 'servicios', 'modalidades', 'estados'
        ));
    }

    public function update(Request $request, Cita $cita)
    {
        abort_if($cita->empresa_id !== auth()->user()->empresa_id, 403);

        $data = $request->validate([
            'paciente_id'  => ['required', 'exists:pacientes,id'],
            'medico_id'    => ['required', 'exists:medicos,id'],
            'servicio_id'  => ['nullable', 'exists:servicios,id'],
            'modalidad_id' => ['nullable', 'exists:modalidades_cita,id'],
            'estado_id'    => ['required', 'exists:estados_cita,id'],
            'fecha'        => ['required', 'date'],
            'hora'         => ['required', 'date_format:H:i'],
        ]);

        $cita->update($data);

        return redirect()->route('gestor.citas')->with('exito', 'Cita actualizada correctamente.');
    }
}
```

### GestorPacientesController.php

```php
<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;

class GestorPacientesController extends Controller
{
    public function index()
    {
        $pacientes = Paciente::where('empresa_id', auth()->user()->empresa_id)
            ->when(request('buscar'), fn ($q) => $q->where('nombre_completo', 'like', '%' . request('buscar') . '%'))
            ->orderBy('nombre_completo')
            ->paginate(15)
            ->withQueryString();

        return view('gestor.pacientes.index', compact('pacientes'));
    }

    public function create()
    {
        return view('gestor.pacientes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_completo'  => ['required', 'string', 'max:200'],
            'identificacion'   => ['required', 'string', 'max:20'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo'             => ['nullable', 'in:M,F,Otro'],
            'telefono'         => ['nullable', 'string', 'max:20'],
            'correo'           => ['nullable', 'email'],
            'direccion'        => ['nullable', 'string'],
        ]);

        Paciente::create(array_merge($data, [
            'empresa_id' => auth()->user()->empresa_id,
        ]));

        return redirect()->route('gestor.pacientes')->with('exito', 'Paciente registrado correctamente.');
    }
}
```

---

## 4. Layout — crear `resources/views/gestor/layouts/app.blade.php`

Copiar `resources/views/medico/layouts/app.blade.php` y ajustar:

- Cambiar `id="medico-sidebar"` → `id="gestor-sidebar"`
- Cambiar el `$nav`:

```php
$nav = [
    [
        'route' => 'gestor.dashboard',
        'icon'  => $empresa?->icono_dashboard_url ?? asset('img/icons/dashboard.png'),
        'label' => 'Dashboard',
    ],
    [
        'route' => 'gestor.citas',
        'match' => 'gestor.citas*',
        'icon'  => $empresa?->icono_card_citas_url ?? asset('img/icons/citas-mes.png'),
        'label' => 'Citas',
    ],
    [
        'route' => 'gestor.pacientes',
        'match' => 'gestor.pacientes*',
        'icon'  => $empresa?->icono_pacientes_url ?? asset('img/icons/pacientes.png'),
        'label' => 'Pacientes',
    ],
];
```

- El sidebar muestra: nombre del gestor, `rol: Gestor de Citas`, botón logout.

### Branding dinámico — obligatorio en el layout

El sistema es multi-tenant: cada IPS personaliza su logo, colores e íconos desde el panel admin.
**Todo esto debe leerse desde `$empresa`, nunca hardcodeado.**

```blade
{{-- Al inicio del <head> --}}
@php $empresa = auth()->user()?->empresa; @endphp

{{-- Título con nombre de la IPS --}}
<title>@yield('title') — {{ $empresa?->nombre ?? 'JLVS Hearth' }}</title>

{{-- Favicon dinámico --}}
@php $fv = ($empresa?->favicon_url ?? asset('favicon.ico')) . '?v=' . ($empresa?->updated_at?->timestamp ?? '1'); @endphp
<link rel="icon" href="{{ $fv }}" type="image/x-icon">

{{-- Colores del sidebar via CSS variables --}}
<style>
    :root {
        --color-sidebar:    {{ $empresa?->color_gestor     ?? '#0f172a' }};
        --color-primario:   {{ $empresa?->color_primario   ?? '#0369a1' }};
        --color-secundario: {{ $empresa?->color_secundario ?? '#075985' }};
    }
    #gestor-sidebar { background-color: var(--color-sidebar) !important; }
</style>

{{-- Logo en el sidebar --}}
<img src="{{ $empresa?->logo_url ?? asset('img/logos/logo1.png') }}"
     alt="{{ $empresa?->nombre ?? 'JLVS Hearth' }}"
     class="h-10 w-auto object-contain">
```

**Íconos del sidebar** — cada ítem del `$nav` usa el ícono de la empresa con fallback:

```php
$nav = [
    [
        'route' => 'gestor.dashboard',
        'icon'  => $empresa?->icono_dashboard_url ?? asset('img/icons/dashboard.png'),
        'label' => 'Dashboard',
    ],
    // ...
];
```

Campos disponibles en `$empresa` para íconos:

| Campo | Uso |
|---|---|
| `$empresa->logo_url` | Logo principal del sidebar |
| `$empresa->favicon_url` | Favicon del navegador |
| `$empresa->color_gestor` | Color de fondo del sidebar del gestor |
| `$empresa->color_primario` | Color primario de la IPS |
| `$empresa->color_secundario` | Color secundario |
| `$empresa->icono_dashboard_url` | Ícono sección Dashboard |
| `$empresa->icono_pacientes_url` | Ícono sección Pacientes |
| `$empresa->icono_card_citas_url` | Ícono sección Citas |
| `$empresa->icono_medicos_url` | Ícono sección Médicos |

> Si el campo es `null` (la IPS no lo ha personalizado), siempre cae al `asset()` de fallback.

---

## 5. Vistas — crear en `resources/views/gestor/`

```
resources/views/gestor/
├── layouts/
│   └── app.blade.php              ← layout (copiar del médico y adaptar)
├── dashboard.blade.php            ← métricas + próximas citas
├── citas/
│   ├── index.blade.php            ← tabla de citas con filtros
│   ├── create.blade.php           ← formulario inteligente (ver sección 6)
│   └── edit.blade.php             ← igual que create pero con datos precargados
└── pacientes/
    ├── index.blade.php            ← lista con buscador
    └── create.blade.php           ← formulario registro rápido
```

---

## 6. Formulario inteligente de cita (create.blade.php)

Este es el formulario clave. El flujo usa Alpine.js + los endpoints del backend.
**No calcules slots manualmente** — el backend ya tiene dos endpoints para eso.

```
Paso 1: Selecciona especialidad  →  GET /especialidades
Paso 2: Selecciona médico        →  GET /medicos?especialidad=X
Paso 3: Selecciona mes/fecha     →  GET /medicos/{id}/dias-disponibles?mes=YYYY-MM
                                    (el calendario bloquea los días sin horario)
Paso 4: Selecciona hora          →  GET /citas/disponibilidad?medico_id=X&fecha=Y&servicio_id=Z
                                    (el backend devuelve solo los slots libres)
Paso 5: Selecciona paciente      →  buscador en los pacientes de la empresa
Paso 6: Servicio + modalidad     →  dropdowns simples
Paso 7: Confirmar                →  POST /gestor/citas → store()
```

> Si en el paso 4 `disponible` es `false` → mostrar opción de **Lista de espera** (ver sección 7.3)

### Estructura Alpine.js para el formulario:

```html
<div x-data="formularioCita()" class="...">

    <!-- Paso 1: Especialidad -->
    <select x-model="especialidad" @change="cargarMedicos()">
        <option value="">Selecciona especialidad</option>
        <template x-for="esp in especialidades" :key="esp">
            <option :value="esp" x-text="esp"></option>
        </template>
    </select>

    <!-- Paso 2: Médico -->
    <select x-model="medicoId" @change="cargarDiasDelMes()" :disabled="!especialidad">
        <option value="">Selecciona médico</option>
        <template x-for="m in medicos" :key="m.id">
            <option :value="m.id" x-text="m.usuario.nombre"></option>
        </template>
    </select>

    <!-- Paso 3: Fecha (solo habilita días disponibles) -->
    <input type="date" x-model="fecha" @change="cargarSlots()"
           :disabled="!medicoId" :min="hoy">
    <p x-show="fecha && slotsLibres.length === 0 && !cargando" class="text-sm text-red-500">
        El médico no tiene disponibilidad ese día.
    </p>

    <!-- Paso 4: Hora (slots del backend) -->
    <select x-model="hora" :disabled="slotsLibres.length === 0">
        <option value="">Selecciona hora</option>
        <template x-for="slot in slotsLibres" :key="slot">
            <option :value="slot" x-text="slot"></option>
        </template>
    </select>

    <!-- Lista de espera si no hay slots -->
    <div x-show="sinDisponibilidad" class="bg-amber-50 border border-amber-200 rounded-lg p-4 mt-2">
        <p class="text-sm text-amber-800">No hay slots disponibles para esa fecha.</p>
        <button type="button" @click="abrirListaEspera()" class="mt-2 text-sm text-amber-700 underline">
            Registrar en lista de espera
        </button>
    </div>

</div>

<script>
function formularioCita() {
    return {
        especialidades: [],
        medicos:        [],
        slotsLibres:    [],
        diasHabilitados: [],  // fechas 'YYYY-MM-DD' del mes actual con horario
        especialidad:   '',
        medicoId:       '',
        servicioId:     '',
        fecha:          '',
        hora:           '',
        cargando:       false,
        sinDisponibilidad: false,
        hoy: new Date().toISOString().split('T')[0],
        mesActual: new Date().toISOString().slice(0, 7), // 'YYYY-MM'

        async init() {
            const res = await fetch('/especialidades', { headers: headers() });
            this.especialidades = await res.json();
        },

        async cargarMedicos() {
            this.medicoId = ''; this.medicos = []; this.slotsLibres = [];
            const res = await fetch(`/medicos?especialidad=${this.especialidad}`, { headers: headers() });
            this.medicos = await res.json();
        },

        // Llamar cuando cambia el médico O cuando el usuario navega a otro mes en el calendario
        async cargarDiasDelMes(mes = null) {
            this.fecha = ''; this.slotsLibres = []; this.sinDisponibilidad = false;
            if (!this.medicoId) return;
            const m = mes ?? this.mesActual;
            const res = await fetch(`/medicos/${this.medicoId}/dias-disponibles?mes=${m}`, { headers: headers() });
            const data = await res.json();
            this.diasHabilitados = data.dias_disponibles ?? [];
        },

        // Verifica si una fecha está habilitada (para colorear el calendario si lo implementas)
        esDiaDisponible(fecha) {
            return this.diasHabilitados.includes(fecha);
        },

        async cargarSlots() {
            if (!this.fecha || !this.medicoId) return;
            this.cargando = true; this.slotsLibres = []; this.sinDisponibilidad = false;
            const url = `/citas/disponibilidad?medico_id=${this.medicoId}&fecha=${this.fecha}`
                + (this.servicioId ? `&servicio_id=${this.servicioId}` : '');
            const res  = await fetch(url, { headers: headers() });
            const data = await res.json();
            this.slotsLibres       = data.slots ?? [];
            this.sinDisponibilidad = !data.disponible;
            this.cargando = false;
        },

        abrirListaEspera() {
            // Aquí puedes abrir un modal o redirigir al formulario de lista de espera
            // con medicoId, fecha y servicioId precargados
        },
    };
}

function headers() {
    return {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    };
}
</script>
```

> **Tip de UX para el calendario:** si usas una librería como Flatpickr o Pikaday,
> puedes pasar `diasHabilitados` a la opción `enable` del calendario para que el
> usuario solo pueda seleccionar días con horario activo.

---

## 7. Endpoints disponibles para el gestor

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/especialidades` | Especialidades de la empresa |
| GET | `/medicos` | Médicos (filtra por `?especialidad=X` o `?buscar=nombre`) |
| GET | `/medicos/{id}` | Detalle de un médico |
| GET | `/medicos/{id}/dias-disponibles?mes=YYYY-MM` | **Días del mes que trabaja el médico** (ver sección 7.1) |
| GET | `/citas/disponibilidad?medico_id=X&fecha=Y` | **Slots libres de un médico** (ver sección 7.2) |
| GET | `/citas` | Citas de la empresa (filtra por `?fecha=`, `?medico_id=`, `?estado_id=`, `?modalidad_id=`) |
| POST | `/citas` | Crear cita |
| PUT/PATCH | `/citas/{id}` | Editar cita / cambiar estado |
| DELETE | `/citas/{id}` | Eliminar cita |
| GET | `/pacientes` | Pacientes de la empresa |
| POST | `/pacientes` | Crear paciente |
| GET | `/estados-cita` | Estados de cita |
| GET | `/modalidades-cita` | Modalidades |
| GET | `/servicios` | Servicios/procedimientos |
| GET | `/lista-espera` | Lista de espera (ver sección 7.3) |
| POST | `/lista-espera` | Registrar paciente en lista de espera |
| PATCH | `/lista-espera/{id}` | Actualizar estado de lista de espera |
| DELETE | `/lista-espera/{id}` | Eliminar registro de lista de espera |
| POST | `/citas/reasignar-medico` | **Reasignación masiva por médico ausente** (ver sección 7.6) |

> **Nota:** Todos estos endpoints retornan JSON y ya están protegidos con el middleware
> `role:gestor_citas`. Solo necesitas llamarlos con el header `Accept: application/json`
> y el token CSRF.

---

### 7.1 Días disponibles del médico — `GET /medicos/{id}/dias-disponibles`

Devuelve qué días del mes el médico tiene horario activo registrado por el administrador.
Úsalo para deshabilitar en el calendario los días que no trabaja.

**Parámetros:**
| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `mes` | string (YYYY-MM) | ✅ | Mes a consultar |

**Ejemplo de respuesta:**
```json
{
    "medico_id": 5,
    "mes": "2026-04",
    "dias_disponibles": ["2026-04-21", "2026-04-22", "2026-04-24", "2026-04-28", "2026-04-29"]
}
```

> Solo devuelve días **desde hoy en adelante** — las fechas pasadas no aparecen aunque el médico tenga horario.

---

### 7.2 Slots libres del día — `GET /citas/disponibilidad`

Devuelve los horarios libres de un médico para una fecha y servicio específicos.
**Usa este endpoint en lugar de calcular slots manualmente.**

**Parámetros:**
| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `medico_id` | integer | ✅ | ID del médico |
| `fecha` | date (YYYY-MM-DD) | ✅ | Fecha a consultar |
| `servicio_id` | integer | ❌ | Determina la duración del slot (default: 30 min) |

**Ejemplo de respuesta:**
```json
{
  "disponible": true,
  "slots": ["08:00", "08:20", "08:40", "09:00", "09:20", "10:00"],
  "fecha": "2026-04-21",
  "duracion_minutos": 20,
  "servicio": "Consulta Medicina General"
}
```

**Respuesta cuando no hay disponibilidad:**
```json
{
  "disponible": false,
  "slots": [],
  "mensaje": "El médico no tiene horario disponible ese día."
}
```

**Uso en Alpine.js:**
```js
async cargarSlots() {
    if (!this.medicoId || !this.fecha) return;
    const res = await fetch(
        `/citas/disponibilidad?medico_id=${this.medicoId}&fecha=${this.fecha}&servicio_id=${this.servicioId}`,
        { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() } }
    );
    const data = await res.json();
    this.slotsLibres = data.slots ?? [];
    this.hayDisponibilidad = data.disponible;
},
```

> **Importante:** El endpoint ya excluye los slots de citas canceladas y "No asistió",
> por lo que esos horarios vuelven a aparecer como disponibles automáticamente.

---

### 7.3 Lista de espera — `/lista-espera`

Cuando no hay disponibilidad, el gestor puede registrar al paciente en lista de espera.

**Crear registro:**
```js
// POST /lista-espera
{
  "paciente_id": 5,
  "medico_id": 4,           // opcional
  "servicio_id": 1,          // opcional
  "fecha_solicitada": "2026-04-21",
  "notas": "Paciente llegó presencial, no hay slots disponibles"
}
```

**Respuesta:**
```json
{
  "id": 1,
  "estado": "esperando",
  "paciente": { "nombre_completo": "...", "identificacion": "..." },
  "medico": { ... },
  "servicio": { "nombre": "Consulta Medicina General" },
  "fecha_solicitada": "2026-04-21"
}
```

**Cuando se libera un slot (otro paciente cancela o no asiste), el gestor agenda la cita y actualiza la lista:**
```js
// PATCH /lista-espera/{id}
{
  "estado": "asignado",
  "cita_id": 130,   // ID de la cita que se le asignó
  "notas": "Se le asignó la cita liberada de las 10:00"
}
```

**Estados posibles:**
| Estado | Significado |
|--------|-------------|
| `esperando` | Paciente en espera, sin slot asignado |
| `asignado` | Se le encontró un slot y se le agendó cita |
| `descartado` | No se encontró disponibilidad o el paciente desistió |

**Filtros disponibles para listar:**
```
GET /lista-espera?estado=esperando
GET /lista-espera?fecha=2026-04-21
GET /lista-espera?medico_id=4
```

---

### 7.4 Cambiar estado de una cita

El gestor cambia el estado de las citas según lo que ocurra en ventanilla:

```js
// Confirmar llegada del paciente
PATCH /citas/{id}  →  { "estado_id": 2 }   // Confirmada

// Paciente no llegó en los 15 minutos de gracia
PATCH /citas/{id}  →  { "estado_id": 5 }   // No asistió  ← libera el slot automáticamente

// Cancelar cita
PATCH /citas/{id}  →  { "estado_id": 4 }   // Cancelada   ← libera el slot automáticamente
```

**IDs de estados:**
| ID | Estado | Color |
|----|--------|-------|
| 1 | Pendiente | 🟡 #FFA500 |
| 2 | Confirmada | 🔵 #007BFF |
| 3 | Atendida | 🟢 #28A745 |
| 4 | Cancelada | 🔴 #DC3545 |
| 5 | No asistió | ⚫ #6C757D |

---

### 7.6 Reasignación masiva — médico ausente — `POST /citas/reasignar-medico`

Cuando un médico no llega (enfermedad, emergencia), el gestor puede redistribuir todas sus
citas pendientes del día a otros médicos de la misma especialidad con un solo clic.

**Body:**
```json
{
    "medico_id_ausente": 5,
    "fecha": "2026-04-29"
}
```

**Respuesta exitosa:**
```json
{
    "message": "Reasignación completada: 6 de 7 citas reasignadas.",
    "reasignadas": 6,
    "sin_suplente": 1,
    "total": 7,
    "detalle": [
        { "cita_id": 101, "hora": "08:00", "medico_suplente": "Dra. Pérez",  "estado": "reasignada" },
        { "cita_id": 102, "hora": "08:30", "medico_suplente": "Dr. Gómez",   "estado": "reasignada" },
        { "cita_id": 107, "hora": "14:00", "estado": "sin_suplente_disponible" }
    ]
}
```

**Lógica interna:**
- Solo reasigna citas en estado `Pendiente` (id=1)
- El suplente debe tener la **misma especialidad** y horario activo ese día de la semana
- Prioriza al suplente con **menor carga** (menos citas ese día)
- Si una cita queda sin suplente disponible → queda en `sin_suplente_disponible`, el gestor la maneja manualmente

**Errores posibles:**
| HTTP | Situación |
|------|-----------|
| 200 | Al menos una cita procesada (incluso si hay `sin_suplente > 0`) |
| 200 | `reasignadas: 0` si no había citas pendientes ese día |
| 422 | No existen médicos suplentes de esa especialidad ese día |

**Implementación sugerida en la vista del gestor:**
```html
<!-- Botón en la vista de citas del día -->
<button @click="reasignarMedico(medicoId, fecha)"
        class="bg-red-600 text-white px-4 py-2 rounded">
    Médico ausente — Reasignar citas
</button>

<script>
async function reasignarMedico(medicoId, fecha) {
    if (!confirm('¿Confirmas que el médico está ausente y deseas reasignar sus citas?')) return;

    const res = await fetch('/citas/reasignar-medico', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ medico_id_ausente: medicoId, fecha }),
    });

    const data = await res.json();
    alert(data.message);

    if (data.sin_suplente > 0) {
        // Mostrar tabla con las citas que quedaron sin asignar
        console.warn('Citas sin suplente:', data.detalle.filter(d => d.estado === 'sin_suplente_disponible'));
    }
}
</script>
```

---

### 7.5 Reglas de agendamiento por modalidad

| Modalidad | ID | Quién agenda | Restricción de fecha |
|-----------|----|-------------|----------------------|
| Presencial | 1 | Gestor o paciente | Mismo día permitido (solo gestor) |
| Telemedicina | 2 | Paciente (portal) | Mínimo **pasado mañana** |
| Domiciliaria | 3 | Gestor | Mínimo **pasado mañana** |

> El backend ya valida esto automáticamente en `StoreAppointmentRequest`.
> El gestor puede agendar presencial el mismo día, el paciente virtual no.

---

## 8. Reglas de seguridad

- Siempre filtrar por `empresa_id = auth()->user()->empresa_id`
- Al crear una cita, verificar que `medico_id` y `paciente_id` pertenecen a la empresa
- En `edit()` y `update()`, verificar `abort_if($cita->empresa_id !== $empresaId, 403)`
- El gestor NO puede modificar historias clínicas, signos vitales ni recetas (eso es del médico)

---

## 9. Chatbot — asistente virtual del gestor

El chatbot usa el componente reutilizable `<x-chatbot>` y el endpoint `gestor.chatbot`.
El asistente solo responde sobre citas, pacientes y médicos. **Nunca revela datos administrativos.**

### 9.1 Agregar la ruta en el grupo gestor (routes/web.php)

```php
use App\Http\Controllers\Admin\ChatbotController;

// Dentro del grupo Route::prefix('gestor')...
Route::post('/chatbot', [ChatbotController::class, 'chat'])->name('chatbot');
```

### 9.2 Agregar el chatbot al layout del gestor

Al final del layout (`resources/views/gestor/layouts/app.blade.php`), **antes** de `@stack('scripts')`:

```blade
{{-- Chatbot flotante --}}
@php
    $chatbotRutas = collect([
        ['clave' => 'dashboard',  'label' => 'Ir al Dashboard',  'ruta' => 'gestor.dashboard'],
        ['clave' => 'citas',      'label' => 'Ver Citas',         'ruta' => 'gestor.citas'],
        ['clave' => 'pacientes',  'label' => 'Ver Pacientes',     'ruta' => 'gestor.pacientes'],
    ])->filter(fn ($s) => Route::has($s['ruta']))
      ->mapWithKeys(fn ($s) => [$s['clave'] => ['label' => $s['label'], 'url' => route($s['ruta'])]])
      ->toJson();
@endphp

<x-chatbot
    endpoint="{{ route('gestor.chatbot') }}"
    storage-key="gestor"
    :rutas-json="$chatbotRutas"
    mensaje-inicial="¡Hola! Soy tu asistente. Puedo decirte cuántas citas hay hoy, buscar pacientes o ayudarte a navegar. ¿En qué te puedo ayudar?"
/>
```

### 9.3 Qué puede y no puede responder el chatbot del gestor

| Puede responder | No puede responder |
|---|---|
| Citas de hoy y pendientes | Configuración de branding |
| Total de pacientes y médicos | Solicitudes de empleadores |
| Navegación a sus secciones | Horarios de médicos |
| | Datos de otros roles |
