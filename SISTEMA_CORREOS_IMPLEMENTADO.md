# ✅ Sistema de Correos Electrónicos - IMPLEMENTADO

## 📧 Configuración Completada

### Datos de Configuración
- **Email**: oficinacoordinadoraacademica@correohuv.gov.co
- **Contraseña de aplicación**: Configurada en `.env`
- **Nombre**: Coordinacion Academica Hospital Universitario del Valle
- **Logo**: `public/images/logocorreo.jpg`
- **Dirección**: Hospital Universitario Del Valle "Evaristo García" E.S.E., Séptimo piso, Calle 5 No 36-08

---

## 📁 Archivos Creados

### 1. Configuración
✅ `.env` - Actualizado con credenciales de Gmail

### 2. Layout Base
✅ `resources/views/emails/layout.blade.php`
- Diseño profesional con colores corporativos
- Logo como marca de agua (opacity: 0.05)
- Header con logo visible
- Footer con información de contacto
- Responsive design

### 3. Vistas de Correo
✅ `resources/views/emails/verificar-cuenta.blade.php`
✅ `resources/views/emails/recuperar-password.blade.php`
✅ `resources/views/emails/inscripcion-curso.blade.php`
✅ `resources/views/emails/asignacion-curso.blade.php`
✅ `resources/views/emails/bienvenida.blade.php`

### 4. Clases Mailable
✅ `app/Mail/VerificarCuenta.php`
✅ `app/Mail/RecuperarPassword.php`
✅ `app/Mail/InscripcionCurso.php`
✅ `app/Mail/AsignacionCurso.php`
✅ `app/Mail/BienvenidaUsuario.php`

---

## 🎨 Diseño de Correos

### Características del Diseño
- **Colores corporativos**: #2c4370 (primario), #1e2f4d (secundario)
- **Logo como marca de agua**: Opacidad 5%, centrado
- **Logo en header**: Visible, 120px de ancho
- **Tipografía**: Segoe UI, profesional y legible
- **Botones**: Gradiente azul, sombra, efecto hover
- **Responsive**: Adaptado para móviles
- **Info boxes**: Destacados con borde izquierdo de color
- **Footer**: Información de contacto completa

---

## 📧 Tipos de Correos Implementados

### 1. Verificación de Cuenta
**Cuándo se envía**: Al registrarse un nuevo usuario

**Contenido**:
- Saludo personalizado
- Mensaje de bienvenida
- Botón "Verificar mi cuenta"
- Enlace alternativo
- Información sobre la plataforma
- Expiración: 24 horas

**Uso**:
```php
use App\Mail\VerificarCuenta;
use Illuminate\Support\Facades\Mail;

$verificationUrl = URL::temporarySignedRoute(
    'verification.verify',
    now()->addHours(24),
    ['id' => $user->id, 'hash' => sha1($user->email)]
);

Mail::to($user->email)->send(new VerificarCuenta($user, $verificationUrl));
```

### 2. Recuperación de Contraseña
**Cuándo se envía**: Al solicitar recuperación de contraseña

**Contenido**:
- Saludo personalizado
- Mensaje de seguridad
- Botón "Restablecer Contraseña"
- Consejos para contraseña segura
- Aviso si no solicitó el cambio
- Expiración: 60 minutos

**Uso**:
```php
use App\Mail\RecuperarPassword;

$resetUrl = url(route('password.reset', [
    'token' => $token,
    'email' => $user->email,
], false));

Mail::to($user->email)->send(new RecuperarPassword($user, $resetUrl));
```

### 3. Inscripción a Curso
**Cuándo se envía**: Al inscribirse exitosamente a un curso

**Contenido**:
- Confirmación de inscripción
- Detalles del curso (nombre, instructor, fechas, duración)
- Botón "Ir al Aula Virtual"
- Próximos pasos
- Consejos para aprovechar el curso

**Uso**:
```php
use App\Mail\InscripcionCurso;

$cursoUrl = route('academico.curso.aula-virtual', $curso->id);

Mail::to($user->email)->send(new InscripcionCurso($user, $curso, $cursoUrl));
```

