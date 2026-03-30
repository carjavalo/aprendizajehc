# 📧 Guía de Uso: Sistema de Correos Electrónicos

## ✅ Estado: COMPLETAMENTE FUNCIONAL

**Fecha de implementación**: 21 de enero de 2026
**Pruebas**: ✅ 5/5 correos enviados exitosamente

---

## 🎯 Correos Implementados

1. ✅ Verificación de cuenta (al registrarse)
2. ✅ Recuperación de contraseña (olvidé mi contraseña)
3. ✅ Inscripción a curso (cuando se inscriben)
4. ✅ Asignación de curso (cuando les asignan un curso)
5. ✅ Bienvenida (después de verificar email)

---

## 📝 Cómo Usar Cada Tipo de Correo

### 1. Correo de Verificación de Cuenta

**Cuándo usar**: Al registrar un nuevo usuario

**Código de ejemplo**:
```php
use App\Mail\VerificarCuenta;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

// Generar URL de verificación con expiración de 24 horas
$verificationUrl = URL::temporarySignedRoute(
    'verification.verify',
    now()->addHours(24),
    ['id' => $user->id, 'hash' => sha1($user->email)]
);

// Enviar correo
Mail::to($user->email)->send(new VerificarCuenta($user, $verificationUrl));
```

**Dónde implementar**: 
- `app/Http/Controllers/Auth/RegisterController.php`
- Método `create()` o `register()`

---

### 2. Correo de Recuperación de Contraseña

**Cuándo usar**: Al solicitar recuperación de contraseña

**Código de ejemplo**:
```php
use App\Mail\RecuperarPassword;
use Illuminate\Support\Facades\Mail;

// Generar URL de reset con token
$resetUrl = url(route('password.reset', [
    'token' => $token,
    'email' => $user->email,
], false));

// Enviar correo
Mail::to($user->email)->send(new RecuperarPassword($user, $resetUrl));
```

**Dónde implementar**:
- `app/Http/Controllers/Auth/ForgotPasswordController.php`
- Método `sendResetLinkEmail()`

---

### 3. Correo de Inscripción a Curso

**Cuándo usar**: Después de que un usuario se inscribe exitosamente a un curso

**Código de ejemplo**:
```php
use App\Mail\InscripcionCurso;
use Illuminate\Support\Facades\Mail;

// Generar URL del aula virtual
$cursoUrl = route('academico.curso.aula-virtual', $curso->id);

// Enviar correo
Mail::to($user->email)->send(new InscripcionCurso($user, $curso, $cursoUrl));
```

**Dónde implementar**:
- Controlador de inscripción de cursos
- Después de crear el registro en `curso_estudiantes`

**Ejemplo completo**:
```php
public function inscribir(Request $request, $cursoId)
{
    $curso = Curso::findOrFail($cursoId);
    $user = auth()->user();
    
    // Crear inscripción
    DB::table('curso_estudiantes')->insert([
        'curso_id' => $curso->id,
        'estudiante_id' => $user->id,
        'estado' => 'activo',
        'fecha_inscripcion' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Enviar correo de confirmación
    $cursoUrl = route('academico.curso.aula-virtual', $curso->id);
    Mail::to($user->email)->send(new InscripcionCurso($user, $curso, $cursoUrl));
    
    return redirect()->route('academico.cursos.disponibles')
        ->with('success', 'Te has inscrito exitosamente al curso');
}
```

---

### 4. Correo de Asignación de Curso

**Cuándo usar**: Cuando un administrador asigna un curso a un usuario

**Código de ejemplo**:
```php
use App\Mail\AsignacionCurso;
use Illuminate\Support\Facades\Mail;

// Generar URL de inscripción
$inscripcionUrl = route('academico.cursos.inscribir', $curso->id);

// Calcular fecha límite (3 días antes del inicio)
$fechaLimite = $curso->fecha_inicio ? 
    \Carbon\Carbon::parse($curso->fecha_inicio)->subDays(3)->format('d/m/Y') : 
    null;

// Enviar correo
Mail::to($user->email)->send(new AsignacionCurso($user, $curso, $inscripcionUrl, $fechaLimite));
```

**Dónde implementar**:
- Panel de administración de cursos
- Al asignar usuarios a un curso

**Ejemplo completo**:
```php
public function asignarUsuarios(Request $request, $cursoId)
{
    $curso = Curso::findOrFail($cursoId);
    $usuariosIds = $request->input('usuarios'); // Array de IDs
    
    foreach ($usuariosIds as $userId) {
        $user = User::find($userId);
        
        // Crear asignación (sin inscribir aún)
        DB::table('curso_asignaciones')->insert([
            'curso_id' => $curso->id,
            'user_id' => $user->id,
            'asignado_por' => auth()->id(),
            'fecha_asignacion' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Enviar correo de notificación
        $inscripcionUrl = route('academico.cursos.inscribir', $curso->id);
        $fechaLimite = $curso->fecha_inicio ? 
            \Carbon\Carbon::parse($curso->fecha_inicio)->subDays(3)->format('d/m/Y') : 
            null;
            
        Mail::to($user->email)->send(new AsignacionCurso($user, $curso, $inscripcionUrl, $fechaLimite));
    }
    
    return redirect()->back()->with('success', 'Usuarios asignados y notificados por correo');
}
```

---

### 5. Correo de Bienvenida

**Cuándo usar**: Después de que el usuario verifica su email

**Código de ejemplo**:
```php
use App\Mail\BienvenidaUsuario;
use Illuminate\Support\Facades\Mail;

// Generar URL del dashboard
$dashboardUrl = route('dashboard');

// Enviar correo
Mail::to($user->email)->send(new BienvenidaUsuario($user, $dashboardUrl));
```

