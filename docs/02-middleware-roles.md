# Middleware de Roles

Archivo: [app/Http/Middleware/VerificarRol.php](../app/Http/Middleware/VerificarRol.php)
Registro: [bootstrap/app.php](../bootstrap/app.php)

---

## Propósito

Controla el acceso a grupos de rutas según el **rol del usuario autenticado**. Actúa a nivel de ruta (autorización gruesa), complementado por las Policies a nivel de fila (autorización fina).

---

## Implementación

```php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    $usuario = auth()->user();

    if (!$usuario) {
        return response()->json(['message' => 'No autenticado.'], 401);
    }

    if (!in_array($usuario->rol?->nombre, $roles)) {
        return response()->json(['message' => 'No tienes permiso para realizar esta acción.'], 403);
    }

    return $next($request);
}
```

**Notas:**
- `string ...$roles` recibe los roles como argumentos variádicos desde la definición de la ruta.
- `$usuario->rol?->nombre` usa el operador nullsafe (`?->`) para evitar error si el usuario no tiene rol asignado.
- Retorna `401` si no hay sesión, `403` si hay sesión pero el rol no está permitido.

---

## Registro del alias

En [bootstrap/app.php](../bootstrap/app.php):

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => \App\Http\Middleware\VerificarRol::class,
    ]);
})
```

---

## Uso en rutas

```php
// Un solo rol
Route::middleware('role:administrador')->group(function () { ... });

// Múltiples roles permitidos (OR)
Route::middleware('role:administrador,medico,paciente')->group(function () { ... });
```

---

## Tabla de roles y permisos por recurso

| Recurso | Leer | Crear | Actualizar | Eliminar |
|---------|------|-------|------------|----------|
| Pacientes | admin, gestor_citas, medico | admin, gestor_citas | admin, gestor_citas | admin, gestor_citas |
| Citas | admin, gestor_citas, medico, paciente | admin, gestor_citas | admin, gestor_citas | admin, gestor_citas |
| Ejecuciones | admin, medico | admin, medico | admin, medico | admin, medico |
| Historias clínicas | admin, medico, paciente | admin, medico | admin, medico | admin, medico |
| Recetas | admin, medico, paciente | admin, medico | admin, medico | admin, medico |
| Documentos | admin, medico, gestor_citas, paciente | admin, medico | admin, medico | admin, medico |
| Médicos | admin, gestor_citas | admin | admin | admin |
| Portafolios | admin | admin | admin | admin |
| Modalidades cita | todos (auth) | admin | admin | admin |
| Estados cita | todos (auth) | admin | admin | admin |
| Roles | admin | — | — | — |

---

## Relación con Policies

El middleware verifica **qué tipo de usuario puede acceder al endpoint**.
Las Policies verifican **si ese usuario puede acceder a ese registro específico**.

```
middleware('role:administrador,medico')  →  ¿puede este rol llamar este endpoint?
$this->authorize('view', $historia)      →  ¿pertenece esta historia a la empresa del usuario?
```
