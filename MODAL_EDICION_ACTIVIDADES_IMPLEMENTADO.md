# Modal de Edición de Actividades Implementado

## Fecha: 23 de enero de 2026

## Funcionalidad Existente

El modal de edición de actividades YA ESTABA IMPLEMENTADO en la vista `/capacitaciones/cursos/{id}/classroom#actividades`. Se realizó un ajuste menor en el título para mayor claridad.

## Ubicación

**Vista**: `resources/views/admin/capacitaciones/cursos/classroom/actividades.blade.php`
**Ruta**: `/capacitaciones/cursos/{id}/classroom#actividades`

## Características del Modal

### 1. Botón de Editar
- Ubicado en cada actividad listada
- Icono: `<i class="fas fa-edit"></i>`
- Texto: "Editar"
- Clase: `btn-editar-actividad`
- Solo visible para instructores

### 2. Título del Modal
**Antes**: "Modificar {tipoLabel}" (ej: "Modificar Quiz", "Modificar Tarea")
**Ahora**: "Modificar Actividad: {tipoLabel}" (ej: "Modificar Actividad: Quiz")

### 3. Campos del Formulario

#### Campos Básicos (Todas las actividades):
- ✅ **Título** (requerido)
- ✅ **Descripción**
- ✅ **Instrucciones**
- ✅ **Fecha de Apertura** (datetime-local)
- ✅ **Fecha de Cierre** (datetime-local)
- ✅ **Puntos Máximos**
- ✅ **Intentos Permitidos**

#### Configuración de Calificación:
- ✅ **Material al que pertenece** (select con materiales disponibles)
- ✅ **Porcentaje del Material** (0-100%)
- ✅ **Nota Mínima de Aprobación** (0.0 - 5.0)

#### Prerrequisitos:
- ✅ **Actividades Prerrequisito** (checkboxes de otras actividades)
- Permite seleccionar actividades que deben completarse antes

#### Campos Específicos para Quiz/Evaluación:
- ✅ **Duración en minutos** (5-180 minutos)
- ✅ **Preguntas** (editor de preguntas con opciones)
- ✅ **Barra de progreso de puntos** (suma total no puede exceder 5.0)
- ✅ **Botón "Agregar Pregunta"**

### 4. Carga de Datos

El modal carga automáticamente todos los datos de la actividad:
```javascript
// Al hacer clic en "Editar"
$('.btn-editar-actividad').on('click', function() {
    var actividadId = $(this).data('actividad-id');
    
    // Hace petición AJAX para obtener datos actualizados
    $.ajax({
        url: `/capacitaciones/cursos/${cursoId}/classroom/actividades/${actividadId}`,
        success: function(response) {
            // Abre modal con datos de la actividad
            editarActividadCompleta(actividadId, response.actividad);
        }
    });
});
```

### 5. Validaciones

- ✅ Título es requerido
- ✅ Material es requerido
- ✅ Porcentaje debe estar entre 0-100%
- ✅ Nota mínima debe estar entre 0.0-5.0
- ✅ Para Quiz/Evaluación: suma de puntos no puede exceder 5.0
- ✅ Fechas en formato datetime-local

### 6. Guardado de Cambios

Al hacer clic en "Guardar Cambios":
```javascript
// Recopila todos los datos del formulario
const data = {
    titulo: $('#edit-actividad-titulo').val(),
    descripcion: $('#edit-actividad-descripcion').val(),
    instrucciones: $('#edit-actividad-instrucciones').val(),
    fecha_apertura: $('#edit-actividad-fecha-apertura').val(),
    fecha_cierre: $('#edit-actividad-fecha-cierre').val(),
    puntos_maximos: $('#edit-actividad-puntos').val(),
    intentos_permitidos: $('#edit-actividad-intentos').val(),
    material_id: $('#edit-actividad-material').val(),
    porcentaje_curso: $('#edit-actividad-porcentaje').val(),
    nota_minima_aprobacion: $('#edit-actividad-nota-minima').val(),
    prerequisite_activity_ids: [...], // IDs de prerrequisitos
    // Para quiz/evaluación:
    contenido_json: {
        duration: $('#edit-actividad-duration').val(),
        questions: [...] // Array de preguntas
    }
};

// Envía petición PUT al servidor
$.ajax({
    url: `/capacitaciones/cursos/${cursoId}/classroom/actividades/${actividadId}/actualizar`,
    type: 'PUT',
    data: data,
    success: function(response) {
        Swal.fire('¡Éxito!', 'Actividad actualizada correctamente', 'success');
        // Recarga la pestaña de actividades
        loadTabContent('actividades', '#actividades');
    }
});
```