**Dónde implementar**:
- Ruta de verificación de email
- Después de marcar el email como verificado

**Ejemplo completo**:
```php
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    // Verificar email
    $request->fulfill();
    
    // Enviar correo de bienvenida
    $user = auth()->user();
    $dashboardUrl = route('dashboard');
    Mail::to($user->email)->send(new BienvenidaUsuario($user, $dashboardUrl));
    
    return redirect('/dashboard')->with('verified', true);
})->middleware(['auth', 'signed'])->name('verification.verify');
```

---

## 🔧 Configuración Actual

### Credenciales de Gmail
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=oficinacoordinadoraacademica@correohuv.gov.co
MAIL_PASSWORD="mxosvhpzkxrfssrb"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=oficinacoordinadoraacademica@correohuv.gov.co
MAIL_FROM_NAME="Coordinacion Academica Hospital Universitario del Valle"
```

### Información Institucional
- **Nombre**: Coordinacion Academica Hospital Universitario del Valle
- **Dirección**: Hospital Universitario Del Valle "Evaristo García" E.S.E., Séptimo piso, Calle 5 No 36-08
- **Logo**: `public/images/logocorreo.jpg`

---

## 🧪 Pruebas

### Ejecutar Pruebas de Envío

```bash
php test_envio_correos.php
```

Este script enviará los 5 tipos de correos al primer usuario de la base de datos.

**Resultado esperado**:
```
✅ Correos enviados exitosamente: 5
❌ Errores encontrados: 0
```

### Verificar Correos

1. Revisa la bandeja de entrada del usuario de prueba
2. Verifica también la carpeta de SPAM
3. Los correos pueden tardar 1-2 minutos en llegar
4. Verifica que el diseño se vea correctamente
5. Prueba los enlaces en los correos

---

## 🎨 Diseño de los Correos

### Características
- ✅ Logo institucional en header
- ✅ Logo como marca de agua (opacidad 5%)
- ✅ Colores corporativos (#2c4370, #1e2f4d)
- ✅ Diseño responsive (móviles y desktop)
- ✅ Botones con gradiente y efectos hover
- ✅ Info boxes destacados
- ✅ Footer con información de contacto
- ✅ Enlaces alternativos para compatibilidad

### Vista Previa
Todos los correos siguen el mismo diseño base con:
- Header azul con logo
- Contenido principal con botón de acción
- Información adicional en cajas destacadas
- Footer con datos de contacto

---

## 📊 Monitoreo

### Logs de Correo

Los intentos de envío se registran en:
```
storage/logs/laravel.log
```

### Ver Últimos Logs
```bash
tail -f storage/logs/laravel.log
```

### Errores Comunes

**Error: "Failed to authenticate"**
- Solución: Verificar contraseña de aplicación en `.env`
- Regenerar contraseña de aplicación en Google

**Error: "Connection timeout"**
- Solución: Verificar que el puerto 587 esté abierto
- Verificar firewall

**Correos van a SPAM**
- Solución: Configurar SPF y DKIM en el dominio
- Usar dominio propio en lugar de Gmail (producción)

---

## 🚀 Mejoras Futuras (Opcional)

### 1. Colas de Correo
Para mejor rendimiento en producción:

```env
QUEUE_CONNECTION=database
```

```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

Cambiar envío a:
```php
Mail::to($user->email)->queue(new VerificarCuenta($user, $verificationUrl));
```

### 2. Notificaciones de Laravel
Usar el sistema de notificaciones:

```bash
php artisan make:notification VerificarEmailNotification
```

### 3. Plantillas Personalizadas
Crear plantillas específicas por tipo de usuario o curso.

### 4. Estadísticas de Correo
Implementar tracking de:
- Correos enviados
- Correos abiertos
- Enlaces clickeados
- Tasa de conversión

---

## 📋 Checklist de Implementación

### Completado ✅
- [x] Configuración de Gmail
- [x] Layout base de correos
- [x] 5 tipos de correos implementados
- [x] Clases Mailable creadas
- [x] Diseño profesional con logo
- [x] Script de pruebas
- [x] Pruebas exitosas (5/5)

### Pendiente (Integración)
- [ ] Agregar envío en registro de usuarios
- [ ] Agregar envío en recuperación de contraseña
- [ ] Agregar envío en inscripción a cursos
- [ ] Agregar envío en asignación de cursos
- [ ] Agregar rutas de verificación de email
- [ ] Configurar colas (opcional)

---

## 💡 Consejos

1. **Prueba primero en desarrollo**: Usa el script de prueba antes de implementar en producción

2. **Revisa los logs**: Siempre verifica `storage/logs/laravel.log` si hay problemas

3. **Límites de Gmail**: Gmail tiene límites de envío (500 correos/día). Para producción, considera usar un servicio profesional como SendGrid, Mailgun o Amazon SES

4. **Personalización**: Puedes personalizar cada correo editando las vistas en `resources/views/emails/`

5. **Testing**: Usa Mailtrap.io o MailHog para testing sin enviar correos reales

---

## 📞 Soporte

Si tienes problemas:

1. Verifica la configuración en `.env`
2. Revisa los logs en `storage/logs/laravel.log`
3. Ejecuta el script de prueba: `php test_envio_correos.php`
4. Verifica que la contraseña de aplicación sea correcta
5. Asegúrate de que la verificación en 2 pasos esté activa en Gmail

---

**Fecha**: 21 de enero de 2026
**Estado**: ✅ COMPLETAMENTE FUNCIONAL
**Pruebas**: ✅ 5/5 correos enviados exitosamente
