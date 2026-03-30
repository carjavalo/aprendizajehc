# Corrección: Modal de Edición de Quiz/Evaluación

## Fecha: 19 de Enero de 2026

## Problema Identificado

Al hacer click en "Editar" en un quiz o evaluación, el modal no se abría correctamente porque:
1. Los datos se pasaban mediante `data-actividad="{{ json_encode($actividad) }}"` lo cual puede causar problemas con objetos complejos
2. El título del modal decía "Editar" en lugar de "Modificar"

## Solución Implementada

### 1. Cambio en el Manejo del Click (JavaScript)

**Archivo:** `resources/views/admin/capacitaciones/cursos/classroom/actividades.blade.php`

**Antes:**
```javascript
$(document).on('click', '.btn-editar-actividad', function() {
    const actividadId = $(this).data('actividad-id');
    const actividad = $(this).data('actividad');
    
    // Abrir modal de edición completo
    editarActividadCompleta(actividadId, actividad);
});
```

**Después:**
```javascript
$(document).on('click', '.btn-editar-actividad', function() {
    const actividadId = $(this).data('actividad-id');
    
    // Mostrar loading
    Swal.fire({
        title: 'Cargando...',
        html: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
        showConfirmButton: false,
        allowOutsideClick: false
    });
    
    // Obtener datos de la actividad mediante AJAX
    $.ajax({
        url: `/capacitaciones/cursos/{{ $curso->id }}/classroom/actividades/${actividadId}/obtener`,
        type: 'GET',
        success: function(response) {
            Swal.close();
            if (response.success && response.actividad) {
                // Abrir modal de edición completo
                editarActividadCompleta(actividadId, response.actividad);
            } else {
                Swal.fire('Error', 'No se pudo cargar la actividad', 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Error al cargar la actividad', 'error');
        }
    });
});
```

**Ventajas:**
- Los datos se obtienen frescos desde la base de datos
- No hay problemas con el parsing de JSON en data attributes
- Muestra un loading mientras carga
- Mejor manejo de errores

### 2. Nuevo Método en el Controlador

**Archivo:** `app/Http/Controllers/CursoClassroomController.php`

```php
/**
 * Obtener datos de una actividad para edición (solo instructor)
 */
public function obtenerActividad(Curso $curso, CursoActividad $actividad): JsonResponse
{
    $user = Auth::user();
    
    // Solo instructor, admin u operador pueden obtener datos de actividades
    if ($curso->instructor_id !== $user->id && !$user->tienePermisoGestion()) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permisos para ver esta actividad'
        ], 403);
    }

    // Verificar que la actividad pertenece al curso
    if ($actividad->curso_id !== $curso->id) {
        return response()->json([
            'success' => false,
            'message' => 'La actividad no pertenece a este curso'
        ], 400);
    }

    try {
        // Cargar relaciones necesarias
        $actividad->load('material');
        
        return response()->json([
            'success' => true,
            'actividad' => $actividad
        ]);
    } catch (\Exception $e) {
        \Log::error('ERROR al obtener actividad', [
            'message' => $e->getMessage(),
            'actividad_id' => $actividad->id
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error al obtener la actividad'
        ], 500);
    }
}
```

**Características:**
- Verifica permisos del usuario
- Verifica que la actividad pertenezca al curso
- Carga relaciones necesarias (material)
- Retorna JSON con la actividad completa
- Manejo de errores con logging

### 3. Nueva Ruta

**Archivo:** `routes/web.php`

```php
Route::get('/actividades/{actividad}/obtener', [CursoClassroomController::class, 'obtenerActividad'])->name('actividades.obtener');
```

**URL:** `GET /capacitaciones/cursos/{curso}/classroom/actividades/{actividad}/obtener`

### 4. Cambio en el Título del Modal

**Archivo:** `resources/views/admin/capacitaciones/cursos/classroom/actividades.blade.php`

**Antes:**
```javascript
title: `<i class="fas fa-edit"></i> Editar ${tipoLabel}`,
```

**Después:**
```javascript
title: `<i class="fas fa-edit"></i> Modificar ${tipoLabel}`,
```

**Resultado:**
- "Modificar Quiz"
- "Modificar Evaluación"
- "Modificar Tarea"
- "Modificar Proyecto"

## Flujo Completo de Edición

1. **Usuario hace click en botón "Editar"**
   - Se captura el ID de la actividad
   - Se muestra loading con spinner

2. **Se hace petición AJAX GET**
   - URL: `/capacitaciones/cursos/{curso}/classroom/actividades/{actividad}/obtener`
   - Se obtienen los datos frescos de la base de datos

3. **Servidor valida y retorna datos**
   - Verifica permisos del usuario
   - Verifica que la actividad pertenezca al curso
   - Carga relaciones (material)
   - Retorna JSON con la actividad completa

4. **Se cierra el loading**
   - Si hay error: muestra mensaje de error
   - Si es exitoso: llama a `editarActividadCompleta()`

5. **Se abre el modal "Modificar Quiz/Evaluación"**
   - Precarga todos los campos
   - Carga todas las preguntas con opciones
   - Marca respuestas correctas
   - Muestra barra de progreso de puntos
   - Permite editar todo

