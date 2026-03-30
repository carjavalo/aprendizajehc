# ✅ FLUJO CORRECTO: Correos y Asignación de Cursos

**Fecha:** 22 de enero de 2026  
**Estado:** IMPLEMENTADO

---

## 🎯 FLUJO CORRECTO DE CORREOS

### REGISTRO AUTOMÁTICO (Curso ID 18)

#### 1️⃣ Usuario se Registra
**Acción:** Usuario completa formulario de registro

**Sistema:**
- Crea usuario con rol "Estudiante"
- **NO asigna curso todavía**
- **NO envía correo de asignación todavía**

**Correo enviado:**
- ✉️ **Verificación de cuenta** (inmediato)
  - Asunto: "Verifica tu cuenta"
  - Contenido: Enlace de verificación válido por 24 horas
  - Idioma: Español

---

#### 2️⃣ Usuario Verifica Email
**Acción:** Usuario hace clic en enlace de verificación

**Sistema:**
- Marca email como verificado
- Usuario ingresa al sistema

**Correo enviado:**
- ✉️ **Bienvenida** (inmediato)
  - Asunto: "¡Bienvenido a la plataforma!"
  - Contenido: Información de la plataforma, consejos, recursos
  - **SIN enlace de ingreso** (solo informativo)
  - Idioma: Español

---

#### 3️⃣ Asignación Automática de Curso (1 minuto después)
**Acción:** Sistema ejecuta job programado

**Sistema:**
- Asigna curso ID 18 "Inducción Institucional (General)"
- Crea registro en `curso_asignaciones`
- Estado: 'activo'

**Correo enviado:**
- ✉️ **Asignación de curso** (1 minuto después de verificar)
  - Asunto: "Has sido asignado a un curso"
  - Contenido: Información del curso + enlace para inscribirse
  - Idioma: Español

---

### ASIGNACIÓN MANUAL (Otros Cursos)

#### 1️⃣ Administrador Asigna Curso
**Acción:** Admin va a `/configuracion/asignacion-cursos` y asigna curso(s)

**Sistema:**
- Busca estudiante por nombre, email o documento
- Selecciona curso(s) a asignar
- Crea registro(s) en `curso_asignaciones`
- Estado: 'activo'

---

#### 2️⃣ Notificación al Estudiante
**Correo enviado:**
- ✉️ **Asignación de curso** (inmediato)
  - Destinatario: Estudiante
  - Asunto: "Has sido asignado a un curso"
  - Contenido:
    * Información del curso
    * Nombre del instructor
    * Duración y modalidad
    * Enlace para inscribirse
    * Requisitos y beneficios
  - Idioma: Español

---

#### 3️⃣ Notificación al Instructor
**Correo enviado:**
- ✉️ **Nuevo estudiante asignado** (inmediato)
  - Destinatario: Instructor del curso
  - Asunto: "Nuevo estudiante asignado a tu curso"
  - Contenido:
    * Información del curso
    * Datos del estudiante (nombre, email, documento, área)
    * Próximos pasos
    * Recordatorios para el instructor
  - Idioma: Español

---

## 📊 RESUMEN DE CORREOS

### Registro Automático (3 correos)
| # | Cuándo | Destinatario | Asunto | Delay |
|---|--------|--------------|--------|-------|
| 1 | Al registrarse | Estudiante | Verifica tu cuenta | Inmediato |
| 2 | Al verificar email | Estudiante | ¡Bienvenido a la plataforma! | Inmediato |
| 3 | Después de verificar | Estudiante | Has sido asignado a un curso | 1 minuto |

### Asignación Manual (2 correos por curso)
| # | Cuándo | Destinatario | Asunto | Delay |
|---|--------|--------------|--------|-------|
| 1 | Al asignar | Estudiante | Has sido asignado a un curso | Inmediato |
| 2 | Al asignar | Instructor | Nuevo estudiante asignado | Inmediato |

---

## 🔄 FLUJO DETALLADO

### Registro Automático

```
TIEMPO 0:00 - Usuario se registra
├─ Sistema crea usuario (rol: Estudiante)
├─ ✉️ Envía correo: Verificación de cuenta
└─ Redirige a: /email/verify

TIEMPO 0:05 - Usuario verifica email
├─ Sistema marca email como verificado
├─ ✉️ Envía correo: Bienvenida (sin enlace)
├─ Programa job: Asignar curso ID 18 (delay: 1 minuto)
└─ Redirige a: /dashboard

TIEMPO 1:05 - Job ejecuta asignación
├─ Sistema asigna curso ID 18
├─ Crea registro en curso_asignaciones
└─ ✉️ Envía correo: Asignación de curso

RESULTADO:
✅ Usuario tiene asignación al curso ID 18
✅ Puede ver el curso en /academico/cursos-disponibles
✅ Estado: "Pendiente" (puede inscribirse)
```

### Asignación Manual

```
TIEMPO 0:00 - Admin asigna curso
├─ Admin selecciona estudiante
├─ Admin selecciona curso(s)
├─ Sistema crea asignación(es)
├─ ✉️ Envía correo al estudiante: Asignación de curso
└─ ✉️ Envía correo al instructor: Nuevo estudiante asignado

RESULTADO:
✅ Estudiante tiene asignación al curso
✅ Estudiante recibe notificación
✅ Instructor recibe notificación
✅ Curso aparece en /academico/cursos-disponibles
✅ Estado: "Pendiente" (puede inscribirse)
```

