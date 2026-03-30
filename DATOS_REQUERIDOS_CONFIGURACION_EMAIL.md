# Datos Requeridos para Configuración de Correos

## 📧 Correo a Utilizar
**Email**: oficinacoordinadoraacademica@correohuv.gov.co

---

## 🔐 Datos Necesarios

### 1. Contraseña de Aplicación de Gmail
**IMPORTANTE**: NO uses la contraseña normal de Gmail. Debes generar una "Contraseña de aplicación".

#### Pasos para Generar Contraseña de Aplicación:

1. **Ir a la configuración de seguridad de Google**:
   - URL: https://myaccount.google.com/security
   - Iniciar sesión con: oficinacoordinadoraacademica@correohuv.gov.co

2. **Activar Verificación en 2 pasos** (si no está activa):
   - Buscar "Verificación en 2 pasos"
   - Seguir los pasos para activarla
   - Esto es OBLIGATORIO para generar contraseñas de aplicación

3. **Generar Contraseña de Aplicación**:
   - Buscar "Contraseñas de aplicaciones" en la página de seguridad
   - Seleccionar "Correo" como aplicación
   - Seleccionar "Otro (nombre personalizado)" como dispositivo
   - Escribir: "Laravel SHC"
   - Clic en "Generar"
   - **Copiar el código de 16 caracteres** (formato: xxxx xxxx xxxx xxxx)

**Ejemplo de contraseña de aplicación**: `abcd efgh ijkl mnop`

---

### 2. Nombre de la Institución
Para mostrar en los correos electrónicos.

**Sugerencia**: Hospital Universitario del Valle - HUV

Por favor confirmar el nombre oficial completo.

---

### 3. URL de la Aplicación
Para generar enlaces en los correos (verificación, recuperación de contraseña, etc.)

**Opciones**:
- Producción: `https://tudominio.com`
- Desarrollo local: `http://127.0.0.1:8000`
- Otro: `_________________`

Por favor indicar la URL correcta.

---

### 4. Información Adicional (Opcional)

#### Logo de la Institución
- ¿Tienen un logo en formato PNG o JPG?
- Ruta del logo: `_________________`

#### Colores Corporativos
Ya identificados:
- Primario: #2c4370
- Secundario: #1e2f4d

#### Información de Contacto
Para incluir en el pie de los correos:
- Teléfono: `_________________`
- Dirección: `_________________`
- Sitio web: `_________________`

---

## 📋 Checklist de Datos

- [ ] Contraseña de aplicación de Gmail generada (16 caracteres)
- [ ] Nombre oficial de la institución confirmado
- [ ] URL de la aplicación confirmada
- [ ] Logo disponible (opcional)
- [ ] Información de contacto (opcional)

---

## 🚀 Una vez tengas estos datos

Proporcióname:

```
CONTRASEÑA_APLICACION: xxxx xxxx xxxx xxxx
NOMBRE_INSTITUCION: Hospital Universitario del Valle
URL_APLICACION: https://tudominio.com
TELEFONO: (opcional)
DIRECCION: (opcional)
SITIO_WEB: (opcional)
```

Con estos datos podré configurar completamente el sistema de correos.

---

## 📧 Correos que se Enviarán

Una vez configurado, el sistema enviará correos para:

1. ✅ **Verificación de cuenta** (al registrarse)
2. ✅ **Recuperación de contraseña** (olvidé mi contraseña)
3. ✅ **Inscripción a curso** (cuando se inscriben)
4. ✅ **Asignación de curso** (cuando les asignan un curso)
5. ✅ **Bienvenida** (después de verificar email)

---

## ⚠️ Notas Importantes

1. **Seguridad**: La contraseña de aplicación es diferente a tu contraseña de Gmail normal
2. **Verificación en 2 pasos**: Es OBLIGATORIA para generar contraseñas de aplicación
3. **No compartir**: La contraseña de aplicación debe mantenerse segura
4. **Revocación**: Puedes revocar la contraseña de aplicación en cualquier momento desde Google

---

## 🔧 Configuración Técnica (Automática)

Una vez proporciones los datos, configuraré automáticamente:

- Archivo `.env` con credenciales de Gmail
- Mailables (clases de correo) para cada tipo de email
- Vistas de correo con diseño profesional
- Controladores para envío automático
- Notificaciones de Laravel
- Colas de correo (opcional, para mejor rendimiento)

---

**Fecha**: 21 de enero de 2026
**Estado**: ⏳ Esperando datos del usuario
