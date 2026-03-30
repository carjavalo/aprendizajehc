# Corrección Error: Columna profile_photo_path no encontrada

## Problema Identificado

Error SQL:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'users.profile_photo_path' in 'field list'
```

## Causa Raíz

La consulta en el método `entregas()` del controlador intentaba obtener la columna `profile_photo_path` de la tabla `users`, pero esta columna no existe en la base de datos actual.

### Código Problemático:
```php
$entregas = DB::table('curso_actividad_entregas')
    ->join('users', 'curso_actividad_entregas.estudiante_id', '=', 'users.id')
    ->where('curso_actividad_entregas.actividad_id', $actividad->id)
    ->select(
        'curso_actividad_entregas.*',
        'users.name as estudiante_nombre',
        'users.email as estudiante_email',
        'users.profile_photo_path'  // ❌ Esta columna no existe
    )
    ->get();
```

## Solución Implementada

### 1. Simplificación de la Consulta en el Controlador

Se eliminó el JOIN innecesario y se simplificó la consulta para obtener solo los datos de entregas. Los datos del estudiante se obtienen a través de la relación Eloquent:

```php
// Obtener entregas de la actividad (simplificado)
$entregas = DB::table('curso_actividad_entregas')
    ->where('curso_actividad_entregas.actividad_id', $actividad->id)
    ->get()
    ->keyBy('estudiante_id');

// Crear lista completa usando la relación del estudiante
$entregasCompletas = $estudiantes->map(function($estudiante) use ($entregas, $actividad) {
    $entrega = $entregas->get($estudiante->id);
    
    if ($entrega) {
        return (object)[
            'id' => $entrega->id,
            'estudiante' => $estudiante,  // ✅ Objeto completo del estudiante
            'fecha_entrega' => $entrega->fecha_entrega ? \Carbon\Carbon::parse($entrega->fecha_entrega) : null,
            'estado' => $entrega->estado ?? 'pendiente',
            'calificacion' => $entrega->calificacion,
            'archivo_path' => $entrega->archivo_path,
            'comentarios' => $entrega->comentarios
        ];
    } else {
        return (object)[
            'id' => null,
            'estudiante' => $estudiante,
            'fecha_entrega' => null,
            'estado' => 'pendiente',
            'calificacion' => null,
            'archivo_path' => null,
            'comentarios' => null
        ];
    }
});
```

**Ventajas:**
- No depende de columnas específicas de la tabla users
- Usa las relaciones Eloquent existentes
- Más eficiente (menos JOINs)
- Más mantenible

### 2. Protección en la Vista

Se agregó verificación adicional con `isset()` para evitar errores si la propiedad no existe:

```php
@if(isset($entrega->estudiante->profile_photo_path) && $entrega->estudiante->profile_photo_path)
    <img src="{{ asset('storage/' . $entrega->estudiante->profile_photo_path) }}" 
         alt="{{ $entrega->estudiante->name }}" 
         class="img-circle elevation-2" 
         style="width: 32px; height: 32px; object-fit: cover;">
@else
    <div class="img-circle elevation-2 d-flex align-items-center justify-content-center" 
         style="width: 32px; height: 32px; background-color: #2e3a75; color: white; font-weight: bold; font-size: 14px;">
        {{ strtoupper(substr($entrega->estudiante->name, 0, 1)) }}
    </div>
@endif
```

**Mejoras:**
- Usa `isset()` para verificar existencia de la propiedad
- Avatar con inicial del nombre como fallback
- Color corporativo (#2e3a75) para el avatar por defecto
- Clase `justify-content-center` corregida (antes era `justify-center`)

## Archivos Modificados

1. **app/Http/Controllers/CursoClassroomController.php**
   - Método `entregas()` - Líneas 1255-1290
   - Simplificada la consulta SQL
   - Eliminado JOIN innecesario

2. **resources/views/admin/capacitaciones/cursos/classroom/entregas.blade.php**
   - Líneas 140-160
   - Agregado `isset()` para verificar propiedad
   - Mejorado el avatar por defecto

## Verificación

Para verificar que la corrección funciona:

1. Acceder a: `http://localhost:8001/capacitaciones/cursos/17/classroom#actividades`
2. Hacer clic en "Ver Entregas" de cualquier actividad
3. La vista debe cargar correctamente mostrando:
   - Avatares con iniciales para usuarios sin foto
   - Fotos de perfil si existen
   - Lista completa de estudiantes

## Notas Técnicas

- La consulta ahora es más eficiente usando `keyBy('estudiante_id')` para indexar por ID
- Se usa `$entregas->get($estudiante->id)` en lugar de `firstWhere()` para mejor rendimiento
- El objeto `$estudiante` viene de la relación Eloquent, por lo que tiene acceso a todas las propiedades del modelo User
- Si en el futuro se agrega la columna `profile_photo_path`, la vista funcionará automáticamente

## Alternativa: Agregar la Columna

Si se desea agregar soporte para fotos de perfil, crear una migración:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('profile_photo_path')->nullable()->after('email');
});
```

Pero la solución actual funciona sin necesidad de modificar la base de datos.
