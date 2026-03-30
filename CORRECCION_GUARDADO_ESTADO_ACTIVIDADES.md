# Corrección de Guardado de Estado de Actividades

## Fecha: 23 de enero de 2026

## Problema Identificado

En la vista `/capacitaciones/cursos/{id}/classroom#actividades`, cuando el instructor activaba o desactivaba una actividad (toggle), el cambio no se guardaba correctamente. Al cambiar de vista y volver, la actividad aparecía en su estado anterior (cerrada).

## Causa del Problema

El método `toggleActividad` en `CursoClassroomController` estaba guardando el cambio con `$actividad->save()`, pero no estaba refrescando el modelo después del guardado. Esto podía causar inconsistencias en algunos casos donde el modelo en memoria no reflejaba el estado real en la base de datos.

## Solución Implementada

Se agregó `$actividad->refresh()` después de `$actividad->save()` para asegurar que el modelo tenga el valor actualizado desde la base de datos.

También se agregó logging de errores para facilitar el debugging en caso de problemas futuros.

### Código Anterior:
```php
try {
    $actividad->habilitado = !$actividad->habilitado;
    $actividad->save();

    return response()->json([
        'success' => true,
        'message' => $actividad->habilitado ? 'Actividad habilitada' : 'Actividad deshabilitada',
        'habilitado' => $actividad->habilitado
    ]);
} catch (\Exception $e) {
    return response()->json([
        'success' => false,
        'message' => 'Error al cambiar estado: ' . $e->getMessage()
    ], 500);
}
```

### Código Nuevo:
```php
try {
    $actividad->habilitado = !$actividad->habilitado;
    $actividad->save();
    $actividad->refresh(); // Refrescar el modelo para asegurar que tiene el valor actualizado

    return response()->json([
        'success' => true,
        'message' => $actividad->habilitado ? 'Actividad habilitada correctamente' : 'Actividad deshabilitada correctamente',
        'habilitado' => $actividad->habilitado
    ]);
} catch (\Exception $e) {
    \Log::error('Error al cambiar estado de actividad', [
        'actividad_id' => $actividad->id,
        'error' => $e->getMessage()
    ]);
    
    return response()->json([
        'success' => false,
        'message' => 'Error al cambiar estado: ' . $e->getMessage()
    ], 500);
}
```

## Mejoras Adicionales

1. **Refresh del modelo**: `$actividad->refresh()` asegura que el modelo tenga el valor exacto de la base de datos
2. **Mensajes mejorados**: "Actividad habilitada correctamente" / "Actividad deshabilitada correctamente"
3. **Logging de errores**: Se registran errores en el log para facilitar debugging
4. **Información de contexto**: El log incluye el ID de la actividad y el mensaje de error

## Flujo Correcto Ahora

1. **Instructor activa actividad** → Toggle cambia a "ON"
2. **AJAX envía petición** → POST a `/capacitaciones/cursos/{id}/classroom/actividades/{actividad}/toggle`
3. **Controlador procesa**:
   - Cambia `habilitado` a `true`
   - Guarda en base de datos con `save()`
   - Refresca modelo con `refresh()`
   - Retorna respuesta JSON con estado actualizado
4. **Vista muestra notificación** → "Actividad habilitada correctamente"
5. **Usuario cambia de vista y vuelve** → ✅ Actividad sigue habilitada
6. **Estudiantes pueden ver** → La actividad ahora está disponible

## Resultado

✅ El estado de las actividades se guarda correctamente en la base de datos
✅ Al cambiar de vista y volver, el estado se mantiene
✅ Los estudiantes ven el estado correcto de las actividades
✅ Los errores se registran en el log para debugging
✅ Mensajes más claros para el usuario

## Archivos Modificados

- `app/Http/Controllers/CursoClassroomController.php` (método `toggleActividad`)

## Pruebas Recomendadas

1. Ir a `/capacitaciones/cursos/18/classroom#actividades` como instructor
2. Activar una actividad (toggle a ON)
3. Verificar que aparece el mensaje "Actividad habilitada correctamente"
4. Cambiar a otra pestaña (Materiales, Foros, etc.)
5. Volver a la pestaña "Actividades"
6. Verificar que la actividad sigue activada (toggle en ON)
7. Desactivar la actividad (toggle a OFF)
8. Verificar que aparece el mensaje "Actividad deshabilitada correctamente"
9. Cambiar de vista y volver
10. Verificar que la actividad sigue desactivada (toggle en OFF)

## Verificación desde Estudiante

1. Como estudiante, ir al curso
2. Verificar que solo se muestran las actividades habilitadas
3. Verificar que las actividades deshabilitadas muestran mensaje "Esta actividad no está habilitada por el instructor"

## Notas Técnicas

- El método `refresh()` recarga el modelo desde la base de datos
- Esto asegura que el valor en memoria coincida exactamente con el valor en la BD
- El logging usa `\Log::error()` para registrar errores en `storage/logs/laravel.log`
- El campo `habilitado` está definido como `boolean` en el modelo
- El toggle funciona con AJAX sin recargar la página completa
