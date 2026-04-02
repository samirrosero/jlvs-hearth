# Form Requests (Validación)

Carpeta: [app/Http/Requests/](../app/Http/Requests/)

24 clases de validación, dos por cada recurso: `StoreXxxRequest` y `UpdateXxxRequest`.

---

## Principios generales

### `authorize()` siempre retorna `true`

```php
public function authorize(): bool
{
    return true;
}
```

La autorización de rol se delega al middleware de rutas (`role:`). La autorización de fila se delega a las Policies.

### Diferencia Store vs Update

| | Store | Update |
|-|-------|--------|
| Campos obligatorios | `required` | `sometimes` |
| Unique con ignore | no | sí (`->ignore($id)`) |
| Método HTTP esperado | `POST` | `PUT` / `PATCH` |

### Validación tenant-scoped

Para garantizar que los IDs referenciados pertenezcan a la misma empresa:

```php
// FK a otra tabla del mismo tenant
'medico_id' => ['required', Rule::exists('medicos', 'id')->where('empresa_id', $empresaId)],

// Campo único dentro del tenant (no globalmente)
'identificacion' => ['required', 'string', 'max:20',
    Rule::unique('pacientes')->where('empresa_id', $empresaId)
],

// En Update: ignorar el registro actual
'identificacion' => ['sometimes', 'string', 'max:20',
    Rule::unique('pacientes')->where('empresa_id', $empresaId)->ignore($this->route('paciente'))
],
```

`$empresaId` se obtiene desde el usuario autenticado:
```php
$empresaId = auth()->user()->empresa_id;
```

---

## Listado de Form Requests

### Pacientes

**StorePatientRequest**
```
nombre          required|string|max:100
apellido        required|string|max:100
identificacion  required|string|max:20|unique(pacientes, empresa_id)
fecha_nacimiento required|date
telefono        nullable|string|max:20
email           nullable|email|max:100
direccion       nullable|string|max:255
empresa_id      — inyectado por controller, no validado aquí
```

**UpdatePatientRequest** — mismos campos con `sometimes`, unique ignora el registro actual.

---

### Médicos

**StoreDoctorRequest**
```
nombre          required|string|max:100
apellido        required|string|max:100
especialidad    required|string|max:100
identificacion  required|string|max:20|unique(medicos, empresa_id)
telefono        nullable|string|max:20
email           nullable|email|max:100
```

---

### Citas

**StoreAppointmentRequest**
```
paciente_id      required|exists(pacientes, empresa_id)
medico_id        required|exists(medicos, empresa_id)
modalidad_id     required|exists(modalidades_cita, id)
estado_id        required|exists(estados_cita, id)
fecha_hora       required|date
motivo           nullable|string|max:500
```

---

### Ejecuciones de cita

**StoreAppointmentExecutionRequest**
```
cita_id          required|exists(citas, empresa_id)
inicio_atencion  required|date
fin_atencion     nullable|date|after_or_equal:inicio_atencion
observaciones    nullable|string
```

---

### Historias clínicas

**StoreClinicalHistoryRequest**
```
paciente_id      required|exists(pacientes, empresa_id)
medico_id        required|exists(medicos, empresa_id)
fecha            required|date
diagnostico      required|string
tratamiento      nullable|string
observaciones    nullable|string
```

---

### Recetas médicas

**StoreMedicalPrescriptionRequest**
```
historia_clinica_id  required|exists(historias_clinicas, empresa_id via paciente)
medicamentos         required|string
indicaciones         nullable|string
fecha_emision        required|date
```

---

### Documentos adjuntos

**StoreAttachedDocumentRequest**
```
historia_clinica_id  required|exists(historias_clinicas)
archivo              required|file|max:10240  (10 MB)
nombre               nullable|string|max:255
tipo                 nullable|string|max:100
```

---

### Portafolios (convenios/EPS)

**StorePortfolioRequest**
```
nombre          required|string|max:150|unique(portafolios, empresa_id)
descripcion     nullable|string
activo          nullable|boolean
```

---

### Modalidades de cita

**StoreAppointmentModalidadRequest**
```
nombre          required|string|max:100|unique(modalidades_cita, empresa_id)
descripcion     nullable|string
```

---

### Estados de cita

**StoreAppointmentStatusRequest**
```
nombre          required|string|max:100|unique(estados_cita, empresa_id)
color           required|string|regex:/^#[0-9A-Fa-f]{6}$/
descripcion     nullable|string
```

El campo `color` valida un **color hexadecimal** de 6 dígitos (ej: `#FF5733`).

---

### Empresa (`UpdateCompanyRequest`)

```
nombre          sometimes|string|max:150
nit             sometimes|string|max:20|unique(empresas)->ignore(empresa_actual)
telefono        sometimes|string|max:20
direccion       sometimes|string|max:255
email           sometimes|email|max:100
```

---

### Roles

Solo lectura — no existen StoreRoleRequest ni UpdateRoleRequest (la escritura es exclusiva del seeder).
