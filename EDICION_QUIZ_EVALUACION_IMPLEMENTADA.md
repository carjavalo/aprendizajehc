# Edición de Quiz y Evaluaciones - Ya Implementado

## Fecha: 19 de Enero de 2026

## Estado: ✅ COMPLETAMENTE IMPLEMENTADO

La funcionalidad de edición de quiz y evaluaciones ya está completamente implementada en el sistema. Al hacer click en "Editar" en un quiz o evaluación, se abre el mismo modal de creación con todos los datos precargados.

## Ubicación del Código

**Vista:** `resources/views/admin/capacitaciones/cursos/classroom/actividades.blade.php`

## Funcionalidades Implementadas

### 1. Botón de Editar (Línea 162-167)
```php
<button type="button" class="btn btn-info btn-sm btn-editar-actividad" 
        data-actividad-id="{{ $actividad->id }}"
        data-actividad="{{ json_encode($actividad) }}">
    <i class="fas fa-edit"></i> Editar
</button>
```

### 2. Event Handler (Línea 436-442)
```javascript
$(document).on('click', '.btn-editar-actividad', function() {
    const actividadId = $(this).data('actividad-id');
    const actividad = $(this).data('actividad');
    
    // Abrir modal de edición completo
    editarActividadCompleta(actividadId, actividad);
});
```

### 3. Función Principal: `editarActividadCompleta()` (Línea 779-1177)

**Características:**
- Detecta automáticamente el tipo de actividad (tarea, quiz, evaluación, proyecto)
- Carga todos los campos del formulario con los datos existentes
- Para quiz/evaluación:
  - Carga todas las preguntas existentes con sus opciones
  - Marca las respuestas correctas
  - Muestra la barra de progreso de puntos (0-5.0)
  - Permite agregar/eliminar preguntas
  - Permite agregar/eliminar opciones por pregunta
- Carga prerrequisitos de actividades
- Carga material asociado
- Carga configuración de calificación (porcentaje, nota mínima)

**Campos que se precargan:**
- Título
- Descripción
- Instrucciones
- Fecha de apertura
- Fecha de cierre
- Puntos máximos
- Intentos permitidos
- Material asociado
- Porcentaje del curso
- Nota mínima de aprobación
- Prerrequisitos de actividades
- **Para Quiz/Evaluación:**
  - Duración en minutos
  - Todas las preguntas con sus textos
  - Puntos por pregunta
  - Opciones de cada pregunta (A, B, C, D, etc.)
  - Respuestas correctas marcadas

### 4. Función: `loadEditQuestion()` (Línea 1191-1238)

**Funcionalidad:**
- Carga una pregunta existente en el modal de edición
- Escapa caracteres especiales para evitar problemas con HTML
- Crea el HTML de la pregunta con todos sus campos
- Carga las opciones de respuesta
- Marca las respuestas correctas con checkbox

**Proceso:**
1. Incrementa el contador de preguntas
2. Crea el contenedor de la pregunta
3. Precarga el texto de la pregunta
4. Precarga los puntos asignados
5. Itera sobre las opciones existentes
6. Llama a `addEditQuestionOptionWithData()` para cada opción
7. Marca las opciones correctas

### 5. Función: `addEditQuestion()` (Línea 1240-1276)

**Funcionalidad:**
- Agrega una nueva pregunta vacía al quiz en edición
- Crea 2 opciones por defecto (A y B)
- Asigna 1.0 punto por defecto
- Actualiza la barra de progreso de puntos

### 6. Función: `removeEditQuestion()` (Línea 1278-1293)

**Funcionalidad:**
- Elimina una pregunta del quiz
- Renumera las preguntas restantes
- Actualiza la barra de progreso de puntos

### 7. Función: `addEditQuestionOptionWithData()` (Línea 1295-1318)

**Funcionalidad:**
- Agrega una opción de respuesta con datos precargados
- Escapa caracteres especiales
- Marca el checkbox si es respuesta correcta
- Asigna la letra correspondiente (A, B, C, etc.)

### 8. Función: `addEditQuestionOption()` (Línea 1320-1341)

**Funcionalidad:**
- Agrega una nueva opción vacía a una pregunta
- Limita a máximo 10 opciones por pregunta
- Asigna automáticamente la siguiente letra disponible

### 9. Función: `removeEditQuestionOption()` (Línea 1343-1352)

**Funcionalidad:**
- Elimina una opción de respuesta
- Mantiene mínimo 2 opciones por pregunta
- Renumera las opciones restantes

### 10. Función: `actualizarActividadCompleta()` (Línea 1378-1427)

**Funcionalidad:**
- Envía los datos actualizados al servidor
- Serializa el contenido_json correctamente
- Serializa los prerequisite_activity_ids
- Muestra loading durante el proceso
- Recarga la pestaña de actividades al éxito

**Endpoint:** `PUT /capacitaciones/cursos/{curso}/classroom/actividades/{actividad}/actualizar`

## Validaciones Implementadas

