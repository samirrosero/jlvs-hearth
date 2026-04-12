# Librerías y tecnologías utilizadas — JLVS Hearth

## Backend (PHP / Composer)

| Librería | Versión | Para qué se usa |
|---|---|---|
| **Laravel** | ^12.0 | Framework principal — rutas, controladores, modelos, migraciones |
| **barryvdh/laravel-dompdf** | ^3.1 | Generación de PDFs (historias clínicas, reportes) |
| **maatwebsite/excel** | ^3.1 | Exportación de datos a Excel (reportes) |
| **laravel/tinker** | ^2.10 | Consola interactiva para pruebas en desarrollo |

## Frontend (Node / npm)

| Librería | Versión | Para qué se usa |
|---|---|---|
| **Tailwind CSS** | ^4.0 | Estilos y diseño del panel de administración |
| **Alpine.js** | ^3.15 | Interactividad ligera (sidebar móvil, mensajes flash) |
| **Vite** | ^7.0 | Compilación y bundling de CSS y JS |
| **Chart.js** | ^4.4 (CDN) | Gráficas del dashboard (citas por mes, estados) |

## Herramientas de desarrollo

| Herramienta | Para qué se usa |
|---|---|
| **Faker** | Generación de datos de prueba en seeders |
| **Laravel Pint** | Formateo automático de código PHP |
| **PHPUnit** | Pruebas unitarias |
