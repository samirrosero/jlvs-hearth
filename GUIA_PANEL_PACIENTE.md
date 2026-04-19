# Panel Paciente — Guía de implementación (Valeri)

## Contexto del proyecto

Sistema multi-tenant para IPS colombianas. Cada paciente pertenece a una empresa (`empresa_id`).
**Regla fundamental:** todo query debe ir filtrado por el `paciente_id` del usuario autenticado.
Stack: Laravel + Blade + Tailwind + Alpine.js

---

## 1. Rutas — agregar en `routes/web.php`

```php
// Importar al inicio del archivo
use App\Http\Controllers\Paciente\PacienteDashboardController;
use App\Http\Controllers\Paciente\PacienteCitasController;
use App\Http\Controllers\Paciente\PacienteHistorialController;

// Agregar ANTES del bloque del panel admin
Route::prefix('paciente')->name('paciente.')->middleware(['auth', 'role:paciente'])->group(function () {
    Route::get('/',            fn () => redirect()->route('paciente.dashboard'));
    Route::get('/dashboard',   PacienteDashboardController::class)->name('dashboard');

    Route::get('/citas',       [PacienteCitasController::class, 'index'])->name('citas');

    Route::get('/historial',   [PacienteHistorialController::class, 'index'])->name('historial');
    Route::get('/historial/{historia}', [PacienteHistorialController::class, 'show'])->name('historial.show');
});
```

---

## 2. Redirect del login — editar `LoginController.php`

En `app/Http/Controllers/Admin/LoginController.php`, línea con `'paciente'`:

```php
// Cambiar esto:
'paciente' => redirect()->route('home'),

// Por esto:
'paciente' => redirect()->route('paciente.dashboard'),
```

---

## 3. Obtener el paciente autenticado

El `User` tiene una relación `paciente()`. Úsala así en todos los controllers:

```php
$paciente = auth()->user()->paciente;  // objeto Paciente
$pacienteId = $paciente->id;
```

---

## 4. Controladores — crear en `app/Http/Controllers/Paciente/`

### PacienteDashboardController.php

```php
<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\HistoriaClinica;

class PacienteDashboardController extends Controller
{
    public function __invoke()
    {
        $paciente = auth()->user()->paciente;

        // Próximas citas del paciente
        $proximasCitas = Cita::where('paciente_id', $paciente->id)
            ->where('activo', true)
            ->where('fecha', '>=', now()->toDateString())
            ->with('medico.usuario', 'estado', 'servicio')
            ->orderBy('fecha')->orderBy('hora')
            ->limit(5)
            ->get();

        // Total de citas históricas
        $totalCitas = Cita::where('paciente_id', $paciente->id)->count();

        // Total de historias clínicas
        $totalHistorias = HistoriaClinica::where('paciente_id', $paciente->id)->count();

        return view('paciente.dashboard', compact(
            'paciente', 'proximasCitas', 'totalCitas', 'totalHistorias'
        ));
    }
}
```

### PacienteCitasController.php

```php
<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\EstadoCita;

class PacienteCitasController extends Controller
{
    public function index()
    {
        $pacienteId = auth()->user()->paciente->id;

        $citas = Cita::where('paciente_id', $pacienteId)
            ->with('medico.usuario', 'estado', 'servicio', 'modalidad')
            ->when(request('estado_id'), fn ($q) => $q->where('estado_id', request('estado_id')))
            ->orderByDesc('fecha')
            ->paginate(10)
            ->withQueryString();

        $estados = EstadoCita::all();

        return view('paciente.citas.index', compact('citas', 'estados'));
    }
}
```

### PacienteHistorialController.php

```php
<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\HistoriaClinica;

class PacienteHistorialController extends Controller
{
    public function index()
    {
        $pacienteId = auth()->user()->paciente->id;

        $historias = HistoriaClinica::where('paciente_id', $pacienteId)
            ->with('ejecucionCita.cita.medico.usuario', 'recetasMedicas')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('paciente.historial.index', compact('historias'));
    }

    public function show(HistoriaClinica $historia)
    {
        // Seguridad: solo el dueño puede ver su historia
        abort_if($historia->paciente_id !== auth()->user()->paciente->id, 403);

        $historia->load('ejecucionCita.cita.medico.usuario', 'recetasMedicas');

        return view('paciente.historial.show', compact('historia'));
    }
}
```

---

## 5. Layout — crear `resources/views/paciente/layouts/app.blade.php`

Copiar la estructura de `resources/views/medico/layouts/app.blade.php` y ajustar:

- Cambiar `id="medico-sidebar"` → `id="paciente-sidebar"`
- Cambiar el `$nav` a las secciones del paciente:

```php
$nav = [
    [
        'route' => 'paciente.dashboard',
        'icon'  => $empresa?->icono_dashboard_url ?? asset('img/icons/dashboard.png'),
        'label' => 'Inicio',
    ],
    [
        'route' => 'paciente.citas',
        'match' => 'paciente.citas*',
        'icon'  => $empresa?->icono_card_citas_url ?? asset('img/icons/citas-mes.png'),
        'label' => 'Mis Citas',
    ],
    [
        'route' => 'paciente.historial',
        'match' => 'paciente.historial*',
        'icon'  => $empresa?->icono_pacientes_url ?? asset('img/icons/pacientes.png'),
        'label' => 'Mi Historial',
    ],
];
```