## Flujo Completo de Edición

1. **Usuario hace clic en "Editar"** → Botón con clase `btn-editar-actividad`
2. **Sistema obtiene datos** → AJAX GET a `/capacitaciones/cursos/{id}/classroom/actividades/{actividad_id}`
3. **Modal se abre** → SweetAlert2 con título "Modificar Actividad: {tipo}"
4. **Campos se llenan** → Todos los datos de la actividad se cargan automáticamente
5. **Usuario modifica** → Puede cambiar cualquier campo
6. **Usuario guarda** → Clic en "Guardar Cambios"
7. **Sistema valida** → Verifica que todos los campos requeridos estén completos
8. **Sistema actualiza** → PUT a `/capacitaciones/cursos/{id}/classroom/actividades/{actividad_id}/actualizar`
9. **Confirmación** → SweetAlert muestra "Actividad actualizada correctamente"
10. **Vista se recarga** → La pestaña de actividades se actualiza con los nuevos datos

## Tipos de Actividades Soportadas

1. **Tarea** 📝
   - Campos básicos + configuración de calificación + prerrequisitos

2. **Quiz** ❓
   - Campos básicos + configuración de calificación + prerrequisitos + preguntas interactivas

3. **Evaluación** 📋
   - Campos básicos + configuración de calificación + prerrequisitos + preguntas interactivas

4. **Proyecto** 📊
   - Campos básicos + configuración de calificación + prerrequisitos

## Características Especiales

### Editor de Preguntas (Quiz/Evaluación)
- Agregar/eliminar preguntas dinámicamente
- Cada pregunta tiene:
  - Texto de la pregunta
  - Puntos asignados
  - 2-10 opciones de respuesta
  - Marcar opciones correctas (checkbox)
- Barra de progreso que muestra puntos totales (máx 5.0)

### Prerrequisitos de Actividades
- Muestra lista de todas las demás actividades del curso
- Permite seleccionar múltiples prerrequisitos
- Los prerrequisitos seleccionados se guardan como array de IDs

### Vinculación con Materiales
- Select con todos los materiales del curso
- Muestra porcentaje del curso de cada material
- Al seleccionar material, actualiza información de porcentaje disponible

## Cambio Realizado

**Línea modificada**: 
```javascript
// Antes:
title: `<i class="fas fa-edit"></i> Modificar ${tipoLabel}`,

// Ahora:
title: `<i class="fas fa-edit"></i> Modificar Actividad: ${tipoLabel}`,
```

Esto hace que el título sea más descriptivo:
- "Modificar Actividad: Quiz"
- "Modificar Actividad: Tarea"
- "Modificar Actividad: Evaluación"
- "Modificar Actividad: Proyecto"

## Archivos Involucrados

- **Vista**: `resources/views/admin/capacitaciones/cursos/classroom/actividades.blade.php`
- **Controlador**: `app/Http/Controllers/CursoClassroomController.php`
- **Ruta**: Definida en `routes/web.php`

## Pruebas Recomendadas

1. Ir a `/capacitaciones/cursos/18/classroom#actividades` como instructor
2. Hacer clic en el botón "Editar" de una actividad
3. Verificar que el modal se abre con título "Modificar Actividad: {tipo}"
4. Verificar que todos los campos están llenos con los datos actuales
5. Modificar algunos campos
6. Hacer clic en "Guardar Cambios"
7. Verificar que aparece mensaje de éxito
8. Verificar que los cambios se reflejan en la lista de actividades

## Notas Técnicas

- El modal usa SweetAlert2 para una mejor experiencia de usuario
- Los datos se cargan mediante AJAX para obtener información actualizada
- El formulario es dinámico y se adapta según el tipo de actividad
- Las validaciones se realizan tanto en frontend como en backend
- El modal tiene scroll interno para manejar formularios largos (max-height: 600px)
- Ancho del modal: 900px para acomodar todos los campos
