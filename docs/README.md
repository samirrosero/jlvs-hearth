# Documentación Backend — JLVS Hearth

Proyecto multi-tenant para IPS colombianas. Backend construido con **Laravel 12**, autenticación por sesión, MySQL.

## Índice

| Archivo | Contenido |
|---------|-----------|
| [01-autenticacion.md](01-autenticacion.md) | Login, logout, endpoint `/me` |
| [02-middleware-roles.md](02-middleware-roles.md) | Middleware `VerificarRol` y sistema de roles |
| [03-form-requests.md](03-form-requests.md) | Validaciones de entrada (24 Form Requests) |
| [04-controladores.md](04-controladores.md) | Los 12 controladores de recursos |
| [05-politicas.md](05-politicas.md) | Políticas de autorización a nivel de fila (9 Policies) |
| [06-rutas.md](06-rutas.md) | Mapa completo de rutas (95 rutas) |
| [07-base-de-datos.md](07-base-de-datos.md) | Estructura completa de las 18 tablas con todos sus atributos |
| [08-flujos.md](08-flujos.md) | Todos los flujos del sistema con endpoints, bodies y orden de ejecución |

## Arquitectura general

```
Request
  │
  ├─ Middleware auth (sesión Laravel)
  ├─ Middleware role:X,Y,Z  ←── VerificarRol (nivel de ruta)
  │
  ├─ Form Request (validación + reglas tenant-scoped)
  │
  ├─ Controller
  │    ├─ index()   → filtra por empresa_id del usuario autenticado
  │    ├─ store()   → inyecta empresa_id automáticamente
  │    ├─ show()    → $this->authorize('view', $model)
  │    ├─ update()  → $this->authorize('update', $model)
  │    └─ destroy() → $this->authorize('delete', $model)
  │
  └─ Policy (nivel de fila: verifica empresa_id del recurso)
```

## Roles del sistema

| Rol | Descripción |
|-----|-------------|
| `administrador` | Acceso total a su empresa (tenant) |
| `medico` | Acceso a citas propias, historias, recetas, documentos |
| `gestor_citas` | Gestión de pacientes y citas |
| `paciente` | Solo lectura de sus propios registros |

## Multi-tenancy

El aislamiento entre empresas (IPS) se aplica en **tres niveles**:

1. **Form Request** — `Rule::exists('tabla')->where('empresa_id', $empresaId)` impide referenciar registros de otro tenant.
2. **Controller `index()`** — `->where('empresa_id', auth()->user()->empresa_id)` en todas las consultas de listado.
3. **Policy** — cada método verifica que el `empresa_id` del recurso coincida con el del usuario autenticado.
