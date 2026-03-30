# Plan de Implementación: Sistema Completo de Correos Electrónicos

## 📧 Correo a Utilizar
**Email**: oficinacoordinadoraacademica@correohuv.gov.co

---

## 🎯 Objetivos

Implementar sistema de envío de correos para:

1. ✅ Verificación de cuenta (al registrarse)
2. ✅ Recuperación de contraseña (olvidé mi contraseña)
3. ✅ Inscripción a curso (cuando se inscriben)
4. ✅ Asignación de curso (cuando les asignan un curso)
5. ✅ Bienvenida (después de verificar email)

---

## 📁 Archivos a Crear/Modificar

### 1. Configuración

#### `.env`
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=oficinacoordinadoraacademica@correohuv.gov.co
MAIL_PASSWORD="[CONTRASEÑA_APLICACION]"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=oficinacoordinadoraacademica@correohuv.gov.co
MAIL_FROM_NAME="Hospital Universitario del Valle"
```

### 2. Mailables (Clases de Correo)

#### `app/Mail/VerificarCuenta.php`
- Correo de verificación de cuenta
- Incluye enlace de verificación
- Diseño profesional

#### `app/Mail/RecuperarPassword.php`
- Correo de recuperación de contraseña
- Incluye enlace temporal
- Instrucciones claras

#### `app/Mail/InscripcionCurso.php`
- Notificación de inscripción exitosa
- Detalles del curso
- Enlace al aula virtual

#### `app/Mail/AsignacionCurso.php`
- Notificación de asignación de curso
- Información del curso
- Enlace para inscribirse

#### `app/Mail/BienvenidaUsuario.php`
- Correo de bienvenida
- Información de la plataforma
- Primeros pasos

### 3. Vistas de Correo (Blade Templates)

#### `resources/views/emails/layout.blade.php`
- Layout base para todos los correos
- Header con logo
- Footer con información de contacto
- Diseño responsive

#### `resources/views/emails/verificar-cuenta.blade.php`
- Vista del correo de verificación

#### `resources/views/emails/recuperar-password.blade.php`
- Vista del correo de recuperación

#### `resources/views/emails/inscripcion-curso.blade.php`
- Vista del correo de inscripción

#### `resources/views/emails/asignacion-curso.blade.php`
- Vista del correo de asignación

#### `resources/views/emails/bienvenida.blade.php`
- Vista del correo de bienvenida

### 4. Notificaciones

#### `app/Notifications/VerificarEmail.php`
- Notificación personalizada de verificación

#### `app/Notifications/ResetPasswordNotification.php`
- Notificación personalizada de reset password

### 5. Controladores (Modificar)

#### `app/Http/Controllers/Auth/RegisterController.php`
- Enviar correo de verificación al registrarse

#### `app/Http/Controllers/Auth/ForgotPasswordController.php`
- Enviar correo de recuperación

#### `app/Http/Controllers/CursoController.php`
- Enviar correo al inscribirse a curso

#### `app/Http/Controllers/Admin/CursoController.php`
- Enviar correo al asignar curso

### 6. Modelos (Modificar)

#### `app/Models/User.php`
- Implementar `MustVerifyEmail`
- Sobrescribir métodos de notificación

### 7. Rutas (Modificar)

#### `routes/web.php`
- Agregar rutas de verificación de email
- Rutas de confirmación

### 8. Migraciones

#### `database/migrations/xxxx_add_email_verified_to_users.php`
- Agregar campo `email_verified_at` si no existe
- Agregar campo `verification_token`

---

## 🎨 Diseño de Correos

### Estructura HTML
```
┌─────────────────────────────────────┐
│         HEADER (Logo + Nombre)      │
├─────────────────────────────────────┤
│                                     │
│         CONTENIDO PRINCIPAL         │
│         - Título                    │
│         - Mensaje                   │
│         - Botón de Acción           │
│         - Información Adicional     │
│                                     │
├─────────────────────────────────────┤
│         FOOTER                      │
│         - Información de contacto   │
│         - Enlaces útiles            │
│         - Redes sociales            │
└─────────────────────────────────────┘
```

### Colores Corporativos
- Primario: #2c4370
- Secundario: #1e2f4d
- Fondo: #f6f7f8
- Texto: #333333

---

## 🔄 Flujos de Correo

### 1. Registro de Usuario
```
Usuario se registra
    ↓
Sistema crea cuenta (sin verificar)
    ↓
Envía correo de verificación
    ↓
Usuario hace clic en enlace
    ↓
Cuenta verificada
    ↓
Envía correo de bienvenida
    ↓
Usuario puede acceder
```

### 2. Recuperación de Contraseña
```
Usuario olvida contraseña
    ↓
Solicita recuperación
    ↓
Sistema envía correo con enlace
    ↓
Usuario hace clic en enlace
    ↓
Restablece contraseña
    ↓
Confirmación de cambio
```

### 3. Inscripción a Curso
```
Usuario se inscribe a curso
    ↓
