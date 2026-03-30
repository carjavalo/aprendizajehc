# Ejemplos de Uso - Chat Interno Institucional

## Escenarios Comunes de Uso

### Escenario 1: Docente envía anuncio a todos sus estudiantes

**Contexto:** El docente necesita informar sobre un cambio de horario a todos los estudiantes de sus cursos.

**Pasos:**
1. Acceder al Dashboard
2. Localizar el widget "Canal de Comunicación"
3. Activar el toggle "Difusión masiva"
4. Seleccionar "Mis cursos (estudiantes inscritos)" del desplegable
5. Escribir el mensaje:
   ```
   Estimados estudiantes,
   
   Les informo que la clase del próximo martes 23 de enero se realizará 
   a las 10:00 AM en lugar de las 8:00 AM.
   
   Saludos cordiales,
   Prof. Juan Pérez
   ```
6. Hacer clic en "Enviar Mensaje"
7. Confirmar el mensaje de éxito: "Mensaje enviado exitosamente a 25 destinatario(s)"

**Resultado:** Todos los estudiantes inscritos en los cursos del docente reciben el mensaje.

---

### Escenario 2: Estudiante consulta a su docente

**Contexto:** Un estudiante tiene dudas sobre una tarea y necesita contactar a su docente.

**Pasos:**
1. Acceder al Dashboard
2. En el campo "Buscar Usuario", escribir: "Juan Pérez"
3. Esperar a que aparezcan los resultados
4. Hacer clic en "Prof. Juan Pérez - Docente"
5. Escribir el mensaje:
   ```
   Profesor,
   
   Tengo una duda sobre la tarea de la semana 3. ¿Podría explicarme 
   nuevamente el punto 5?
   
   Gracias,
   María González
   ```
6. Hacer clic en "Enviar Mensaje"
7. Confirmar el mensaje de éxito

**Resultado:** El docente recibe el mensaje individual del estudiante.

---

### Escenario 3: Operador envía comunicado general

**Contexto:** El operador necesita informar sobre mantenimiento del sistema a todos los usuarios.

**Pasos:**
1. Acceder al Dashboard
2. Activar el toggle "Difusión masiva"
3. Seleccionar "Todos los usuarios" del desplegable
4. Escribir el mensaje:
   ```
   AVISO IMPORTANTE
   
   El sistema estará en mantenimiento el sábado 27 de enero 
   de 2:00 AM a 6:00 AM.
   
   Durante este período no podrán acceder a la plataforma.
   
   Disculpen las molestias.
   
   Equipo de Soporte Técnico
   ```
5. Hacer clic en "Enviar Mensaje"
6. Confirmar el mensaje de éxito con el total de destinatarios

**Resultado:** Todos los usuarios del sistema reciben el comunicado.

---

### Escenario 4: Estudiante intenta enviar mensaje durante evaluación

**Contexto:** Un estudiante está realizando un quiz y quiere enviar un mensaje.

**Pasos:**
1. Estudiante está en un quiz activo
2. Accede al Dashboard en otra pestaña
3. Intenta buscar un usuario y escribir un mensaje
4. Hace clic en "Enviar Mensaje"

**Resultado:** 
- ❌ Aparece error: "No puedes enviar mensajes mientras estás en una evaluación activa"
- El mensaje no se envía
- El estudiante debe terminar la evaluación primero

---

### Escenario 5: Estudiante contacta a Operador para soporte

**Contexto:** Un estudiante no puede acceder a un material del curso.

**Pasos:**
1. Acceder al Dashboard
2. En el campo "Buscar Usuario", escribir: "soporte" o "operador"
3. Seleccionar al operador de la lista
4. Escribir el mensaje:
   ```
   Hola,
   
   No puedo descargar el PDF de la Semana 2 del curso de Anatomía.
   Me aparece un error 404.
   
   ¿Pueden ayudarme?
   
   Gracias,
   Carlos Ramírez
   Estudiante ID: 36
   ```
5. Hacer clic en "Enviar Mensaje"

**Resultado:** El operador recibe el reporte y puede dar soporte.

---

### Escenario 6: Docente envía mensaje solo a estudiantes

**Contexto:** El docente quiere enviar un recordatorio solo a los estudiantes (no a operadores).

**Pasos:**
1. Acceder al Dashboard
2. Activar el toggle "Difusión masiva"
3. Seleccionar "Estudiantes" del desplegable
4. Escribir el mensaje:
   ```
   Recordatorio:
   
   La entrega del proyecto final es el viernes 26 de enero a las 11:59 PM.
   
   No se aceptarán entregas tardías.
   
   Prof. Ana Martínez
   ```
5. Hacer clic en "Enviar Mensaje"

**Resultado:** Solo los estudiantes de los cursos del docente reciben el mensaje.

---

### Escenario 7: Estudiante coordina con compañero de curso

**Contexto:** Dos estudiantes del mismo curso quieren coordinar un trabajo en grupo.

**Pasos:**
1. Estudiante A accede al Dashboard
2. Busca a "Pedro López" (compañero del mismo curso)
3. Selecciona a Pedro de los resultados
4. Escribe el mensaje:
   ```
   Hola Pedro,
   
   ¿Podemos reunirnos mañana a las 3 PM para trabajar en el proyecto?
   
   Saludos,
   Laura
   ```
5. Hace clic en "Enviar Mensaje"

**Resultado:** Pedro recibe el mensaje de coordinación.

---