- El sidebar muestra: nombre del paciente, `rol: Paciente`, botón logout.

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
        --color-sidebar:    {{ $empresa?->color_paciente   ?? '#0f172a' }};
        --color-primario:   {{ $empresa?->color_primario   ?? '#0369a1' }};
        --color-secundario: {{ $empresa?->color_secundario ?? '#075985' }};
    }
    #paciente-sidebar { background-color: var(--color-sidebar) !important; }
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
        'route' => 'paciente.dashboard',
        'icon'  => $empresa?->icono_dashboard_url ?? asset('img/icons/dashboard.png'),
        'label' => 'Inicio',
    ],
    // ...
];
```

Campos disponibles en `$empresa` para íconos:

| Campo | Uso |
|---|---|
| `$empresa->logo_url` | Logo principal del sidebar |
| `$empresa->favicon_url` | Favicon del navegador |
| `$empresa->color_paciente` | Color de fondo del sidebar del paciente |
| `$empresa->color_primario` | Color primario de la IPS |
| `$empresa->color_secundario` | Color secundario |
| `$empresa->icono_dashboard_url` | Ícono sección Dashboard |
| `$empresa->icono_pacientes_url` | Ícono sección Pacientes |
| `$empresa->icono_card_citas_url` | Ícono sección Citas |

> Si el campo es `null` (la IPS no lo ha personalizado), siempre cae al `asset()` de fallback.

---

## 6. Vistas — crear en `resources/views/paciente/`

```
resources/views/paciente/
├── layouts/
│   └── app.blade.php          ← layout principal
├── dashboard.blade.php        ← inicio: próximas citas + resumen
├── citas/
│   └── index.blade.php        ← lista de citas con filtro por estado
└── historial/
    ├── index.blade.php        ← lista de historias clínicas
    └── show.blade.php         ← detalle de una historia + receta
```

---

## 7. Endpoints de la API disponibles para el paciente

El paciente ya tiene acceso a estas rutas (auth middleware + role:paciente):

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/citas` | Sus citas (filtra por paciente_id en el controller) |
| GET | `/citas/{cita}` | Detalle de una cita |
| GET | `/historias-clinicas` | Sus historias clínicas |
| GET | `/historias-clinicas/{historia}` | Detalle de historia |
| GET | `/historias-clinicas/{historia}/pdf` | Descargar historia en PDF |
| GET | `/recetas` | Sus recetas |
| GET | `/signos-vitales` | Sus signos vitales |
| GET | `/antecedentes` | Sus antecedentes |
| GET | `/valoraciones` | Sus valoraciones |
| POST | `/valoraciones` | Crear una valoración a un médico |
| GET | `/especialidades` | Especialidades disponibles en su IPS |
| GET | `/medicos?especialidad=X` | Médicos por especialidad (si puede agendar) |

> **Nota:** Estos endpoints retornan JSON. Úsalos con `fetch()` desde Alpine.js si necesitas
> interactividad, o consúmelos directamente en el controller Blade con el modelo.

---

## 8. Reglas de seguridad

- Nunca uses solo `HistoriaClinica::find($id)` — siempre agrega `->where('paciente_id', $pacienteId)`
- El `abort_if` en `show()` es obligatorio para evitar que manipulen la URL
- El paciente NO puede crear/editar historias, solo leerlas
- El paciente SÍ puede crear valoraciones (una por cita)

---

## 9. Chatbot — asistente virtual del paciente

El chatbot usa el componente reutilizable `<x-chatbot>` y el endpoint `paciente.chatbot`.
El asistente solo responde sobre las citas e historial del paciente autenticado. **Nunca revela datos de otros pacientes ni información administrativa.**

### 9.1 Agregar la ruta en el grupo paciente (routes/web.php)

```php
use App\Http\Controllers\Admin\ChatbotController;

// Dentro del grupo Route::prefix('paciente')...
Route::post('/chatbot', [ChatbotController::class, 'chat'])->name('chatbot');
```

### 9.2 Agregar el chatbot al layout del paciente

Al final del layout (`resources/views/paciente/layouts/app.blade.php`), **antes** de `@stack('scripts')`:

```blade
{{-- Chatbot flotante --}}
@php
    $chatbotRutas = collect([
        ['clave' => 'dashboard',  'label' => 'Ir al Inicio',       'ruta' => 'paciente.dashboard'],
        ['clave' => 'citas',      'label' => 'Ver Mis Citas',       'ruta' => 'paciente.citas'],
        ['clave' => 'historial',  'label' => 'Ver Mi Historial',    'ruta' => 'paciente.historial'],
    ])->filter(fn ($s) => Route::has($s['ruta']))
      ->mapWithKeys(fn ($s) => [$s['clave'] => ['label' => $s['label'], 'url' => route($s['ruta'])]])
      ->toJson();
@endphp

<x-chatbot
    endpoint="{{ route('paciente.chatbot') }}"
    storage-key="paciente"
    :rutas-json="$chatbotRutas"
    mensaje-inicial="¡Hola! Soy tu asistente personal. Puedo decirte cuántas citas tienes o ayudarte a navegar. ¿En qué te puedo ayudar?"
/>
```

### 9.3 Qué puede y no puede responder el chatbot del paciente

| Puede responder | No puede responder |
|---|---|
| Número de próximas citas del paciente | Datos de otros pacientes |
| Total de historias clínicas propias | Estadísticas de la IPS |
| Navegación a sus secciones | Datos administrativos |
| | Información de médicos u otros usuarios |
