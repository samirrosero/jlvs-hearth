# Políticas de Autorización (Policies)

Carpeta: [app/Policies/](../app/Policies/)

9 clases de Policy para autorización a nivel de fila. Complementan el middleware de roles (que actúa a nivel de ruta).

---

## Propósito

Las Policies responden la pregunta: **¿puede este usuario específico operar sobre este registro específico?**

Ejemplo: un `administrador` puede ver citas, pero solo las de su empresa. La Policy verifica que `cita->empresa_id === user->empresa_id`.

---

## Descubrimiento automático

Laravel registra las Policies automáticamente por convención de nombres:

```
App\Models\Paciente  →  App\Policies\PacientePolicy
App\Models\Cita      →  App\Policies\CitaPolicy
...
```

No se necesita registro manual en `AuthServiceProvider`.

---

## Habilitación en controladores

El trait `AuthorizesRequests` en la clase base `Controller` habilita:

```php
$this->authorize('view', $modelo);    // llama Policy::view()
$this->authorize('update', $modelo);  // llama Policy::update()
$this->authorize('delete', $modelo);  // llama Policy::delete()
```

Si la Policy retorna `false`, Laravel lanza `AuthorizationException` (HTTP 403 automáticamente).

---

## Patrón estándar

```php
class PacientePolicy
{
    public function view(User $user, Paciente $paciente): bool
    {
        return $user->empresa_id === $paciente->empresa_id;
    }

    public function update(User $user, Paciente $paciente): bool
    {
        return $user->empresa_id === $paciente->empresa_id;
    }

    public function delete(User $user, Paciente $paciente): bool
    {
        return $user->empresa_id === $paciente->empresa_id;
    }
}
```

---

## Políticas y sus reglas

### EmpresaPolicy
[app/Policies/EmpresaPolicy.php](../app/Policies/EmpresaPolicy.php)

Solo el administrador puede ver/editar su propia empresa:
```php
public function view(User $user, Empresa $empresa): bool
{
    return $user->empresa_id === $empresa->id;
}
```

---

### PacientePolicy
[app/Policies/PacientePolicy.php](../app/Policies/PacientePolicy.php)

- `view`: mismo tenant (admin, gestor_citas, medico) **o** el paciente viendo su propio perfil.
- `update` / `delete`: solo mismo tenant (no el paciente mismo).

```php
public function view(User $user, Paciente $paciente): bool
{
    if ($user->empresa_id === $paciente->empresa_id) return true;

    // El paciente puede ver su propio perfil
    return $user->rol?->nombre === 'paciente'
        && $user->paciente?->id === $paciente->id;
}
```

---

### MedicoPolicy
[app/Policies/MedicoPolicy.php](../app/Policies/MedicoPolicy.php)

Verificación de tenant estándar. Solo `administrador` y `gestor_citas` llegan a estas rutas (middleware), la Policy confirma que el médico pertenece a la empresa.

---

### CitaPolicy
[app/Policies/CitaPolicy.php](../app/Policies/CitaPolicy.php)

`view` considera los casos especiales de médico y paciente:

```php
public function view(User $user, Cita $cita): bool
{
    if ($user->empresa_id !== $cita->empresa_id) return false;

    $rol = $user->rol?->nombre;

    if ($rol === 'medico') {
        return $user->medico?->id === $cita->medico_id;
    }

    if ($rol === 'paciente') {
        return $user->paciente?->id === $cita->paciente_id;
    }

    return true; // administrador, gestor_citas
}
```

---

### EjecucionCitaPolicy
[app/Policies/EjecucionCitaPolicy.php](../app/Policies/EjecucionCitaPolicy.php)

Verifica tenant a través de la cita asociada:
```php
private function mismaTenant(User $user, EjecucionCita $ejecucion): bool
{
    return $user->empresa_id === $ejecucion->cita?->empresa_id;
}
```

---

### HistoriaClinicaPolicy
[app/Policies/HistoriaClinicaPolicy.php](../app/Policies/HistoriaClinicaPolicy.php)

`view` permite al paciente ver sus propias historias:

```php
public function view(User $user, HistoriaClinica $historia): bool
{
    if ($user->empresa_id !== $historia->paciente?->empresa_id) return false;

    if ($user->rol?->nombre === 'paciente') {
        return $user->paciente?->id === $historia->paciente_id;
    }

    return true;
}
```

`update` / `delete` restringen al paciente (solo admin y médico pueden modificar):
```php
public function update(User $user, HistoriaClinica $historia): bool
{
    return $user->empresa_id === $historia->paciente?->empresa_id
        && $user->rol?->nombre !== 'paciente';
}
```

---

### RecetaMedicaPolicy
[app/Policies/RecetaMedicaPolicy.php](../app/Policies/RecetaMedicaPolicy.php)

La receta no tiene `empresa_id` directo — la validación sigue la cadena:

```
RecetaMedica → HistoriaClinica → Paciente → empresa_id
```

```php
private function mismaTenant(User $user, RecetaMedica $receta): bool
{
    return $user->empresa_id === $receta->historiaClinica?->paciente?->empresa_id;
}
```

`view` permite al paciente ver sus propias recetas:
```php
public function view(User $user, RecetaMedica $receta): bool
{
    if (!$this->mismaTenant($user, $receta)) return false;

    if ($user->rol?->nombre === 'paciente') {
        return $user->paciente?->id === $receta->historiaClinica?->paciente_id;
    }

    return true;
}
```

---

### DocumentoAdjuntoPolicy
[app/Policies/DocumentoAdjuntoPolicy.php](../app/Policies/DocumentoAdjuntoPolicy.php)

Similar a recetas, sigue la cadena: `DocumentoAdjunto → HistoriaClinica → Paciente → empresa_id`.

---

### PortafolioPolicy
[app/Policies/PortafolioPolicy.php](../app/Policies/PortafolioPolicy.php)

Verificación de tenant estándar. Solo `administrador` accede a estas rutas.

---

## Tabla resumen

| Policy | Comprobación tenant | Restricción extra por rol |
|--------|---------------------|---------------------------|
| EmpresaPolicy | `empresa_id === empresa.id` | Solo el admin de esa empresa |
| PacientePolicy | `empresa_id` directo | Paciente solo ve su propio perfil |
| MedicoPolicy | `empresa_id` directo | — |
| CitaPolicy | `empresa_id` directo | Médico: solo sus citas; Paciente: solo las suyas |
| EjecucionCitaPolicy | vía `cita.empresa_id` | — |
| HistoriaClinicaPolicy | vía `paciente.empresa_id` | Paciente: solo sus historias; no puede modificar |
| RecetaMedicaPolicy | vía `historia.paciente.empresa_id` | Paciente: solo sus recetas; no puede modificar |
| DocumentoAdjuntoPolicy | vía `historia.paciente.empresa_id` | — |
| PortafolioPolicy | `empresa_id` directo | — |
