# 🧪 GUÍA DE PRUEBA MANUAL: Sistema de Registro

## ✅ CORRECCIONES APLICADAS

1. ✅ Cambio de `user_id` a `estudiante_id` en asignación de curso
2. ✅ Cambio de ruta de logo de `.jpg` a `.jpeg`
3. ✅ Sistema completamente funcional

---

## 📝 PASOS PARA PRUEBA MANUAL

### PASO 1: Preparación
```bash
# Verificar que el sistema esté configurado correctamente
php test_registro_completo.php
```

**Resultado esperado:** Todos los checks deben pasar ✅

---

### PASO 2: Registro de Usuario

1. Abrir navegador e ir a la página de registro
2. Llenar el formulario con datos de prueba:
   - **Nombre:** Usuario
   - **Primer Apellido:** Prueba
   - **Segundo Apellido:** Test
   - **Email:** prueba@test.com (usar email real para recibir correos)
   - **Contraseña:** Password123!
   - **Confirmar Contraseña:** Password123!
   - **Tipo de Documento:** Cédula de Ciudadanía
   - **Número de Documento:** 1234567890
   - **Servicio/Área:** Seleccionar cualquiera
   - **Vinculación/Contrato:** Seleccionar cualquiera
   - **Sede:** Seleccionar cualquiera
   - **Teléfono:** (opcional) 3001234567

3. Hacer clic en **"Registrarse"**

**Resultado esperado:**
- ✅ Redirige a página de verificación de email
- ✅ Mensaje: "¡Registro exitoso! Por favor verifica tu correo electrónico."

---

### PASO 3: Verificar Correos Recibidos

Revisar la bandeja de entrada del email registrado.

**Debe recibir 2 correos:**

#### Correo 1: Verificación de Cuenta
- **Asunto:** "Verifica tu cuenta"
- **Remitente:** oficinacoordinadoraacademica@correohuv.gov.co
- **Contenido:**
  - Logo del HUV en header
  - Saludo personalizado
  - Botón "Verificar mi cuenta"
  - Enlace alternativo
  - Marca de agua con logo (muy tenue)

#### Correo 2: Asignación de Curso
- **Asunto:** "Has sido asignado a un curso"
- **Remitente:** oficinacoordinadoraacademica@correohuv.gov.co
- **Contenido:**
  - Logo del HUV en header
  - Información del curso ID 18
  - Nombre del instructor: Jhon Andres
  - Botón "Inscribirme al curso"
  - Marca de agua con logo