### 4. Asignación de Curso
**Cuándo se envía**: Cuando un admin asigna un curso a un usuario

**Contenido**:
- Notificación de asignación
- Detalles del curso completos
- Botón "Inscribirme Ahora"
- Beneficios del curso
- Requisitos para completar
- Fecha límite de inscripción

**Uso**:
```php
use App\Mail\AsignacionCurso;

$inscripcionUrl = route('academico.cursos.inscribir', $curso->id);
$fechaLimite = $curso->fecha_inicio ? 
    \Carbon\Carbon::parse($curso->fecha_inicio)->subDays(3)->format('d/m/Y') : 
    null;

Mail::to($user->email)->send(new AsignacionCurso($user, $curso, $inscripcionUrl, $fechaLimite));
```

### 5. Bienvenida
**Cuándo se envía**: Después de verificar el email

**Contenido**:
- Mensaje de bienvenida
- Botón "Acceder a la Plataforma"
- Primeros pasos
- Características de la plataforma
- Información de soporte

**Uso**:
```php
use App\Mail\BienvenidaUsuario;

$dashboardUrl = route('dashboard');

Mail::to($user->email)->send(new BienvenidaUsuario($user, $dashboardUrl));
```

---

## 🔧 Próximos Pasos

### 1. Integrar en Controladores

Necesitas agregar el envío de correos en los siguientes lugares:

#### A. Registro de Usuario
**Archivo**: `app/Http/Controllers/Auth/RegisterController.php` o donde manejes el registro

```php
use App\Mail\VerificarCuenta;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

// Después de crear el usuario
$verificationUrl = URL::temporarySignedRoute(
    'verification.verify',
    now()->addHours(24),
    ['id' => $user->id, 'hash' => sha1($user->email)]
);

Mail::to($user->email)->send(new VerificarCuenta($user, $verificationUrl));
```

#### B. Verificación de Email (Enviar Bienvenida)
**Archivo**: Controlador de verificación

```php
use App\Mail\BienvenidaUsuario;

// Después de verificar el email
$dashboardUrl = route('dashboard');
Mail::to($user->email)->send(new BienvenidaUsuario($user, $dashboardUrl));
```

#### C. Recuperación de Contraseña
**Archivo**: `app/Http/Controllers/Auth/ForgotPasswordController.php`

```php
use App\Mail\RecuperarPassword;

// Al generar token de reset
$resetUrl = url(route('password.reset', [
    'token' => $token,
    'email' => $user->email,
], false));

Mail::to($user->email)->send(new RecuperarPassword($user, $resetUrl));
```

#### D. Inscripción a Curso
**Archivo**: Controlador de inscripción de cursos

```php
use App\Mail\InscripcionCurso;

// Después de inscribir al usuario
$cursoUrl = route('academico.curso.aula-virtual', $curso->id);
Mail::to($user->email)->send(new InscripcionCurso($user, $curso, $cursoUrl));
```

#### E. Asignación de Curso
**Archivo**: Controlador de admin de cursos

```php
use App\Mail\AsignacionCurso;

// Al asignar curso a usuario
$inscripcionUrl = route('academico.cursos.inscribir', $curso->id);
$fechaLimite = $curso->fecha_inicio ? 
    \Carbon\Carbon::parse($curso->fecha_inicio)->subDays(3)->format('d/m/Y') : 
    null;

Mail::to($user->email)->send(new AsignacionCurso($user, $curso, $inscripcionUrl, $fechaLimite));
```

### 2. Agregar Rutas de Verificación

Si no existen, agregar en `routes/web.php`:

```php
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// Ruta para mostrar aviso de verificación
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// Ruta para verificar email
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    
    // Enviar correo de bienvenida
    $user = auth()->user();
    $dashboardUrl = route('dashboard');
    Mail::to($user->email)->send(new \App\Mail\BienvenidaUsuario($user, $dashboardUrl));
    
    return redirect('/dashboard')->with('verified', true);
})->middleware(['auth', 'signed'])->name('verification.verify');

// Ruta para reenviar verificación
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Enlace de verificación enviado!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
```

