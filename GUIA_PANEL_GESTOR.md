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

Este es el formulario clave. El flujo de selección usa Alpine.js + fetch:

```
Paso 1: Selecciona especialidad
        → GET /especialidades
        → dropdown se puebla dinámicamente

Paso 2: Selecciona médico
        → GET /medicos?especialidad=Pediatría
        → solo muestra médicos de esa especialidad en la empresa

Paso 3: Selecciona fecha
        → GET /horarios?medico_id=3
        → deshabilita en el <input type="date"> los días que el médico no trabaja

Paso 4: Selecciona hora
        → calcula slots cada 30 min entre hora_inicio y hora_fin del horario
        → descuenta horas ya ocupadas: GET /citas?medico_id=3&fecha=2026-04-25
        → muestra solo slots libres

Paso 5: Selecciona paciente (buscador)
        → buscar en los pacientes de la empresa

Paso 6: Selecciona servicio y modalidad (dropdowns simples)

Paso 7: POST /gestor/citas → store()
```

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
    <select x-model="medicoId" @change="cargarHorario()" :disabled="!especialidad">
        <option value="">Selecciona médico</option>
        <template x-for="m in medicos" :key="m.id">
            <option :value="m.id" x-text="m.usuario.nombre"></option>
        </template>
    </select>

    <!-- Paso 3: Fecha -->
    <input type="date" x-model="fecha" @change="cargarSlots()"
           :disabled="!medicoId" :min="hoy">
    <!-- Tip: deshabilitar días según diasDisponibles (array de dia_semana) -->

    <!-- Paso 4: Hora (slots) -->
    <select x-model="hora" :disabled="!fecha">
        <template x-for="slot in slotsLibres" :key="slot">
            <option :value="slot" x-text="slot"></option>
        </template>
    </select>

</div>

<script>
function formularioCita() {
    return {
        especialidades: [],
        medicos: [],
        especialidad: '',
        medicoId: '',
        fecha: '',
        hora: '',
        diasDisponibles: [],   // [1,2,3,4,5] = lunes a viernes
        slotsLibres: [],
        hoy: new Date().toISOString().split('T')[0],

        async init() {
            const res = await fetch('/especialidades', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }
            });
            this.especialidades = await res.json();
        },

        async cargarMedicos() {
            this.medicoId = ''; this.medicos = [];
            const res = await fetch(`/medicos?especialidad=${this.especialidad}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }
            });
            this.medicos = await res.json();
        },

        async cargarHorario() {
            this.fecha = ''; this.slotsLibres = [];
            const res = await fetch(`/horarios?medico_id=${this.medicoId}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }
            });
            const horarios = await res.json();
            // dia_semana: 0=domingo, 1=lunes ... 6=sábado
            this.diasDisponibles = horarios.filter(h => h.activo).map(h => h.dia_semana);
            // Guardar también las horas para calcular slots
            this.horarioPorDia = Object.fromEntries(horarios.map(h => [h.dia_semana, h]));
        },

        async cargarSlots() {
            if (!this.fecha || !this.medicoId) return;
            const diaSemana = new Date(this.fecha + 'T00:00:00').getDay(); // 0=dom, 1=lun...
            const horario   = this.horarioPorDia?.[diaSemana];
            if (!horario) { this.slotsLibres = []; return; }

            // Citas ya ocupadas en esa fecha
            const res = await fetch(`/citas?medico_id=${this.medicoId}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }
            });
            const data    = await res.json();
            // La API devuelve paginación, ajusta según la respuesta real
            const citas   = Array.isArray(data) ? data : (data.data ?? []);
            const ocupadas = citas
                .filter(c => c.fecha === this.fecha)
                .map(c => c.hora.substring(0, 5));

            // Generar slots cada 30 minutos
            this.slotsLibres = generarSlots(horario.hora_inicio, horario.hora_fin, 30)
                .filter(slot => !ocupadas.includes(slot));
        },
    };
}

function generarSlots(inicio, fin, intervaloMin) {
    const slots = [];
    let [h, m] = inicio.split(':').map(Number);
    const [hf, mf] = fin.split(':').map(Number);
    while (h < hf || (h === hf && m < mf)) {
        slots.push(`${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}`);
        m += intervaloMin;
        if (m >= 60) { h++; m -= 60; }
    }
    return slots;
}

function csrf() {
    return document.querySelector('meta[name="csrf-token"]').content;
}
</script>
```

---

## 7. Endpoints disponibles para el gestor

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/especialidades` | Especialidades de la empresa |
| GET | `/medicos` | Médicos (filtra por `?especialidad=X` o `?buscar=nombre`) |
| GET | `/medicos/{id}` | Detalle de un médico |
| GET | `/horarios?medico_id=X` | Horario semanal de un médico |
| GET | `/citas` | Citas de la empresa |
| POST | `/citas` | Crear cita |
| PUT/PATCH | `/citas/{id}` | Editar cita |
| DELETE | `/citas/{id}` | Eliminar cita |
| GET | `/pacientes` | Pacientes de la empresa |
| POST | `/pacientes` | Crear paciente |
| GET | `/estados-cita` | Estados de cita |
| GET | `/modalidades-cita` | Modalidades |
| GET | `/servicios` | Servicios/procedimientos |

> **Nota:** Todos estos endpoints retornan JSON y ya están protegidos con el middleware
> `role:gestor_citas`. Solo necesitas llamarlos con el header `Accept: application/json`
> y el token CSRF.

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
