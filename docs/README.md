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
| [06-rutas.md](06-rutas.md) | Mapa completo de rutas (100+ rutas) |
| [07-base-de-datos.md](07-base-de-datos.md) | Estructura completa de las 21 tablas con todos sus atributos |
| [08-flujos.md](08-flujos.md) | Todos los flujos del sistema con endpoints, bodies y orden de ejecución |
| [09-diagrama-eer.md](09-diagrama-eer.md) | Entidades, atributos, relaciones y cardinalidades para el diagrama EER |
| [10-diagramas-casos-uso.md](10-diagramas-casos-uso.md) | Diagramas de casos de uso del sistema |
| [11-onboarding-y-registro.md](11-onboarding-y-registro.md) | Onboarding de IPS, registro público, branding y solicitudes de empleadores |

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
| `administrador` | Acceso total a su empresa: dashboard, reportes, auditoría, configuración |
| `medico` | Acceso a sus citas, historias clínicas, recetas, documentos |
| `gestor_citas` | Gestión de pacientes (incluido registro presencial) y agendamiento de citas |
| `paciente` | Solo lectura de sus propios registros, puede valorar y descargar su historia en PDF |

## Multi-tenancy

El aislamiento entre empresas (IPS) se aplica en **tres niveles**:

1. **Form Request** — `Rule::exists('tabla')->where('empresa_id', $empresaId)` impide referenciar registros de otro tenant.
2. **Controller `index()`** — `->where('empresa_id', auth()->user()->empresa_id)` en todas las consultas de listado.
3. **Policy** — cada método verifica que el `empresa_id` del recurso coincida con el del usuario autenticado.

## Nuevas funcionalidades (Abril 2026)

### 1. Onboarding de IPS
Registro público de nuevas IPS sin autenticación. Crea automáticamente la empresa y su administrador inicial.

### 2. Registro Público
- **Afiliados (pacientes)**: Se registran directamente y pueden acceder inmediatamente
- **Empleadores (médicos/gestores)**: Envían solicitud que requiere aprobación del administrador

### 3. Branding/Identidad Visual
Cada IPS puede personalizar:
- Logo, favicon e imágenes de login/registro
- Paleta de colores por rol (admin, médico, gestor, paciente)
- Slogans personalizados para login y registro
- Color para PDFs de reportes e historias clínicas

### 4. Gestión de Solicitudes de Empleadores
Flujo de aprobación/rechazo desde el panel administrativo con:
- Verificación de documento con foto
- Creación automática de usuario al aprobar
- Observaciones en caso de rechazo

### 5. Panel de Administración Blade
Interfaz web administrativa con:
- Login con número de documento
- Dashboard con métricas
- CRUD de pacientes y médicos
- Gestión de reportes (PDF/Excel)
- Chatbot asistente con Ollama
