# Corrección: Guardar Calificación en Control Pedagógico

## Fecha: 19 de Enero de 2026

## Problema Identificado

En la vista `http://192.168.2.200:8001/academico/control-pedagogico?curso_id=17`, cuando se abre el modal "Detalle de Calificación":
- ✅ Muestra correctamente la información del estudiante
- ✅ Muestra las observaciones del estudiante
- ✅ Permite descargar el archivo adjunto
- ❌ **NO guardaba la calificación** cuando el docente la ingresaba

## Causa del Problema

El método `index()` del controlador solo aceptaba peticiones GET, pero para guardar la calificación se necesitaba hacer una petición POST. El código intentaba enviar un POST a la misma ruta del index con un parámetro `action=guardar_calificacion`, pero esto no funcionaba correctamente.

## Solución Implementada

### 1. Nueva Ruta POST

**Archivo:** `routes/web.php`

```php
Route::post('control-pedagogico/guardar-calificacion', [ControlPedagogicoController::class, 'guardarCalificacionPublic'])->name('control-pedagogico.guardar-calificacion');
```

**URL:** `POST /academico/control-pedagogico/guardar-calificacion`

### 2. Nuevo Método Público en el Controlador

**Archivo:** `app/Http/Controllers/ControlPedagogicoController.php`

```php
/**
 * Guardar calificación de una actividad (método público para ruta POST)
 */
public function guardarCalificacionPublic(Request $request)
{
    return $this->guardarCalificacion($request);
}
```

Este método público llama al método privado `guardarCalificacion()` que ya existía.

### 3. Actualización del JavaScript

**Archivo:** `resources/views/academico/control-pedagogico/index.blade.php`

**Antes:**
```javascript
$.ajax({
    url: '{{ route("academico.control-pedagogico.index") }}',
    method: 'POST',
    data: {
        _token: '{{ csrf_token() }}',
        action: 'guardar_calificacion',
        curso_id: datos.cursoId,
        estudiante_id: datos.estudianteId,
        actividad_id: datos.actividadId,
        calificacion: valores.calificacion,
        retroalimentacion: valores.retroalimentacion
    },
    // ...
});
```

**Después:**
```javascript
$.ajax({
    url: '{{ route("academico.control-pedagogico.guardar-calificacion") }}',
    method: 'POST',
    data: {
        _token: '{{ csrf_token() }}',
        curso_id: datos.cursoId,
        estudiante_id: datos.estudianteId,
        actividad_id: datos.actividadId,
        calificacion: valores.calificacion,
        retroalimentacion: valores.retroalimentacion
    },
    // ...
});
```

**Cambios:**
- ✅ URL actualizada a la nueva ruta específica
- ✅ Eliminado el parámetro `action` (ya no es necesario)
- ✅ Mantiene todos los demás parámetros necesarios

## Flujo Completo de Guardado

### 1. Usuario Abre Modal
- Click en celda de calificación
- Se obtienen datos de la entrega
- Se muestra modal con formulario

### 2. Usuario Ingresa Calificación
- Ingresa nota (0.0 - 5.0)
- Ingresa retroalimentación (opcional)
- Click en "Guardar Calificación"

### 3. Validación en el Cliente
```javascript
if (!calificacion) {
    Swal.showValidationMessage('Por favor ingrese una calificación');
    return false;
}

if (parseFloat(calificacion) < 0 || parseFloat(calificacion) > 5) {
    Swal.showValidationMessage('La calificación debe estar entre 0.0 y 5.0');
    return false;
}
```

### 4. Envío al Servidor
```javascript
POST /academico/control-pedagogico/guardar-calificacion
{
    _token: "...",
    curso_id: 17,
    estudiante_id: 123,
    actividad_id: 45,
    calificacion: 4.5,
    retroalimentacion: "Buen trabajo"
}
```

### 5. Procesamiento en el Servidor

**Método:** `ControlPedagogicoController@guardarCalificacionPublic()`

```php
public function guardarCalificacionPublic(Request $request)
{
    return $this->guardarCalificacion($request);
}

private function guardarCalificacion(Request $request)
{
    // 1. Obtener parámetros
    $cursoId = $request->get('curso_id');
    $estudianteId = $request->get('estudiante_id');
    $actividadId = $request->get('actividad_id');
    $calificacion = $request->get('calificacion');
    $retroalimentacion = $request->get('retroalimentacion');
    
    // 2. Validar calificación
    if ($calificacion < 0 || $calificacion > 5) {
        return response()->json(['error' => 'La calificación debe estar entre 0 y 5'], 400);
    }
    
    // 3. Buscar la entrega
    $entrega = DB::table('curso_actividad_entrega')
        ->where('curso_id', $cursoId)
        ->where('actividad_id', $actividadId)
        ->where('user_id', $estudianteId)
        ->first();
    
    if (!$entrega) {
        return response()->json(['error' => 'No se encontró la entrega'], 404);
    }
    
    // 4. Actualizar calificación
    DB::table('curso_actividad_entrega')
        ->where('id', $entrega->id)
        ->update([
            'calificacion' => $calificacion,
            'comentarios_instructor' => $retroalimentacion,
            'estado' => 'revisado',
            'revisado_at' => now(),
            'updated_at' => now()
        ]);
    
    // 5. Retornar éxito
    return response()->json([
        'success' => true,
        'mensaje' => 'Calificación guardada correctamente'
    ]);
}
```