6. **Usuario realiza cambios y guarda**
   - Se validan los datos
   - Se envía PUT a `/actividades/{actividad}/actualizar`
   - Se actualiza en la base de datos
   - Se recarga la pestaña de actividades

## Datos que se Precargan en el Modal

### Campos Básicos:
- ✅ Título
- ✅ Descripción
- ✅ Instrucciones
- ✅ Fecha de apertura
- ✅ Fecha de cierre
- ✅ Puntos máximos
- ✅ Intentos permitidos

### Configuración de Calificación:
- ✅ Material asociado (dropdown)
- ✅ Porcentaje del material (0-100%)
- ✅ Nota mínima de aprobación (0-5.0)

### Prerrequisitos:
- ✅ Actividades prerrequisito (checkboxes marcados)

### Para Quiz/Evaluación:
- ✅ Duración en minutos
- ✅ Todas las preguntas existentes
- ✅ Texto de cada pregunta
- ✅ Puntos por pregunta
- ✅ Opciones de respuesta (A, B, C, D, etc.)
- ✅ Respuestas correctas marcadas
- ✅ Barra de progreso de puntos totales

## Validaciones

### Al Cargar:
- ✅ Usuario debe tener permisos (instructor, admin, operador)
- ✅ Actividad debe pertenecer al curso

### Al Guardar:
- ✅ Título requerido
- ✅ Material requerido
- ✅ Porcentaje entre 0-100%
- ✅ Nota mínima entre 0.0-5.0
- ✅ Para quiz: mínimo 1 pregunta
- ✅ Para quiz: suma de puntos no puede exceder 5.0
- ✅ Para quiz: cada pregunta debe tener al menos 2 opciones
- ✅ Para quiz: cada pregunta debe tener al menos 1 respuesta correcta

## Respuesta del Servidor

### Éxito:
```json
{
    "success": true,
    "actividad": {
        "id": 15,
        "curso_id": 17,
        "material_id": 66,
        "titulo": "Quiz de Introducción",
        "descripcion": "Evaluación inicial",
        "tipo": "quiz",
        "instrucciones": "Lee cuidadosamente cada pregunta",
        "contenido_json": {
            "duration": 30,
            "questions": [
                {
                    "id": 1,
                    "text": "¿Pregunta 1?",
                    "points": 2.5,
                    "options": {
                        "A": "Opción A",
                        "B": "Opción B",
                        "C": "Opción C"
                    },
                    "correctAnswers": ["A"],
                    "isMultipleChoice": false
                }
            ],
            "totalPoints": 5.0
        },
        "fecha_apertura": "2026-01-20 08:00:00",
        "fecha_cierre": "2026-01-27 23:59:00",
        "puntos_maximos": 100,
        "intentos_permitidos": 2,
        "porcentaje_curso": 15.5,
        "nota_minima_aprobacion": 3.0,
        "prerequisite_activity_ids": [12, 13],
        "material": {
            "id": 66,
            "titulo": "Material 1",
            "porcentaje_curso": 20.0
        }
    }
}
```

### Error:
```json
{
    "success": false,
    "message": "No tienes permisos para ver esta actividad"
}
```

## Archivos Modificados

1. ✅ `resources/views/admin/capacitaciones/cursos/classroom/actividades.blade.php`
   - Event handler del botón editar
   - Título del modal

2. ✅ `app/Http/Controllers/CursoClassroomController.php`
   - Nuevo método `obtenerActividad()`

3. ✅ `routes/web.php`
   - Nueva ruta GET para obtener actividad

## Testing

### Casos de Prueba:

1. **Editar Quiz con preguntas:**
   - ✅ Click en "Editar" de un quiz
   - ✅ Debe mostrar loading
   - ✅ Debe abrir modal "Modificar Quiz"
   - ✅ Debe cargar todas las preguntas
   - ✅ Debe marcar respuestas correctas
   - ✅ Debe permitir agregar/eliminar preguntas
   - ✅ Debe guardar cambios correctamente

2. **Editar Evaluación:**
   - ✅ Click en "Editar" de una evaluación
   - ✅ Debe abrir modal "Modificar Evaluación"
   - ✅ Debe cargar todas las preguntas
   - ✅ Debe permitir modificar puntos
   - ✅ Debe validar suma de puntos <= 5.0

3. **Permisos:**
   - ✅ Instructor puede editar sus actividades
   - ✅ Admin puede editar cualquier actividad
   - ✅ Operador puede editar cualquier actividad
   - ✅ Estudiante NO puede editar actividades

4. **Validaciones:**
   - ✅ No se puede guardar sin título
   - ✅ No se puede guardar sin material
   - ✅ No se puede exceder 5.0 puntos totales
   - ✅ Cada pregunta debe tener mínimo 2 opciones

## Conclusión

✅ **Problema resuelto completamente**

Ahora al hacer click en "Editar" en un quiz o evaluación:
1. Se muestra un loading mientras carga
2. Se obtienen los datos frescos de la base de datos
3. Se abre el modal con título "Modificar Quiz" o "Modificar Evaluación"
4. Se precargan TODOS los datos incluyendo preguntas y opciones
5. Se permite modificar cualquier campo
6. Se guardan los cambios correctamente en la base de datos
