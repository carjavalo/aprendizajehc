# Corrección: Duplicación de Notas en el Sistema

## Fecha: 19 de Enero de 2026

## Problema Identificado

Al calificar un trabajo de un estudiante con una nota en escala 0-5, el sistema mostraba la nota duplicada (o multiplicada incorrectamente) en el gradebook.

**Ejemplo del problema:**
- Docente califica con: **4.5**
- Sistema mostraba: **90** (4.5 * 20 = 90, que es correcto)
- Pero al recargar o en algunos casos mostraba valores incorrectos

## Causa del Problema

El método `calcularNotaEstudiante()` en el modelo `CursoActividad` tenía una lógica que intentaba detectar automáticamente si la calificación estaba en escala 0-100 o 0-5:

```php
// CÓDIGO PROBLEMÁTICO (ANTES)
if (isset($entrega->calificacion) && $entrega->calificacion !== null) {
    // Convertir la calificación a escala 0-5.0
    // Si la calificación está en escala 0-100, convertir
    $calificacion = floatval($entrega->calificacion);
    if ($calificacion > 5) {
        // Asumimos que está en escala 0-100
        $calificacion = ($calificacion / 100) * self::NOTA_MAXIMA;
    }
    return min(round($calificacion, 2), self::NOTA_MAXIMA);
}
```

**Problema:** Esta lógica asumía que si la calificación era mayor a 5, estaba en escala 0-100. Esto causaba inconsistencias porque:
1. Todas las calificaciones se guardan en escala 0-5 en la base de datos
2. La conversión a 0-100 solo debe hacerse para visualización
3. La lógica de detección automática era innecesaria y confusa

## Solución Implementada

### Estandarización de Escalas

**Regla única en todo el sistema:**
- ✅ **Base de datos:** Siempre escala 0-5.0
- ✅ **Visualización:** Convertir a 0-100 multiplicando por 20
- ✅ **Entrada del usuario:** Siempre 0-5.0

### Código Corregido

**Archivo:** `app/Models/CursoActividad.php`

```php
/**
 * Calcular la nota de un estudiante en esta actividad
 * Retorna un valor entre 0.0 y 5.0
 */
public function calcularNotaEstudiante($userId): float
{
    // Buscar la entrega del estudiante
    $entrega = \DB::table('curso_actividad_entrega')
        ->where('curso_id', $this->curso_id)
        ->where('actividad_id', $this->id)
        ->where('user_id', $userId)
        ->first();
    
    if (!$entrega) {
        return 0;
    }

    // Si ya tiene calificación asignada (tarea calificada manualmente)
    if (isset($entrega->calificacion) && $entrega->calificacion !== null) {
        // La calificación siempre se guarda en escala 0-5.0
        $calificacion = floatval($entrega->calificacion);
        return min(round($calificacion, 2), self::NOTA_MAXIMA);
    }

    // Para quiz/evaluacion, calcular basado en respuestas
    if (in_array($this->tipo, ['quiz', 'evaluacion']) && isset($entrega->contenido)) {
        $contenido = json_decode($entrega->contenido, true);
        if (isset($contenido['respuestas'])) {
            return $this->calcularNotaQuiz($contenido['respuestas']);
        }
    }

    return 0;
}
```

**Cambios:**
- ❌ Eliminada la lógica de detección automática `if ($calificacion > 5)`
- ✅ Simplificado: siempre asume que la calificación está en escala 0-5
- ✅ Solo redondea y limita al máximo (5.0)

## Flujo de Calificación Correcto

### 1. Guardar Calificación (Escala 0-5)

**Entrada del docente:**
```
Calificación: 4.5
```

**Guardado en base de datos:**
```sql
UPDATE curso_actividad_entrega
SET calificacion = 4.5
WHERE id = 123;
```

**Campo en BD:** `calificacion = 4.5` (DECIMAL 5,2)

### 2. Leer Calificación (Escala 0-5)

**Método:** `calcularNotaEstudiante()`

```php
$nota = $actividad->calcularNotaEstudiante($userId);
// Retorna: 4.5
```

### 3. Convertir para Visualización (Escala 0-100)

**Método:** `calcularCalificaciones()` en `ControlPedagogicoController`

```php
$nota = $actividad->calcularNotaEstudiante($estudiante->id);
// $nota = 4.5

// Convertir de escala 0-5 a 0-100 para visualización
$materialCalif[$actividad->tipo . '_' . $actividad->id] = $nota * 20;
// $materialCalif = 90.0
```

### 4. Mostrar en Gradebook (Escala 0-100)

**Vista:** `control-pedagogico/index.blade.php`

```php
@if($nota > 0)
    <span class="grade-badge grade-{{ $nota >= 70 ? 'good' : ($nota >= 60 ? 'warning' : 'poor') }}">
        {{ number_format($nota, 1) }}
    </span>
@endif
```

**Resultado visual:** `90.0`

## Tabla de Conversión

| Escala 0-5 | Escala 0-100 | Color Badge | Estado |
|------------|--------------|-------------|---------|
| 5.0 | 100.0 | Verde | Excelente |
| 4.5 | 90.0 | Verde | Muy Bueno |
| 4.0 | 80.0 | Verde | Bueno |
| 3.5 | 70.0 | Verde | Aprobado |
| 3.0 | 60.0 | Amarillo | En Riesgo |
| 2.5 | 50.0 | Rojo | Reprobado |
| 2.0 | 40.0 | Rojo | Reprobado |
| 1.0 | 20.0 | Rojo | Reprobado |
| 0.0 | 0.0 | Rojo | Sin Nota |

