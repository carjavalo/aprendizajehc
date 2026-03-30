# Instrucciones de Uso - Chat Interno Institucional

## Acceso al Chat

El widget de chat está ubicado en el **Dashboard** principal de la plataforma, reemplazando el antiguo recuadro "Explora nuestros cursos".

**URL de acceso:** `http://192.168.2.200:8001/dashboard`

## Ubicación del Widget

El widget "Canal de Comunicación" se encuentra en la sección CTA (Call To Action), debajo de los cursos destacados, junto a la sección "¿Quieres inscribirte en un curso?".

## Funcionalidades Disponibles

### 1. Búsqueda de Usuarios

- **Campo:** "Buscar Usuario"
- **Funcionamiento:**
  - Escribe al menos 2 caracteres del nombre o email
  - Los resultados aparecen automáticamente después de 300ms
  - Muestra máximo 10 usuarios
  - Cada resultado muestra: Avatar, Nombre completo, Rol y Email

**Usuarios visibles según tu rol:**
- **Docentes:** Ven estudiantes de sus cursos activos + Operadores
- **Estudiantes:** Ven Operadores + Docentes y estudiantes del mismo curso
- **Admin/Operador:** Ven todos los usuarios del sistema

### 2. Mensajes Individuales

**Pasos:**
1. Busca el usuario destinatario
2. Haz clic en el resultado de búsqueda
3. El usuario se selecciona automáticamente
4. Escribe tu mensaje (máx 4000 caracteres)
5. Haz clic en "Enviar Mensaje"

**Nota:** Solo puedes seleccionar un usuario a la vez para mensajes individuales.

### 3. Difusión Masiva (Mensajes Grupales)

**IMPORTANTE:** Esta funcionalidad está disponible SOLO para:
- ✅ Docentes
- ✅ Operadores
- ✅ Administradores
- ✅ Super Administradores
- ❌ **NO disponible para Estudiantes**

**Pasos:**
1. Activa el toggle "Difusión masiva"
2. Selecciona un grupo del desplegable:
   - **Todos los usuarios** (Solo Admin/Operador)
   - **Estudiantes** (Docentes ven solo sus estudiantes)
   - **Docentes** (Solo Admin/Operador)
   - **Operadores**
   - **Mis cursos** (Solo Docentes: estudiantes inscritos en sus cursos)
3. Escribe tu mensaje
4. Haz clic en "Enviar Mensaje"

**Nota:** Al activar difusión masiva, la búsqueda individual se deshabilita automáticamente.

### 4. Editor de Mensaje

**Características:**
- Área de texto con máximo 4000 caracteres
- Contador de caracteres en tiempo real
- Toolbar con botones (decorativos por ahora):
  - Negrita
  - Cursiva
  - Enlace
  - Emoji
  - Adjuntar archivo

**Validaciones:**
- El botón "Enviar Mensaje" se habilita solo cuando:
  - Hay texto en el mensaje
  - Hay al menos un destinatario seleccionado (individual o grupo)

### 5. Información de Destinatarios

En la parte inferior del widget se muestra:
- **Contador:** Número de destinatarios seleccionados
- **Texto descriptivo:**
  - "Ninguno seleccionado" (sin destinatarios)
  - Nombre del usuario (mensaje individual)
  - "X usuarios seleccionados" (difusión masiva)

## Restricciones y Permisos

### Restricciones para Estudiantes

❌ **No pueden enviar mensajes cuando:**
- Están realizando un Quiz activo
- Están realizando una Evaluación activa

El sistema verifica automáticamente si hay evaluaciones activas y bloquea el envío con un mensaje de error.

### Permisos por Rol

#### Docentes
✅ Pueden enviar a:
- Estudiantes de sus cursos inscritos y activos
- Operadores

✅ Difusión masiva a:
- Estudiantes (solo de sus cursos)
- Operadores
- Mis cursos (estudiantes inscritos)

❌ No pueden:
- Enviar a estudiantes de otros cursos
- Enviar a "Todos los usuarios"

#### Estudiantes
✅ Pueden enviar a:
- Operadores
- Docentes de sus cursos
- Compañeros del mismo curso

❌ No pueden:
- Enviar a estudiantes de otros cursos
- Enviar durante evaluaciones activas
- **Usar difusión masiva (solo mensajes individuales)**

#### Operadores
✅ Pueden enviar a:
- Todos los usuarios del sistema