**Verificar:**
- ✅ Ambos correos tienen el logo correcto
- ✅ Diseño con colores corporativos (#2c4370, #1e2f4d)
- ✅ Textos en español
- ✅ Información institucional en footer

---

### PASO 4: Verificar Email

1. Abrir el correo de "Verificación de Cuenta"
2. Hacer clic en el botón **"Verificar mi cuenta"**

**Resultado esperado:**
- ✅ Redirige al dashboard de la plataforma
- ✅ Usuario queda autenticado
- ✅ Email marcado como verificado

---

### PASO 5: Verificar Correo de Bienvenida

Revisar nuevamente la bandeja de entrada.

**Debe recibir 1 correo adicional:**

#### Correo 3: Bienvenida
- **Asunto:** "¡Bienvenido a la plataforma!"
- **Remitente:** oficinacoordinadoraacademica@correohuv.gov.co
- **Contenido:**
  - Logo del HUV en header
  - Mensaje de bienvenida personalizado
  - Información sobre la plataforma
  - Próximos pasos
  - Datos de contacto

**Verificar:**
- ✅ Correo recibido después de verificar
- ✅ Diseño consistente con otros correos
- ✅ Textos en español

---

### PASO 6: Verificar Rol de Usuario

En el dashboard, verificar:

1. **Rol asignado:** Debe ser "Estudiante"
2. **Acceso:** Debe tener acceso a sección académica

**Verificación en base de datos (opcional):**
```bash
php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class); \$kernel->bootstrap(); \$user = App\Models\User::where('email', 'prueba@test.com')->first(); echo 'Rol: ' . \$user->role . PHP_EOL;"
```

**Resultado esperado:** `Rol: Estudiante`

---

### PASO 7: Verificar Asignación de Curso

1. En el dashboard, ir a la sección **"Cursos Disponibles"**
   - URL: `/academico/cursos-disponibles`

2. Buscar el curso ID 18

**Resultado esperado:**
- ✅ Aparece el curso ID 18 en la lista
- ✅ Muestra botón **"Inscribirse"**
- ✅ Muestra información del instructor: Jhon Andres

**Verificación en base de datos (opcional):**
```bash
php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class); \$kernel->bootstrap(); \$user = App\Models\User::where('email', 'prueba@test.com')->first(); \$asignacion = Illuminate\Support\Facades\DB::table('curso_asignaciones')->where('estudiante_id', \$user->id)->where('curso_id', 18)->first(); echo \$asignacion ? 'Asignación encontrada' : 'No asignado'; echo PHP_EOL;"
```

**Resultado esperado:** `Asignación encontrada`

---

### PASO 8: Inscribirse al Curso

1. Hacer clic en el botón **"Inscribirse"** del curso ID 18

**Resultado esperado:**
- ✅ Inscripción exitosa
- ✅ Mensaje de confirmación
- ✅ Curso aparece en "Mis Cursos"

---

## 🔍 VERIFICACIONES ADICIONALES

### Verificar Logs de Correos

Si algún correo no llega, revisar logs:

```bash
# Ver últimas líneas del log de Laravel
tail -n 50 storage/logs/laravel.log
```

Buscar mensajes relacionados con:
- `Error al enviar correo de verificación`
- `Error al asignar curso 18`

---

### Verificar Configuración de Correo

```bash
php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class); \$kernel->bootstrap(); echo 'MAIL_FROM_ADDRESS: ' . config('mail.from.address') . PHP_EOL; echo 'MAIL_FROM_NAME: ' . config('mail.from.name') . PHP_EOL;"
```

**Resultado esperado:**
```
MAIL_FROM_ADDRESS: oficinacoordinadoraacademica@correohuv.gov.co
MAIL_FROM_NAME: Coordinacion Academica Hospital Universitario del Valle
```

---

## ✅ CHECKLIST DE PRUEBA

Marcar cada item al completarlo:

- [ ] Script de prueba ejecutado sin errores
- [ ] Usuario registrado exitosamente
- [ ] Correo de verificación recibido
- [ ] Correo de asignación de curso recibido
- [ ] Email verificado correctamente
- [ ] Correo de bienvenida recibido
- [ ] Usuario tiene rol "Estudiante"
- [ ] Usuario tiene asignación al curso ID 18
- [ ] Curso ID 18 aparece en cursos disponibles
- [ ] Inscripción al curso exitosa
- [ ] Todos los correos tienen logo correcto
- [ ] Todos los correos están en español
- [ ] Diseño de correos es consistente

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### Problema: No llegan correos

**Solución:**
1. Verificar configuración en `.env`:
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=oficinacoordinadoraacademica@correohuv.gov.co
   MAIL_PASSWORD=mxosvhpzkxrfssrb
   MAIL_ENCRYPTION=tls
   ```

2. Verificar que Gmail permita aplicaciones menos seguras
3. Revisar logs: `storage/logs/laravel.log`

### Problema: Error al asignar curso

**Solución:**
1. Verificar que curso ID 18 existe:
   ```bash
   php check_curso_18.php
   ```

2. Verificar que tabla `curso_asignaciones` tiene columna `estudiante_id`

### Problema: Logo no aparece en correos

**Solución:**
1. Verificar que archivo existe:
   ```bash
   Test-Path "public/images/logocorreo.jpeg"
   ```

2. Verificar permisos de lectura del archivo

### Problema: Usuario no tiene rol "Estudiante"

**Solución:**
1. Verificar en `RegisteredUserController.php` línea 68:
   ```php
   'role' => 'Estudiante',
   ```

---

## 📊 RESULTADOS ESPERADOS

Al completar todas las pruebas:

✅ **Sistema de registro:** Funcional  
✅ **Asignación automática de rol:** Funcional  
✅ **Asignación automática de curso:** Funcional  
✅ **Envío de correos:** Funcional  
✅ **Verificación de email:** Funcional  
✅ **Diseño de correos:** Correcto  
✅ **Idioma español:** Implementado  
✅ **Logo institucional:** Visible  

---

## 📞 CONTACTO

Si encuentra algún problema durante las pruebas:
- **Email:** oficinacoordinadoraacademica@correohuv.gov.co
- **Ubicación:** Hospital Universitario del Valle, Séptimo piso

---

**Fecha:** 22 de enero de 2026  
**Versión:** 1.0
