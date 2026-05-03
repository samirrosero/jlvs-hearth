@component('mail::message')
# ¡Bienvenido a {{ $empresa?->nombre ?? 'JLVS Health' }}!

Hola {{ $nombre }},

Tu cuenta ha sido creada exitosamente en nuestro sistema de gestión de salud. A continuación encontrarás tus credenciales de acceso temporales:

---

## 📧 Tus Credenciales

**Correo electrónico:** {{ $correo }}
**Contraseña temporal:** `{{ $passwordTemporal }}`

---

@component('mail::button', ['url' => $urlLogin, 'color' => 'primary'])
🔐 Iniciar Sesión
@endcomponent

---

## ⚠️ Importante

1. **Esta es una contraseña temporal** - Te solicitaremos cambiarla en tu primer inicio de sesión.
2. **No compartas tus credenciales** - Mantén tu información segura.
3. **Tu rol asignado:** {{ ucwords(str_replace('_', ' ', $rol)) }}

---

## 🆘 ¿Necesitas ayuda?

Si tienes problemas para acceder o tienes preguntas sobre el sistema, contacta al administrador de tu institución.

---

Saludos cordiales,

**Equipo de {{ $empresa?->nombre ?? 'JLVS Health' }}**

<small>Si no reconoces esta cuenta o crees que es un error, por favor ignora este correo.</small>
@endcomponent