✅ Difusión masiva a:
- Todos los grupos disponibles

#### Admin/Super Admin
✅ Pueden enviar a:
- Todos los usuarios del sistema

✅ Difusión masiva a:
- Todos los grupos disponibles

## Mensajes de Feedback

### Éxito
- **Mensaje individual:** "Mensaje enviado exitosamente a 1 destinatario(s)"
- **Mensaje grupal:** "Mensaje enviado exitosamente a X destinatario(s)"
- Aparece como alerta verde con ícono de éxito
- Se cierra automáticamente después de 2 segundos

### Errores Comunes

1. **"Mensaje vacío"**
   - Causa: Intentaste enviar sin escribir nada
   - Solución: Escribe un mensaje antes de enviar

2. **"Grupo no seleccionado"**
   - Causa: Activaste difusión masiva pero no seleccionaste un grupo
   - Solución: Selecciona un grupo del desplegable

3. **"Sin destinatarios"**
   - Causa: No seleccionaste ningún usuario
   - Solución: Busca y selecciona un usuario

4. **"No puedes enviar mensajes mientras estás en una evaluación activa"**
   - Causa: Eres estudiante y hay un quiz/evaluación activa
   - Solución: Espera a terminar la evaluación

5. **"No tienes permiso para enviar mensajes a este usuario"**
   - Causa: Intentaste enviar a un usuario fuera de tu alcance
   - Solución: Verifica los permisos de tu rol

## Limpieza Automática

Después de enviar exitosamente un mensaje, el formulario se limpia automáticamente:
- ✓ Campo de mensaje vacío
- ✓ Contador de caracteres en 0
- ✓ Usuarios seleccionados eliminados
- ✓ Toggle de difusión desactivado
- ✓ Grupo deseleccionado
- ✓ Búsqueda habilitada nuevamente

## Estados del Botón de Envío

1. **Deshabilitado (gris):**
   - Falta mensaje o destinatarios
   - No se puede hacer clic

2. **Habilitado (azul):**
   - Todo listo para enviar
   - Hover cambia a azul más oscuro

3. **Enviando (azul con spinner):**
   - Mensaje en proceso de envío
   - Botón deshabilitado temporalmente
   - Muestra ícono giratorio

## Consejos de Uso

### Para Docentes
- Usa "Mis cursos" para enviar anuncios a todos tus estudiantes
- Busca estudiantes específicos para mensajes personalizados
- Contacta a Operadores para soporte técnico

### Para Estudiantes
- Contacta a Operadores para dudas administrativas
- Envía mensajes a tus docentes para consultas académicas
- Coordina con compañeros del mismo curso
- **Nota:** Solo puedes enviar mensajes individuales, no difusión masiva

### Para Operadores/Admin
- Usa "Todos los usuarios" para anuncios generales
- Filtra por rol para comunicaciones específicas
- Monitorea el uso del chat para soporte

## Solución de Problemas

### El widget no aparece
- Verifica que estés en el Dashboard (`/dashboard`)
- Asegúrate de estar autenticado
- Refresca la página (Ctrl + F5)

### No aparecen resultados al buscar
- Verifica que escribiste al menos 2 caracteres
- Espera 300ms para que aparezcan los resultados
- Verifica que existan usuarios con ese nombre/email
- Confirma que tienes permisos para ver esos usuarios

### El botón de envío está deshabilitado
- Verifica que escribiste un mensaje
- Confirma que seleccionaste un destinatario o grupo
- Revisa que no estés en una evaluación activa (estudiantes)

### Error al enviar mensaje
- Verifica tu conexión a internet
- Confirma que la sesión no haya expirado
- Revisa que el destinatario aún exista en el sistema
- Contacta al administrador si el problema persiste

## Soporte Técnico

Si encuentras problemas no listados aquí:
1. Contacta a un Operador a través del chat
2. Envía un email al administrador del sistema
3. Reporta el error con detalles específicos:
   - Tu rol de usuario
   - Acción que intentabas realizar
   - Mensaje de error recibido
   - Navegador y versión utilizada

## Actualizaciones Futuras

Funcionalidades planeadas:
- Notificaciones en tiempo real
- Historial de conversaciones
- Marcar mensajes como leídos
- Envío de archivos adjuntos
- Formato de texto enriquecido
- Emojis funcionales
- Búsqueda en historial

---

**Versión:** 1.0.0  
**Última actualización:** 21 de Enero de 2026  
**Estado:** Producción
