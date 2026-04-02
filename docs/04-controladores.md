# Controladores

Carpeta: [app/Http/Controllers/](../app/Http/Controllers/)

12 controladores de recursos + 1 de autenticación. Todos retornan `response()->json()`.

---

## Base Controller

[app/Http/Controllers/Controller.php](../app/Http/Controllers/Controller.php)

```php
abstract class Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
}
```

El trait `AuthorizesRequests` habilita `$this->authorize()` en todos los controladores hijos.

---

## Patrón estándar de CRUD

Todos los controladores de recursos siguen este patrón:

```php
// Listar — filtra por empresa del usuario autenticado
public function index(): JsonResponse
{
    return response()->json(
        Modelo::where('empresa_id', auth()->user()->empresa_id)->get()
    );
}

// Crear — inyecta empresa_id automáticamente
public function store(StoreRequest $request): JsonResponse
{
    $modelo = Modelo::create(
        array_merge($request->validated(), ['empresa_id' => auth()->user()->empresa_id])
    );
    return response()->json($modelo, 201);
}

// Ver uno — Policy verifica propiedad del registro
public function show(Modelo $modelo): JsonResponse
{
    $this->authorize('view', $modelo);
    return response()->json($modelo);
}

// Actualizar
public function update(UpdateRequest $request, Modelo $modelo): JsonResponse
{
    $this->authorize('update', $modelo);
    $modelo->update($request->validated());
    return response()->json($modelo);
}

// Eliminar
public function destroy(Modelo $modelo): JsonResponse
{
    $this->authorize('delete', $modelo);
    $modelo->delete();
    return response()->json(['message' => 'Eliminado.']);
}
```

---

## Controladores y sus particularidades

### AuthController
[app/Http/Controllers/AuthController.php](../app/Http/Controllers/AuthController.php)

| Método | Ruta | Descripción |
|--------|------|-------------|
| `login` | `POST /login` | `Auth::attempt()` + `session()->regenerate()` |
| `logout` | `POST /logout` | `Auth::logout()` + invalidar sesión |
| `me` | `GET /me` | Retorna usuario con `rol` y `empresa` cargados |

---

### PatientController
[app/Http/Controllers/PatientController.php](../app/Http/Controllers/PatientController.php)

CRUD estándar. No tiene lógica especial adicional al patrón base.

---

### DoctorController
[app/Http/Controllers/DoctorController.php](../app/Http/Controllers/DoctorController.php)

CRUD estándar.

---

### AppointmentController
[app/Http/Controllers/AppointmentController.php](../app/Http/Controllers/AppointmentController.php)

`index()` tiene filtrado adicional por rol:

```php
public function index(): JsonResponse
{
    $user = auth()->user();
    $query = Cita::where('empresa_id', $user->empresa_id);

    if ($user->rol?->nombre === 'medico') {
        $query->where('medico_id', $user->medico?->id);
    } elseif ($user->rol?->nombre === 'paciente') {
        $query->where('paciente_id', $user->paciente?->id);
    }

    return response()->json($query->get());
}
```

`destroy()` no elimina físicamente — cancela la cita:
```php
$cita->update(['activo' => false]);
return response()->json(['message' => 'Cita cancelada.']);
```

---

### AppointmentExecutionController
[app/Http/Controllers/AppointmentExecutionController.php](../app/Http/Controllers/AppointmentExecutionController.php)

CRUD estándar. Accesible solo por `administrador` y `medico`.

---

### ClinicalHistoryController
[app/Http/Controllers/ClinicalHistoryController.php](../app/Http/Controllers/ClinicalHistoryController.php)

`index()` filtra por `empresa_id`. La Policy restringe adicionalmente al `paciente` para que solo vea sus propias historias.

---

### MedicalPrescriptionController
[app/Http/Controllers/MedicalPrescriptionController.php](../app/Http/Controllers/MedicalPrescriptionController.php)

`index()` filtra a través de la relación con historia clínica:

```php
public function index(): JsonResponse
{
    $empresaId = auth()->user()->empresa_id;
    $recetas = RecetaMedica::whereHas('historiaClinica.paciente', function ($q) use ($empresaId) {
        $q->where('empresa_id', $empresaId);
    })->get();

    return response()->json($recetas);
}
```

---

### AttachedDocumentController
[app/Http/Controllers/AttachedDocumentController.php](../app/Http/Controllers/AttachedDocumentController.php)

`store()` maneja subida de archivos:

```php
public function store(StoreAttachedDocumentRequest $request): JsonResponse
{
    $ruta = $request->file('archivo')->store('documentos', 'local');

    $documento = DocumentoAdjunto::create(array_merge(
        $request->validated(),
        ['ruta_archivo' => $ruta]
    ));

    return response()->json($documento, 201);
}
```

Los archivos se guardan en `storage/app/documentos/` (disco `local`, privado).

---

### AppointmentModalidadController
[app/Http/Controllers/AppointmentModalidadController.php](../app/Http/Controllers/AppointmentModalidadController.php)

`index()` y `show()` son accesibles a todos los roles autenticados.
Escritura solo para `administrador`.

---

### AppointmentStatusController
[app/Http/Controllers/AppointmentStatusController.php](../app/Http/Controllers/AppointmentStatusController.php)

Igual que modalidades. El campo `color` almacena un hex como `#3B82F6`.

---

### PortfolioController
[app/Http/Controllers/PortfolioController.php](../app/Http/Controllers/PortfolioController.php)

CRUD estándar. Solo `administrador` tiene acceso completo.

---

### RoleController
[app/Http/Controllers/RoleController.php](../app/Http/Controllers/RoleController.php)

Solo lectura (`index`, `show`). No expone escritura via API — los roles se gestionan por seeder.

---

### CompanyController
[app/Http/Controllers/CompanyController.php](../app/Http/Controllers/CompanyController.php)

`store()` es público (onboarding de nueva IPS):
```php
// POST /empresas — sin auth
public function store(StoreCompanyRequest $request): JsonResponse
{
    $empresa = Empresa::create($request->validated());
    return response()->json($empresa, 201);
}
```

`destroy()` hace soft-delete lógico (no elimina el registro):
```php
$empresa->update(['activo' => false]);
return response()->json(['message' => 'Empresa desactivada.']);
```

`/mi-empresa` (GET y PUT) usa closures en rutas directamente, sin pasar por este controlador, para evitar problemas con el model binding del administrador autenticado.

---

## Resumen de modelos por controlador

| Controlador | Modelo | Tabla |
|-------------|--------|-------|
| PatientController | Paciente | `pacientes` |
| DoctorController | Medico | `medicos` |
| AppointmentController | Cita | `citas` |
| AppointmentExecutionController | EjecucionCita | `ejecuciones_cita` |
| ClinicalHistoryController | HistoriaClinica | `historias_clinicas` |
| MedicalPrescriptionController | RecetaMedica | `recetas_medicas` |
| AttachedDocumentController | DocumentoAdjunto | `documentos_adjuntos` |
| AppointmentModalidadController | ModalidadCita | `modalidades_cita` |
| AppointmentStatusController | EstadoCita | `estados_cita` |
| PortfolioController | Portafolio | `portafolios` |
| RoleController | Rol | `roles` |
| CompanyController | Empresa | `empresas` |