---

## 🛠️ IMPLEMENTACIÓN TÉCNICA

### 1. RegisteredUserController
**Archivo:** `app/Http/Controllers/Auth/RegisteredUserController.php`

**Cambios:**
- ❌ Eliminado: Asignación de curso ID 18
- ❌ Eliminado: Envío de correo de asignación
- ✅ Mantiene: Envío de correo de verificación

```php
// Solo envía correo de verificación
Mail::to($user->email)->send(new VerificarCuenta($user, $verificationUrl));
```

---

### 2. VerifyEmailController
**Archivo:** `app/Http/Controllers/Auth/VerifyEmailController.php`

**Cambios:**
- ✅ Agregado: Envío de correo de bienvenida (inmediato)
- ✅ Agregado: Programación de asignación de curso (1 minuto)

```php
// Enviar correo de bienvenida
Mail::to($user->email)->send(new BienvenidaUsuario($user, $dashboardUrl));

// Programar asignación con delay de 1 minuto
dispatch(function () use ($user) {
    // Asignar curso ID 18
    // Enviar correo de asignación
})->delay(now()->addMinute());
```

---

### 3. AsignacionCursoController
**Archivo:** `app/Http/Controllers/AsignacionCursoController.php`

**Cambios:**
- ✅ Agregado: Envío de correo al estudiante
- ✅ Agregado: Envío de correo al instructor

```php
// Enviar correo al estudiante
Mail::to($estudiante->email)->send(
    new AsignacionCurso($estudiante, $curso, $inscripcionUrl)
);

// Enviar correo al instructor
if ($curso->instructor) {
    Mail::to($curso->instructor->email)->send(
        new NotificacionInstructorAsignacion($instructor, $estudiante, $curso)
    );
}
```

---

### 4. Nuevos Archivos Creados

**Mailable:**
- `app/Mail/NotificacionInstructorAsignacion.php`

**Vista:**
- `resources/views/emails/notificacion-instructor-asignacion.blade.php`

---

## ✅ VENTAJAS DEL NUEVO FLUJO

### Para el Usuario
1. ✅ Recibe correos en orden lógico
2. ✅ No se siente abrumado con múltiples correos simultáneos
3. ✅ Tiene tiempo de explorar la plataforma antes de ver el curso
4. ✅ Correo de bienvenida es informativo (sin presión de ingresar)

### Para el Sistema
1. ✅ Mejor experiencia de usuario
2. ✅ Correos espaciados evitan spam
3. ✅ Logs claros de cada paso
4. ✅ Fácil de debuggear

### Para los Instructores
1. ✅ Reciben notificación cuando se asigna estudiante
2. ✅ Conocen quién es el estudiante
3. ✅ Pueden preparar el curso con anticipación
4. ✅ Mejor comunicación con coordinación académica

---

## 🧪 PRUEBAS

### Prueba 1: Registro Completo
```bash
# 1. Registrar usuario
# 2. Verificar correo de verificación (inmediato)
# 3. Hacer clic en enlace de verificación
# 4. Verificar correo de bienvenida (inmediato)
# 5. Esperar 1 minuto
# 6. Verificar correo de asignación de curso
# 7. Ir a /academico/cursos-disponibles
# 8. Verificar que aparezca curso ID 18
```

### Prueba 2: Asignación Manual
```bash
# 1. Admin va a /configuracion/asignacion-cursos
# 2. Busca estudiante
# 3. Selecciona curso
# 4. Asigna curso
# 5. Verificar correo al estudiante (inmediato)
# 6. Verificar correo al instructor (inmediato)
# 7. Estudiante ve curso en /academico/cursos-disponibles
```

---

## 📝 LOGS Y MONITOREO

### Logs Importantes

**Asignación exitosa:**
```
[INFO] Curso 18 asignado exitosamente al usuario {user_id}
[INFO] Correo de asignación enviado al usuario {user_id}
```

**Errores:**
```
[ERROR] Error al asignar curso 18: {mensaje}
[ERROR] Error al enviar correo de asignación al estudiante: {mensaje}
[ERROR] Error al enviar correo al instructor: {mensaje}
```

### Verificar Jobs
```bash
# Ver jobs pendientes
php artisan queue:work --once

# Ver jobs fallidos
php artisan queue:failed
```

---

## ⚠️ NOTAS IMPORTANTES

### Curso ID 18 (Inducción Institucional)
- ✅ Se asigna SOLO después de verificar email
- ✅ Delay de 1 minuto para mejor experiencia
- ✅ Solo para usuarios con rol "Estudiante"
- ✅ No se duplica si ya existe asignación

### Otros Cursos
- ✅ Se asignan manualmente desde `/configuracion/asignacion-cursos`
- ✅ Envían correo al estudiante Y al instructor
- ✅ Pueden asignarse múltiples cursos a la vez
- ✅ Instructor recibe notificación por cada estudiante asignado

### Correo de Bienvenida
- ✅ Solo informativo (sin enlace de ingreso)
- ✅ Contiene consejos y recursos
- ✅ Se envía inmediatamente después de verificar
- ✅ No presiona al usuario a ingresar

---

## 📞 SOPORTE

Para cualquier problema:
- **Email:** oficinacoordinadoraacademica@correohuv.gov.co
- **Ubicación:** Hospital Universitario del Valle, Séptimo piso

---

**Documento generado:** 22 de enero de 2026  
**Versión:** 1.0  
**Estado:** Sistema implementado y funcional
