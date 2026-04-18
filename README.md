# JLVS Hearth — Backend
 
 Backend multi-tenant para IPS colombianas, construido con **Laravel 12**.
 
 - **Multi-tenancy** por `empresa_id` (aislamiento a nivel de Form Requests, Controllers y Policies)
 - **Autenticación por sesión** (Laravel)
 - **Panel de administración** con vistas Blade
 - **MySQL**
 
 ## Funcionalidades principales
 
 ### Core (Sistema de Gestión Clínica)
 - Gestión de pacientes, médicos y citas
 - Historias clínicas con CIE-10
 - Recetas médicas y documentos adjuntos
 - Signos vitales y antecedentes
 - Auditoría (Resolución 1995/1999)
 
 ### Nuevas funcionalidades (Abril 2026)
 - **Onboarding de IPS**: Registro público de nuevas IPS
 - **Registro público**: Afiliados (directo) y empleadores (con aprobación)
 - **Branding/Identidad visual**: Personalización por IPS (colores, logos, imágenes)
 - **Gestión de solicitudes**: Flujo de aprobación de empleadores
 - **Panel administrativo**: Interfaz web con dashboard, CRUDs y reportes
 - **Chatbot asistente**: Integración con Ollama
 
 ## Documentación
 
 La documentación técnica vive en la carpeta `docs/`:
 
 - `docs/README.md` (índice principal)
 - `docs/01-autenticacion.md`
 - `docs/02-middleware-roles.md`
 - `docs/03-form-requests.md`
 - `docs/04-controladores.md`
 - `docs/05-politicas.md`
 - `docs/06-rutas.md`
 - `docs/07-base-de-datos.md`
 - `docs/08-flujos.md`
 - `docs/09-diagrama-eer.md`
 - `docs/10-diagramas-casos-uso.md`
 - `docs/11-onboarding-y-registro.md` **(nuevo)**
 
 ## Requisitos
 
 - PHP (compatible con Laravel 12)
 - Composer
 - MySQL
 
 ## Instalación
 
 1. Instala dependencias:
 
    ```bash
    composer install
    ```
 
 2. Crea tu archivo de entorno:
 
    ```bash
    cp .env.example .env
    ```
 
 3. Genera la key de la app:
 
    ```bash
    php artisan key:generate
    ```
 
 4. Configura tu conexión a base de datos en `.env` (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD).
 
 5. Corre migraciones y seeders:
 
    ```bash
    php artisan migrate --seed
    ```
 
 ## Correr el proyecto
 
 - Servidor local:
 
   ```bash
   php artisan serve
   ```
 
 ## Notas
 
 - La lógica de aislamiento (tenant) y autorización está detallada en `docs/README.md`.
 
 ## Rutas principales
 
 ### Públicas
 - `GET /` - Landing page
 - `GET /adquirir` - Registro de nueva IPS
 - `GET /registro` - Registro de afiliados y empleadores
 - `GET /login` - Login del panel administrativo
 
 ### Panel de administración (requiere auth)
 - `GET /admin/dashboard` - Dashboard con métricas
 - `GET /admin/pacientes` - Gestión de pacientes
 - `GET /admin/medicos` - Gestión de médicos
 - `GET /admin/branding` - Configuración de identidad visual
 - `GET /admin/solicitudes` - Gestión de solicitudes de empleadores
 - `GET /admin/reportes` - Reportes PDF/Excel
 
 ### API REST (requiere auth)
 - `GET /me` - Usuario autenticado
 - `GET /citas` - Gestión de citas
 - `GET /pacientes` - API de pacientes
 - `GET /medicos` - API de médicos
 - Y 80+ endpoints más documentados en `docs/06-rutas.md`
