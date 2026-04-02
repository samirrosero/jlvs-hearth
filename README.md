# JLVS Hearth — Backend
 
 Backend multi-tenant para IPS colombianas, construido con **Laravel 12**.
 
 - **Multi-tenancy** por `empresa_id` (aislamiento a nivel de Form Requests, Controllers y Policies)
 - **Autenticación por sesión** (Laravel)
 - **MySQL**
 
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
