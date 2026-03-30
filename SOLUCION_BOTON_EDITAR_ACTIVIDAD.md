# Solución: Botón Editar Actividad Funcional

## Problema

El botón "Editar" en la vista de actividades (`http://192.168.2.200:8001/capacitaciones/cursos/17/classroom#actividades`) no abría ningún modal para modificar la actividad.

## Solución Implementada

Se creó una solución simplificada que:

1. **Usa los datos ya disponibles**: El botón tiene `data-actividad="{{ json_encode($actividad) }}"` con todos los datos necesarios
2. **Modal simple y funcional**: Se creó un modal de SweetAlert2 con los campos esenciales
3. **Sin AJAX innecesario**: No hace petición al servidor para obtener datos que ya tiene

### Cambios Realizados

#### 1. Event Listener Simplificado

```javascript
$(document).on('click', '.btn-editar-actividad', function(e) {
    e.preventDefault();
    const actividadId = $(this).data('actividad-id');
    const actividadData = $(this).data('actividad');
    
    if (!actividadData) {
        Swal.fire('Error', 'No se pudieron cargar los datos de la actividad', 'error');
        return;
    }
    
    // Abrir modal de edición directamente
    abrirModalEdicion(actividadId, actividadData);
});
```

#### 2. Nueva Función `abrirModalEdicion()`

Crea un modal de SweetAlert2 con los siguientes campos:

- **Título** (obligatorio)
- **Descripción**
- **Tipo** (deshabilitado, no se puede cambiar)
- **Material** (select con materiales disponibles)
- **Fecha Apertura** (datetime-local)
- **Fecha Cierre** (datetime-local)
- **Porcentaje del Material** (0-100%)
- **Nota Mínima Aprobación** (0-5.0)

**Características**:
- Modal de 800px de ancho
- Scroll vertical si el contenido es muy largo
- Validación del título obligatorio
- Pre-llenado con los datos actuales de la actividad
- Botones "Guardar Cambios" y "Cancelar"

#### 3. Nueva Función `guardarEdicionActividad()`

Envía los datos al servidor mediante AJAX:

```javascript
PUT /capacitaciones/cursos/{curso}/classroom/actividades/{actividad}/actualizar
```

**Datos enviados**:
- titulo
- descripcion
- material_id
- fecha_apertura
- fecha_cierre
- porcentaje_curso
- nota_minima_aprobacion

**Respuesta exitosa**:
- Muestra mensaje de éxito
- Recarga la pestaña de actividades automáticamente

## Campos del Modal

### Campos Editables

1. **Título** ✏️
   - Campo de texto
   - Obligatorio
   - Pre-llenado con el título actual

2. **Descripción** ✏️
   - Textarea de 3 filas
   - Opcional
   - Pre-llenado con la descripción actual

3. **Material** ✏️
   - Select con lista de materiales del curso
   - Opcional (puede ser "Sin material")
   - Pre-seleccionado el material actual

4. **Fecha Apertura** ✏️
   - Input datetime-local
   - Opcional
   - Pre-llenado con la fecha actual

5. **Fecha Cierre** ✏️
   - Input datetime-local
   - Opcional
   - Pre-llenado con la fecha actual

6. **Porcentaje del Material** ✏️
   - Input numérico (0-100, step 0.1)
   - Pre-llenado con el porcentaje actual

7. **Nota Mínima Aprobación** ✏️
   - Input numérico (0-5, step 0.1)
   - Pre-llenado con la nota mínima actual

### Campos No Editables

1. **Tipo** 🔒
   - Select deshabilitado
   - Muestra el tipo actual (Tarea, Quiz, Evaluación, Proyecto)
   - No se puede cambiar el tipo de una actividad existente

### Nota Especial para Quiz/Evaluación

Si la actividad es de tipo Quiz o Evaluación, se muestra un mensaje informativo:

```
ℹ️ Para editar las preguntas del quiz/evaluación, contacta al administrador del sistema.
```

Esto es porque las preguntas tienen una estructura compleja que requiere un editor especializado.

## Flujo de Uso

1. Usuario hace clic en "Editar" en una actividad
2. Se abre el modal con los datos actuales pre-llenados
3. Usuario modifica los campos que desea
4. Usuario hace clic en "Guardar Cambios"
5. Se valida que el título no esté vacío
6. Se envían los datos al servidor
7. Se muestra mensaje de éxito
8. Se recarga la lista de actividades automáticamente

## Archivos Modificados

1. **resources/views/admin/capacitaciones/cursos/classroom/actividades.blade.php**
   - Simplificado el event listener del botón editar
   - Agregada función `abrirModalEdicion()`
   - Agregada función `guardarEdicionActividad()`

## Ruta Utilizada

```
PUT /capacitaciones/cursos/{curso}/classroom/actividades/{actividad}/actualizar
```

Esta ruta ya existía en el sistema y está manejada por el método `actualizarActividad()` en `CursoClassroomController`.

## Validaciones

### Cliente (JavaScript)
- Título obligatorio

### Servidor (Controller)
- Título obligatorio (max 200 caracteres)
- Descripción opcional
- Fechas válidas
- Porcentaje entre 0-100
- Nota mínima entre 0-5
- Permisos del usuario (solo instructor, admin u operador)

## Instrucciones de Uso

1. **Recarga la página** con `Ctrl + F5`
2. **Ve a la pestaña "Actividades"**
3. **Haz clic en "Editar"** en cualquier actividad
4. **Modifica los campos** que desees
5. **Haz clic en "Guardar Cambios"**
6. **Verifica** que los cambios se aplicaron correctamente

## Ventajas de esta Solución

✅ **Simple**: No requiere peticiones AJAX adicionales para obtener datos
✅ **Rápida**: Abre el modal inmediatamente
✅ **Funcional**: Permite editar todos los campos importantes
✅ **Validada**: Incluye validaciones en cliente y servidor
✅ **Consistente**: Usa el mismo estilo de modales del sistema (SweetAlert2)
✅ **Responsive**: Se adapta a diferentes tamaños de pantalla

## Limitaciones

⚠️ **Preguntas de Quiz/Evaluación**: No se pueden editar desde este modal (requiere editor especializado)
⚠️ **Tipo de Actividad**: No se puede cambiar el tipo de una actividad existente

## Estado

✅ **IMPLEMENTADO Y FUNCIONAL**

El botón "Editar" ahora abre un modal funcional que permite modificar los campos principales de la actividad.