### Validaciones Generales:
- ✅ Título requerido
- ✅ Material requerido
- ✅ Porcentaje entre 0-100%
- ✅ Nota mínima entre 0.0-5.0

### Validaciones para Quiz/Evaluación:
- ✅ Mínimo 1 pregunta
- ✅ Todas las preguntas deben tener texto
- ✅ Puntos por pregunta entre 0-5
- ✅ Suma total de puntos no puede exceder 5.0
- ✅ Mínimo 2 opciones por pregunta
- ✅ Todas las opciones deben tener texto
- ✅ Al menos 1 respuesta correcta por pregunta

## Características Especiales

### Barra de Progreso de Puntos:
- Muestra visualmente cuántos puntos se han asignado (0-5.0)
- Color verde cuando está dentro del límite
- Color rojo cuando excede 5.0
- Deshabilita el botón "Agregar Pregunta" cuando se alcanza 5.0

### Prerrequisitos de Actividades:
- Muestra checkboxes de todas las actividades del curso
- Excluye la actividad actual
- Precarga los prerrequisitos existentes
- Permite seleccionar múltiples prerrequisitos

### Material Asociado:
- Dropdown con todos los materiales disponibles
- Muestra el porcentaje del curso de cada material
- Actualiza dinámicamente la información de porcentaje disponible

### Manejo de Caracteres Especiales:
- Escapa comillas dobles y simples en textos
- Previene problemas con HTML injection
- Mantiene la integridad de los datos

## Flujo de Edición

1. **Usuario hace click en "Editar"**
   - Se captura el ID de la actividad
   - Se obtiene el objeto completo de la actividad desde el data attribute

2. **Se abre el modal con SweetAlert2**
   - Título dinámico según tipo de actividad
   - Ancho de 900px para mejor visualización
   - Scroll interno para contenido largo

3. **Se inicializan las variables globales**
   - `window.editQuestions = []`
   - `window.editQuestionCounter = 0`
   - `window.editOptionCounters = {}`

4. **Se precargan todos los campos**
   - Campos básicos (título, descripción, etc.)
   - Fechas en formato datetime-local
   - Material seleccionado
   - Prerrequisitos marcados

5. **Para Quiz/Evaluación: Se cargan las preguntas**
   - Se itera sobre `actividad.contenido_json.questions`
   - Se llama a `loadEditQuestion()` para cada pregunta
   - Se actualizan los puntos totales

6. **Usuario realiza cambios**
   - Puede editar cualquier campo
   - Puede agregar/eliminar preguntas
   - Puede agregar/eliminar opciones
   - Validaciones en tiempo real

7. **Usuario hace click en "Guardar Cambios"**
   - Se ejecuta `preConfirm`
   - Se validan todos los campos
   - Se construye el objeto de datos
   - Se serializa contenido_json

8. **Se envía al servidor**
   - Método PUT
   - Se muestra loading
   - Al éxito: mensaje y recarga de pestaña
   - Al error: mensaje de error

## Endpoint del Controlador

**Ruta:** `PUT /capacitaciones/cursos/{curso}/classroom/actividades/{actividad}/actualizar`

**Controlador:** `CursoClassroomController@actualizarActividad`

**Datos enviados:**
```javascript
{
    _token: string,
    titulo: string,
    descripcion: string,
    instrucciones: string,
    fecha_apertura: datetime,
    fecha_cierre: datetime,
    puntos_maximos: int,
    intentos_permitidos: int,
    material_id: int,
    porcentaje_curso: float,
    nota_minima_aprobacion: float,
    prerequisite_activity_ids: JSON string,
    contenido_json: JSON string (solo para quiz/evaluación)
}
```

## Formato del contenido_json

```json
{
    "duration": 30,
    "questions": [
        {
            "id": 1,
            "text": "¿Pregunta 1?",
            "points": 1.5,
            "options": {
                "A": "Opción A",
                "B": "Opción B",
                "C": "Opción C",
                "D": "Opción D"
            },
            "correctAnswers": ["A", "C"],
            "isMultipleChoice": true
        }
    ],
    "totalPoints": 5.0
}
```

## Notas Técnicas

- El modal usa SweetAlert2 para mejor UX
- Los datos se pasan mediante data attributes en el botón
- Se usa jQuery para manejo de eventos
- Las preguntas se identifican con IDs únicos incrementales
- Las opciones usan letras (A-J) para identificación
- El sistema soporta hasta 10 opciones por pregunta
- Se permite respuesta única o múltiple por pregunta

## Conclusión

✅ **La funcionalidad está 100% implementada y funcional**

No se requieren cambios adicionales. El sistema ya permite:
- Editar quiz y evaluaciones
- Modificar todas las preguntas
- Agregar/eliminar preguntas y opciones
- Cambiar respuestas correctas
- Actualizar configuración de calificación
- Guardar cambios en la base de datos

El usuario solo necesita hacer click en el botón "Editar" de cualquier quiz o evaluación para acceder a todas estas funcionalidades.