### Escenario 8: Admin envía mensaje a todos los docentes

**Contexto:** El administrador necesita convocar a una reunión de docentes.

**Pasos:**
1. Acceder al Dashboard
2. Activar el toggle "Difusión masiva"
3. Seleccionar "Docentes" del desplegable
4. Escribir el mensaje:
   ```
   CONVOCATORIA - REUNIÓN DE DOCENTES
   
   Fecha: Lunes 29 de enero
   Hora: 4:00 PM
   Lugar: Sala de Conferencias
   
   Temas a tratar:
   - Planificación del nuevo semestre
   - Actualización de contenidos
   - Evaluación de resultados
   
   Confirmen su asistencia.
   
   Dirección Académica
   ```
5. Hacer clic en "Enviar Mensaje"

**Resultado:** Todos los docentes del sistema reciben la convocatoria.

---

### Escenario 9: Estudiante intenta enviar a usuario no permitido

**Contexto:** Un estudiante intenta enviar mensaje a un estudiante de otro curso.

**Pasos:**
1. Estudiante del Curso A busca a un estudiante del Curso B
2. El estudiante del Curso B NO aparece en los resultados de búsqueda

**Resultado:** 
- El sistema automáticamente filtra usuarios no permitidos
- Solo aparecen usuarios con los que el estudiante puede comunicarse
- No es posible enviar mensajes a usuarios fuera del alcance

---

### Escenario 10: Uso del contador de caracteres

**Contexto:** Un usuario quiere enviar un mensaje largo.

**Pasos:**
1. Acceder al Dashboard
2. Seleccionar un destinatario
3. Comenzar a escribir en el campo de mensaje
4. Observar el contador: "0 / 4000"
5. Mientras escribe, el contador se actualiza: "523 / 4000"
6. Si intenta escribir más de 4000 caracteres, el campo no permite más texto

**Resultado:** 
- El usuario sabe cuántos caracteres ha escrito
- No puede exceder el límite de 4000 caracteres
- El sistema previene mensajes demasiado largos

---

## Casos de Error y Soluciones

### Error 1: "Mensaje vacío"

**Causa:** Intentó enviar sin escribir nada.

**Solución:**
```
1. Escribir un mensaje en el campo de texto
2. Verificar que el contador muestre más de 0 caracteres
3. Intentar enviar nuevamente
```

---

### Error 2: "Grupo no seleccionado"

**Causa:** Activó difusión masiva pero no seleccionó un grupo.

**Solución:**
```
1. Verificar que el toggle "Difusión masiva" esté activado
2. Abrir el desplegable "Seleccionar Grupo"
3. Elegir una opción (Estudiantes, Docentes, etc.)
4. Intentar enviar nuevamente
```

---

### Error 3: "Sin destinatarios"

**Causa:** No seleccionó ningún usuario en modo individual.

**Solución:**
```
1. Desactivar "Difusión masiva" si está activa
2. Buscar un usuario en el campo "Buscar Usuario"
3. Hacer clic en un resultado de la búsqueda
4. Verificar que aparezca el nombre en "Destinatarios"
5. Intentar enviar nuevamente
```

---

### Error 4: Evaluación activa (Estudiantes)

**Causa:** Hay un quiz o evaluación activa.

**Solución:**
```
1. Terminar la evaluación actual
2. Cerrar la ventana de la evaluación
3. Esperar a que la evaluación se cierre automáticamente
4. Intentar enviar el mensaje nuevamente
```

---

### Error 5: "No tienes permiso"

**Causa:** Intentó enviar a un usuario fuera de su alcance.

**Solución:**
```
1. Verificar los permisos de tu rol
2. Buscar solo usuarios permitidos
3. Si necesitas contactar a alguien específico, hacerlo a través de un Operador
```

---

## Tips y Mejores Prácticas

### Para Docentes

✅ **Hacer:**
- Usar "Mis cursos" para anuncios generales
- Ser claro y conciso en los mensajes
- Incluir fecha y hora en convocatorias
- Firmar los mensajes con tu nombre

❌ **Evitar:**
- Enviar mensajes muy largos (usar materiales del curso en su lugar)
- Enviar múltiples mensajes seguidos (consolidar en uno)
- Usar lenguaje informal en comunicaciones oficiales

### Para Estudiantes

✅ **Hacer:**
- Ser respetuoso y formal con docentes
- Incluir tu nombre completo en mensajes importantes
- Ser específico en tus consultas
- Contactar a Operadores para problemas técnicos

❌ **Evitar:**
- Enviar mensajes durante evaluaciones
- Usar el chat para temas no académicos
- Enviar mensajes urgentes fuera de horario laboral

### Para Operadores

✅ **Hacer:**
- Responder rápidamente a consultas
- Usar "Todos los usuarios" solo para anuncios importantes
- Mantener un tono profesional y amable
- Documentar problemas reportados

❌ **Evitar:**
- Enviar spam o mensajes innecesarios
- Ignorar mensajes de usuarios
- Usar el chat para temas personales

---

## Estadísticas de Uso (Ejemplo)

Después de implementar el chat, puedes esperar:

- **Docentes:** 5-10 mensajes por semana (promedio)
- **Estudiantes:** 2-5 mensajes por semana (promedio)
- **Operadores:** 10-20 mensajes por semana (promedio)
- **Tiempo de respuesta esperado:** 24-48 horas

---

**Nota:** Estos son ejemplos ilustrativos. El uso real puede variar según las necesidades de tu institución.
