# Guía de instalación — JLVS Hearth

Instrucciones para configurar el proyecto localmente desde cero.

---

## Requisitos previos

Instala estas herramientas antes de comenzar:

| Herramienta | Versión mínima | Descarga |
|-------------|---------------|---------|
| PHP | 8.2+ | https://www.php.net/downloads |
| Composer | 2.x | https://getcomposer.org |
| MySQL | 8.0+ | https://dev.mysql.com/downloads/mysql/ |
| Node.js | 18+ | https://nodejs.org |
| Git | cualquier | https://git-scm.com |

> **Windows:** Se recomienda usar [Laragon](https://laragon.org) o [XAMPP](https://www.apachefriends.org) para tener PHP, MySQL y Composer listos de una vez.

---

## Paso 1 — Clonar el repositorio

```bash
git clone https://github.com/samirrosero/jlvs-hearth.git
cd jlvs-hearth
```

---

## Paso 2 — Instalar dependencias PHP

```bash
composer install
```

---

## Paso 3 — Configurar el archivo de entorno

```bash
cp .env.example .env
```

Abre el archivo `.env` con cualquier editor de texto y ajusta estos valores:

```env
# ── Base de datos ──────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jlvs_hearth      # nombre de la BD que vas a crear
DB_USERNAME=root              # tu usuario de MySQL
DB_PASSWORD=                  # tu contraseña de MySQL (puede quedar vacío en Laragon)

# ── Correo (Gmail SMTP) ────────────────────────────────────
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tucorreo@gmail.com
MAIL_PASSWORD="xxxx xxxx xxxx xxxx"   # contraseña de aplicación de Gmail (16 caracteres)
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tucorreo@gmail.com
MAIL_FROM_NAME="JLVS Hearth"
```

> **Contraseña de aplicación Gmail:** Ve a tu cuenta de Google → Seguridad → Verificación en dos pasos → Contraseñas de aplicaciones → crea una para "Correo / Windows" y pega los 16 caracteres aquí.

---

## Paso 4 — Crear la base de datos en MySQL

Abre MySQL Workbench, phpMyAdmin o la terminal y ejecuta:

```sql
CREATE DATABASE jlvs_hearth CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## Paso 5 — Generar la clave de la aplicación

```bash
php artisan key:generate
```

---

## Paso 6 — Ejecutar migraciones y seeders

```bash
php artisan migrate:fresh --seed
```

Esto crea todas las tablas y carga:
- La empresa de ejemplo
- Usuarios de prueba (administrador, médico, gestor, paciente)
- ~100 códigos CIE-10
- 20 pacientes ficticios con historial de 6 meses de citas

---

## Paso 7 — Enlace de almacenamiento

```bash
php artisan storage:link
```

---

## Paso 8 — Instalar dependencias JavaScript y compilar assets

```bash
npm install
npm run build
```

---

## Paso 9 — Iniciar el servidor

Abre **dos terminales** en la carpeta del proyecto:

**Terminal 1 — Servidor web:**
```bash
php artisan serve
```
La aplicación queda disponible en `http://localhost:8000`

**Terminal 2 — Cola de correos (para que los emails funcionen):**
```bash
php artisan queue:listen
```

---

## Usuarios de prueba

Después de ejecutar los seeders tendrás estos accesos listos:

| Rol | Correo | Contraseña |
|-----|--------|-----------|
| Administrador | admin@jlvs.com | password |
| Médico | medico@jlvs.com | password |
| Gestor de citas | gestor@jlvs.com | password |
| Paciente | paciente@jlvs.com | password |

---

## Solución de problemas frecuentes

**Error `SQLSTATE[HY000] [1045] Access denied`**
→ Revisa `DB_USERNAME` y `DB_PASSWORD` en el `.env`.

**Error `No application encryption key has been specified`**
→ Ejecuta de nuevo `php artisan key:generate`.

**Los correos no llegan**
→ Verifica que la contraseña de aplicación de Gmail esté correcta y que `php artisan queue:listen` esté corriendo en otra terminal.

**Error al ejecutar `npm run build`**
→ Asegúrate de tener Node.js 18+ instalado: `node -v`.

**`php artisan` no se reconoce**
→ PHP no está en el PATH. Si usas Laragon, abre la terminal desde el mismo Laragon.

---

## Para la presentación

Durante la demo, los pasos recomendados son:

1. Iniciar sesión como **administrador** → mostrar dashboard con métricas históricas
2. Crear un nuevo **médico** desde el panel
3. Crear un nuevo **paciente** (como gestor de citas) → se genera cuenta con contraseña temporal
4. El paciente inicia sesión → el sistema pide cambiar la contraseña
5. Agendar una cita → el paciente recibe correo de confirmación
6. El médico atiende la cita → crea historia clínica con CIE-10 → descarga PDF
7. El administrador exporta reporte de citas en PDF o Excel