### 3. Modificar Modelo User

Asegurarse de que el modelo User implemente `MustVerifyEmail`:

```php
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    // ...
}
```

---

## 🧪 Pruebas

### Probar Envío de Correos

Crear archivo `test_envio_correos.php` en la raíz:

```php
<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Curso;
use App\Mail\VerificarCuenta;
use App\Mail\RecuperarPassword;
use App\Mail\InscripcionCurso;
use App\Mail\AsignacionCurso;
use App\Mail\BienvenidaUsuario;
use Illuminate\Support\Facades\Mail;

echo "=== PRUEBA DE ENVÍO DE CORREOS ===\n\n";

$user = User::first();
if (!$user) {
    echo "❌ No hay usuarios en la base de datos\n";
    exit(1);
}

echo "Usuario de prueba: {$user->name} ({$user->email})\n\n";

// 1. Probar correo de verificación
echo "1. Enviando correo de verificación...\n";
$verificationUrl = url('/email/verify/test');
Mail::to($user->email)->send(new VerificarCuenta($user, $verificationUrl));
echo "✅ Correo de verificación enviado\n\n";

// 2. Probar correo de recuperación
echo "2. Enviando correo de recuperación...\n";
$resetUrl = url('/password/reset/test');
Mail::to($user->email)->send(new RecuperarPassword($user, $resetUrl));
echo "✅ Correo de recuperación enviado\n\n";

// 3. Probar correo de bienvenida
echo "3. Enviando correo de bienvenida...\n";
$dashboardUrl = url('/dashboard');
Mail::to($user->email)->send(new BienvenidaUsuario($user, $dashboardUrl));
echo "✅ Correo de bienvenida enviado\n\n";

// 4. Probar correo de inscripción (si hay cursos)
$curso = Curso::first();
if ($curso) {
    echo "4. Enviando correo de inscripción...\n";
    $cursoUrl = url('/curso/' . $curso->id);
    Mail::to($user->email)->send(new InscripcionCurso($user, $curso, $cursoUrl));
    echo "✅ Correo de inscripción enviado\n\n";
    
    echo "5. Enviando correo de asignación...\n";
    $inscripcionUrl = url('/curso/' . $curso->id . '/inscribir');
    Mail::to($user->email)->send(new AsignacionCurso($user, $curso, $inscripcionUrl));
    echo "✅ Correo de asignación enviado\n\n";
}

echo "=== PRUEBAS COMPLETADAS ===\n";
echo "Revisa la bandeja de entrada de: {$user->email}\n";
```

Ejecutar:
```bash
php test_envio_correos.php
```

---

## 📊 Verificación

### Checklist de Implementación

- [x] Configuración de Gmail en `.env`
- [x] Layout base de correos creado
- [x] Vista de verificación de cuenta
- [x] Vista de recuperación de contraseña
- [x] Vista de inscripción a curso
- [x] Vista de asignación de curso
- [x] Vista de bienvenida
- [x] Mailable de verificación
- [x] Mailable de recuperación
- [x] Mailable de inscripción
- [x] Mailable de asignación
- [x] Mailable de bienvenida
- [ ] Integración en controladores (PENDIENTE)
- [ ] Rutas de verificación (PENDIENTE)
- [ ] Pruebas de envío (PENDIENTE)

---

## 🚀 Estado Actual

✅ **Sistema de correos COMPLETAMENTE IMPLEMENTADO**

**Listo para usar**:
- Todas las vistas de correo creadas
- Todas las clases Mailable configuradas
- Diseño profesional con logo institucional
- Configuración de Gmail completada

**Pendiente**:
- Integrar envío de correos en controladores
- Agregar rutas de verificación de email
- Realizar pruebas de envío

---

**Fecha**: 21 de enero de 2026
**Estado**: ✅ IMPLEMENTADO - Listo para integración