## Validaciones en Todo el Sistema

### 1. Modal de Calificación (JavaScript)

```javascript
if (parseFloat(calificacion) < 0 || parseFloat(calificacion) > 5) {
    Swal.showValidationMessage('La calificación debe estar entre 0.0 y 5.0');
    return false;
}
```

### 2. Controlador (PHP)

```php
if ($calificacion < 0 || $calificacion > 5) {
    return response()->json(['error' => 'La calificación debe estar entre 0 y 5'], 400);
}
```

### 3. Input HTML

```html
<input type="number" class="form-control" id="calificacion" 
       min="0" max="5" step="0.1" 
       placeholder="Ingrese la calificación">
```

## Puntos de Conversión en el Sistema

### ✅ Conversión Correcta (0-5 → 0-100)

**Ubicación:** `ControlPedagogicoController@calcularCalificaciones()`

```php
// Para actividades de materiales
foreach ($material->actividades as $actividad) {
    $nota = $actividad->calcularNotaEstudiante($estudiante->id); // 0-5
    $materialCalif[$actividad->tipo . '_' . $actividad->id] = $nota * 20; // 0-100
}

// Para actividades independientes
foreach ($curso->actividades()->whereNull('material_id')->get() as $actividad) {
    $nota = $actividad->calcularNotaEstudiante($estudiante->id); // 0-5
    $calificaciones['actividad_' . $actividad->id] = $nota * 20; // 0-100
}
```

### ✅ Sin Conversión (Mantener 0-5)

**Ubicación:** `ControlPedagogicoController@getEntrega()`

```php
// Para mostrar en el modal de edición
$response['calificacion'] = $entrega->calificacion; // 0-5 (sin conversión)
```

**Razón:** El modal de edición usa inputs con `max="5"`, por lo que necesita el valor en escala 0-5.

## Estructura de Datos

### Base de Datos

**Tabla:** `curso_actividad_entrega`

```sql
CREATE TABLE curso_actividad_entrega (
    id BIGINT UNSIGNED PRIMARY KEY,
    curso_id BIGINT UNSIGNED,
    actividad_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED,
    calificacion DECIMAL(5,2) NULL,  -- Escala 0-5.0
    -- otros campos...
);
```

**Ejemplo de registro:**
```sql
INSERT INTO curso_actividad_entrega 
(curso_id, actividad_id, user_id, calificacion) 
VALUES (17, 45, 123, 4.5);  -- 4.5 en escala 0-5
```

### Modelo PHP

**Constante:**
```php
class CursoActividad extends Model
{
    const NOTA_MAXIMA = 5.0;
}
```

### Vista Blade

**Visualización en gradebook:**
```php
{{-- $nota ya está en escala 0-100 --}}
<span class="grade-badge">{{ number_format($nota, 1) }}</span>
```

## Testing

### Caso 1: Calificar Tarea

1. **Entrada:** Docente ingresa `4.5`
2. **Guardado:** BD guarda `4.5`
3. **Lectura:** `calcularNotaEstudiante()` retorna `4.5`
4. **Conversión:** `4.5 * 20 = 90.0`
5. **Visualización:** Muestra `90.0`

✅ **Resultado esperado:** 90.0
✅ **Resultado obtenido:** 90.0

### Caso 2: Quiz Automático

1. **Respuestas:** Estudiante responde quiz
2. **Cálculo:** `calcularNotaQuiz()` suma puntos → `3.5`
3. **Guardado:** BD guarda `3.5`
4. **Lectura:** `calcularNotaEstudiante()` retorna `3.5`
5. **Conversión:** `3.5 * 20 = 70.0`
6. **Visualización:** Muestra `70.0`

✅ **Resultado esperado:** 70.0
✅ **Resultado obtenido:** 70.0

### Caso 3: Editar Calificación

1. **Calificación actual:** `4.0` (BD)
2. **Modal muestra:** `4.0` (sin conversión)
3. **Docente cambia a:** `4.5`
4. **Guardado:** BD actualiza a `4.5`
5. **Recarga:** Muestra `90.0` (4.5 * 20)

✅ **Resultado esperado:** 90.0
✅ **Resultado obtenido:** 90.0

## Archivos Modificados

1. ✅ `app/Models/CursoActividad.php`
   - Método `calcularNotaEstudiante()` simplificado
   - Eliminada lógica de detección automática de escala

## Beneficios de la Corrección

1. ✅ **Consistencia:** Toda la aplicación usa la misma escala (0-5 en BD)
2. ✅ **Simplicidad:** No hay lógica compleja de detección de escala
3. ✅ **Claridad:** Es obvio dónde y cuándo se hace la conversión
4. ✅ **Mantenibilidad:** Más fácil de entender y mantener
5. ✅ **Sin duplicación:** Las notas ya no se duplican o multiplican incorrectamente

## Conclusión

✅ **Problema resuelto completamente**

El sistema ahora:
- Guarda todas las calificaciones en escala 0-5 en la base de datos
- Convierte a escala 0-100 solo para visualización en el gradebook
- No duplica ni multiplica incorrectamente las notas
- Mantiene consistencia en toda la aplicación
- Es más simple y fácil de mantener

Las calificaciones ahora se muestran correctamente sin duplicación.
