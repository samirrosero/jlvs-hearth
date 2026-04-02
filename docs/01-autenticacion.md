# Autenticación

Archivo: [app/Http/Controllers/AuthController.php](../app/Http/Controllers/AuthController.php)

Laravel utiliza autenticación basada en **sesión** (no tokens JWT/Sanctum). El guard por defecto es `web`.

---

## Endpoints

### `POST /login` — público

Inicia sesión con email y contraseña.

**Body:**
```json
{
  "email": "admin@clinica.com",
  "password": "secret"
}
```

**Respuesta exitosa `200`:**
```json
{
  "message": "Sesión iniciada.",
  "usuario": {
    "id": 1,
    "nombre": "Dr. Pérez",
    "email": "admin@clinica.com",
    "rol": { "nombre": "administrador" },
    "empresa": { "id": 1, "nombre": "Clínica Salud" }
  }
}
```

**Error `401`** si las credenciales son incorrectas.

---

### `POST /logout` — requiere auth

Cierra la sesión y regenera el token CSRF.

**Respuesta `200`:**
```json
{ "message": "Sesión cerrada." }
```

---

### `GET /me` — requiere auth

Devuelve el usuario autenticado con su rol y empresa.

**Respuesta `200`:**
```json
{
  "id": 1,
  "nombre": "Dr. Pérez",
  "email": "admin@clinica.com",
  "rol": { "nombre": "administrador" },
  "empresa": { "id": 1, "nombre": "Clínica Salud" }
}
```

---

## Implementación clave

```php
public function login(Request $request): JsonResponse
{
    $credenciales = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (!Auth::attempt($credenciales, $request->boolean('remember'))) {
        return response()->json(['message' => 'Credenciales incorrectas.'], 401);
    }

    $request->session()->regenerate();  // previene session fixation

    return response()->json([
        'message' => 'Sesión iniciada.',
        'usuario' => auth()->user()->load('rol', 'empresa'),
    ]);
}
```

**Notas:**
- `$request->boolean('remember')` permite "recordarme" si el frontend lo envía.
- `session()->regenerate()` es obligatorio tras login para prevenir ataques de session fixation.
- El usuario cargado incluye relaciones `rol` y `empresa` para que el frontend pueda establecer permisos inmediatamente.

---

## Configuración de sesión

La sesión usa el driver configurado en `config/session.php` (por defecto `file` en desarrollo, recomendado `database` en producción).

El middleware `auth` de Laravel rechaza automáticamente con `401` cualquier ruta protegida si no hay sesión activa.