Sistema registra inscripción
    ↓
Envía correo de confirmación
    ↓
Usuario recibe detalles del curso
```

### 4. Asignación de Curso
```
Admin asigna curso a usuario
    ↓
Sistema crea asignación
    ↓
Envía correo de notificación
    ↓
Usuario recibe enlace para inscribirse
```

---

## 🔧 Comandos Artisan

### Crear Mailables
```bash
php artisan make:mail VerificarCuenta
php artisan make:mail RecuperarPassword
php artisan make:mail InscripcionCurso
php artisan make:mail AsignacionCurso
php artisan make:mail BienvenidaUsuario
```

### Crear Notificaciones
```bash
php artisan make:notification VerificarEmail
php artisan make:notification ResetPasswordNotification
```

### Probar Envío de Correos
```bash
php artisan tinker
>>> Mail::to('test@example.com')->send(new App\Mail\VerificarCuenta($user));
```

---

## 🧪 Testing

### Script de Prueba
`test_sistema_correos.php`
- Verificar configuración de correo
- Probar envío de cada tipo de correo
- Validar enlaces generados
- Verificar diseño responsive

---

## 📊 Monitoreo

### Logs de Correo
- `storage/logs/laravel.log`
- Registrar intentos de envío
- Registrar errores
- Registrar correos enviados exitosamente

### Dashboard de Correos (Opcional)
- Ver correos enviados
- Ver correos fallidos
- Reenviar correos
- Estadísticas

---

## ⚙️ Configuración Avanzada (Opcional)

### Colas de Correo
Para mejor rendimiento, usar colas:

```env
QUEUE_CONNECTION=database
```

```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

### Rate Limiting
Limitar envíos para evitar spam:
- Máximo 100 correos por hora
- Máximo 10 correos por minuto

---

## 🔐 Seguridad

### Tokens de Verificación
- Generar tokens únicos y seguros
- Expiración de 24 horas
- Un solo uso

### Protección contra Spam
- Validar email antes de enviar
- Verificar que el usuario existe
- Rate limiting

### Privacidad
- No incluir información sensible en correos
- Usar enlaces seguros (HTTPS)
- Cumplir con GDPR/LOPD

---

## 📝 Contenido de Correos

### 1. Verificación de Cuenta
**Asunto**: Verifica tu cuenta - Hospital Universitario del Valle

**Contenido**:
- Saludo personalizado
- Mensaje de bienvenida
- Botón "Verificar mi cuenta"
- Enlace alternativo
- Información de soporte

### 2. Recuperación de Contraseña
**Asunto**: Recupera tu contraseña - Hospital Universitario del Valle

**Contenido**:
- Saludo personalizado
- Mensaje de seguridad
- Botón "Restablecer contraseña"
- Tiempo de expiración (60 minutos)
- Aviso si no solicitó el cambio

### 3. Inscripción a Curso
**Asunto**: Inscripción exitosa - [Nombre del Curso]

**Contenido**:
- Confirmación de inscripción
- Detalles del curso (nombre, instructor, fechas)
- Botón "Ir al aula virtual"
- Próximos pasos
- Información de contacto del instructor

### 4. Asignación de Curso
**Asunto**: Te han asignado un curso - [Nombre del Curso]

**Contenido**:
- Notificación de asignación
- Detalles del curso
- Botón "Inscribirme ahora"
- Fecha límite de inscripción
- Beneficios del curso

### 5. Bienvenida
**Asunto**: ¡Bienvenido a la plataforma! - Hospital Universitario del Valle

**Contenido**:
- Mensaje de bienvenida
- Características de la plataforma
- Primeros pasos
- Recursos disponibles
- Información de soporte

---

## 🚀 Orden de Implementación

1. ✅ Configurar `.env` con credenciales de Gmail
2. ✅ Crear layout base de correos
3. ✅ Crear Mailable de verificación de cuenta
4. ✅ Crear Mailable de recuperación de contraseña
5. ✅ Crear Mailable de inscripción a curso
6. ✅ Crear Mailable de asignación de curso
7. ✅ Crear Mailable de bienvenida
8. ✅ Modificar controladores para enviar correos
9. ✅ Crear script de pruebas
10. ✅ Probar cada tipo de correo
11. ✅ Documentar sistema completo

---

## 📋 Checklist de Implementación

- [ ] Configuración de Gmail en `.env`
- [ ] Layout base de correos creado
- [ ] Mailable de verificación creado
- [ ] Mailable de recuperación creado
- [ ] Mailable de inscripción creado
- [ ] Mailable de asignación creado
- [ ] Mailable de bienvenida creado
- [ ] Controladores modificados
- [ ] Rutas de verificación agregadas
- [ ] Script de pruebas creado
- [ ] Pruebas realizadas
- [ ] Documentación completa

---

**Fecha**: 21 de enero de 2026
**Estado**: ⏳ Esperando datos de configuración