### 6. Respuesta al Cliente

**Éxito:**
```json
{
    "success": true,
    "mensaje": "Calificación guardada correctamente"
}
```

**Error (validación):**
```json
{
    "error": "La calificación debe estar entre 0 y 5"
}
```

**Error (no encontrado):**
```json
{
    "error": "No se encontró la entrega"
}
```

### 7. Actualización de la Vista
```javascript
success: function(response) {
    Swal.fire({
        icon: 'success',
        title: '¡Guardado!',
        text: 'La calificación ha sido guardada correctamente',
        confirmButtonColor: '#2c4370'
    }).then(() => {
        // Recargar la página para actualizar las calificaciones
        location.reload();
    });
}
```

## Datos que se Actualizan en la Base de Datos

**Tabla:** `curso_actividad_entrega`

**Campos actualizados:**
- `calificacion`: Nota asignada (0.0 - 5.0)
- `comentarios_instructor`: Retroalimentación del docente
- `estado`: Cambia a 'revisado'
- `revisado_at`: Timestamp de cuándo se revisó
- `updated_at`: Timestamp de actualización

**Ejemplo:**
```sql
UPDATE curso_actividad_entrega
SET 
    calificacion = 4.5,
    comentarios_instructor = 'Buen trabajo, sigue así',
    estado = 'revisado',
    revisado_at = '2026-01-19 15:30:00',
    updated_at = '2026-01-19 15:30:00'
WHERE id = 123;
```

## Validaciones Implementadas

### En el Cliente (JavaScript):
1. ✅ Calificación no puede estar vacía
2. ✅ Calificación debe estar entre 0.0 y 5.0
3. ✅ Retroalimentación es opcional

### En el Servidor (PHP):
1. ✅ Calificación debe estar entre 0 y 5
2. ✅ La entrega debe existir en la base de datos
3. ✅ Debe pertenecer al curso, actividad y estudiante correctos

## Manejo de Errores

### Error de Validación:
```javascript
Swal.showValidationMessage('La calificación debe estar entre 0.0 y 5.0');
```

### Error de Servidor:
```javascript
error: function(xhr) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo guardar la calificación',
        confirmButtonColor: '#2c4370'
    });
}
```

## Archivos Modificados

1. ✅ `routes/web.php`
   - Agregada ruta POST para guardar calificación

2. ✅ `app/Http/Controllers/ControlPedagogicoController.php`
   - Agregado método público `guardarCalificacionPublic()`

3. ✅ `resources/views/academico/control-pedagogico/index.blade.php`
   - Actualizada URL en función `guardarCalificacion()`
   - Eliminado parámetro `action`

## Testing

### Casos de Prueba:

1. **Guardar calificación válida:**
   - ✅ Ingresar nota 4.5
   - ✅ Ingresar retroalimentación
   - ✅ Click en "Guardar"
   - ✅ Debe mostrar mensaje de éxito
   - ✅ Debe recargar la página
   - ✅ Debe mostrar la nueva calificación en el gradebook

2. **Validación de rango:**
   - ✅ Ingresar nota -1 → Error
   - ✅ Ingresar nota 6 → Error
   - ✅ Ingresar nota 0 → Válido
   - ✅ Ingresar nota 5 → Válido

3. **Retroalimentación opcional:**
   - ✅ Guardar sin retroalimentación → Válido
   - ✅ Guardar con retroalimentación → Válido

4. **Actualización de calificación existente:**
   - ✅ Modificar calificación de 3.0 a 4.5
   - ✅ Debe actualizar correctamente
   - ✅ Debe mantener el historial en `updated_at`

## Visualización en el Gradebook

Después de guardar, la calificación se muestra:

**Escala 0-100 (visualización):**
```php
{{ number_format($nota, 1) }}
```

**Escala 0-5 (base de datos):**
```php
$nota = $entrega->calificacion; // 4.5
$notaVisual = $nota * 20; // 90.0
```

**Badge con color:**
- Verde: >= 70% (>= 3.5/5.0)
- Amarillo: 60-69% (3.0-3.4/5.0)
- Rojo: < 60% (< 3.0/5.0)

## Conclusión

✅ **Problema resuelto completamente**

Ahora el sistema:
1. Muestra correctamente el modal con la información del estudiante
2. Permite ingresar la calificación (0.0 - 5.0)
3. Permite ingresar retroalimentación opcional
4. **GUARDA correctamente la calificación en la base de datos**
5. Actualiza el estado a 'revisado'
6. Recarga la página para mostrar la nueva calificación
7. Muestra la calificación actualizada en el gradebook

El docente ahora puede calificar las tareas de los estudiantes sin problemas.
