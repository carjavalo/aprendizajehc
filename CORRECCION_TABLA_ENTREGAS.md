# Corrección: Tabla de Entregas Incorrecta

## Problema Identificado

Las entregas de los estudiantes no se mostraban en la vista de entregas aunque existían en la base de datos.

## Causa Raíz

El controlador estaba consultando la tabla incorrecta:
- **Tabla consultada:** `curso_actividad_entregas` (plural) - Tabla vacía creada por la nueva migración
- **Tabla correcta:** `curso_actividad_entrega` (singular) - Tabla existente con datos reales

Además, los nombres de las columnas eran diferentes:
- **Columna buscada:** `estudiante_id`
- **Columna real:** `user_id`

## Estructura de la Tabla Real

**Tabla:** `curso_actividad_entrega` (singular)

Campos principales:
- `id` - ID único
- `curso_id` - FK al curso
- `actividad_id` - FK a la actividad
- `user_id` - FK al usuario (estudiante)
- `contenido` - Contenido de la entrega
- `observaciones_estudiante` - Observaciones del estudiante
- `archivo_path` - Ruta del archivo entregado
- `calificacion` - Nota (decimal)
- `comentarios_instructor` - Retroalimentación del instructor
- `estado` - Estado: 'revisado', etc.
- `entregado_at` - Timestamp de entrega
- `revisado_at` - Timestamp de revisión
- `created_at`, `updated_at`

## Solución Implementada

### 1. Cambio de Tabla en el Controlador

```php
// ❌ ANTES (tabla incorrecta)
$entregas = DB::table('curso_actividad_entregas')
    ->where('curso_actividad_entregas.actividad_id', $actividad->id)
    ->get()
    ->keyBy('estudiante_id');

// ✅ DESPUÉS (tabla correcta)
$entregas = DB::table('curso_actividad_entrega')
    ->where('actividad_id', $actividad->id)
    ->get()
    ->keyBy('user_id');
```

### 2. Mapeo de Campos Correcto

```php
if ($entrega) {
    // Determinar estado basado en la tabla actual
    $estado = 'pendiente';
    if ($entrega->entregado_at) {
        $estado = $entrega->estado === 'revisado' ? 'entregado' : 'entregado';
        
        // Verificar si es tarde
        if ($actividad->fecha_cierre) {
            $fechaCierre = \Carbon\Carbon::parse($actividad->fecha_cierre);
            $fechaEntrega = \Carbon\Carbon::parse($entrega->entregado_at);
            if ($fechaEntrega->gt($fechaCierre)) {
                $estado = 'tarde';
            }
        }
    }
    
    return (object)[
        'id' => $entrega->id,
        'estudiante' => $estudiante,
        'fecha_entrega' => $entrega->entregado_at ? \Carbon\Carbon::parse($entrega->entregado_at) : null,
        'estado' => $estado,
        'calificacion' => $entrega->calificacion,
        'archivo_path' => $entrega->archivo_path,
        'comentarios' => $entrega->comentarios_instructor ?? $entrega->observaciones_estudiante
    ];
}
```

### 3. Lógica de Estado

El estado se determina dinámicamente:
- **Pendiente:** No hay registro de entrega
- **Entregado:** Existe `entregado_at` y está dentro del plazo
- **Tarde:** Existe `entregado_at` pero es posterior a `fecha_cierre`

## Datos de Prueba Encontrados

Se encontraron 5 entregas en la tabla `curso_actividad_entrega`:

1. **Actividad 32** - Usuario 36 - Calificación: 5.00 - Estado: revisado
2. **Actividad 33** - Usuario 36 - Calificación: 4.00 - Estado: revisado
3. **Actividad 34** - Usuario 36 - Calificación: 4.00 - Estado: revisado
4. **Actividad 35** - Usuario 36 - Calificación: 5.00 - Estado: revisado
5. **Actividad 36** - Usuario 36 - Calificación: 5.00 - Estado: revisado

## Nota sobre el Usuario

El usuario buscado `Estudianteuno@estudiante.com` no se encontró en la base de datos. Las entregas pertenecen al `user_id: 36`.

## Archivos Modificados

1. **app/Http/Controllers/CursoClassroomController.php**
   - Método `entregas()` - Líneas 1255-1290
   - Cambiada tabla de `curso_actividad_entregas` a `curso_actividad_entrega`
   - Cambiado campo de `estudiante_id` a `user_id`
   - Actualizado mapeo de campos según estructura real
   - Agregada lógica para determinar estado dinámicamente

## Verificación

Para verificar que funciona:

1. Acceder a: `http://192.168.2.200:8001/capacitaciones/cursos/17/classroom/actividades/32/entregas`
2. Ahora debería mostrar la entrega del usuario 36
3. Verificar que se muestren:
   - Fecha de entrega
   - Estado (Entregado/Tarde)
   - Calificación
   - Botón "Ver Trabajo"

## Recomendación Futura

Considerar:
1. **Unificar tablas:** Decidir si usar `curso_actividad_entrega` (singular) o `curso_actividad_entregas` (plural)
2. **Migración de datos:** Si se decide usar la nueva tabla, migrar los datos existentes
3. **Modelo Eloquent:** Crear un modelo `CursoActividadEntrega` para manejar las entregas de forma más elegante
4. **Consistencia:** Asegurar que todos los métodos usen la misma tabla

## Script de Verificación

Se creó `verificar_entregas.php` para diagnosticar problemas similares en el futuro.
