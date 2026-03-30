# Corrección: Cálculo Ponderado de Notas según Porcentajes

## Fecha: 19 de Enero de 2026

## Problema Identificado

El sistema multiplicaba todas las notas por 20 para convertirlas a escala 0-100, pero esto era incorrecto. Las notas deben:
1. Mantenerse en escala 0-5.0
2. Ponderarse según el porcentaje asignado a cada actividad
3. Sumarse para obtener la nota final del curso

**Ejemplo del problema anterior:**
- Actividad con 15% del curso
- Estudiante saca 4.5/5.0
- Sistema mostraba: 90 (4.5 * 20)
- **Incorrecto:** No consideraba el porcentaje de la actividad

**Ejemplo correcto:**
- Actividad con 15% del curso
- Estudiante saca 4.5/5.0
- Aporte al curso: (4.5/5.0) * 15% = 13.5%
- Se muestra: 4.5/5.0

## Solución Implementada

### 1. Eliminada Conversión a Escala 0-100

**Archivo:** `app/Http/Controllers/ControlPedagogicoController.php`

**Método:** `calcularCalificaciones()`

**Antes:**
```php
foreach ($material->actividades as $actividad) {
    $nota = $actividad->calcularNotaEstudiante($estudiante->id);
    // Convertir de escala 0-5 a 0-100 para visualización
    $materialCalif[$actividad->tipo . '_' . $actividad->id] = $nota * 20;
}
```

**Después:**
```php
foreach ($material->actividades as $actividad) {
    $nota = $actividad->calcularNotaEstudiante($estudiante->id);
    // Guardar la nota en escala 0-5 (sin conversión)
    $materialCalif[$actividad->tipo . '_' . $actividad->id] = $nota;
}
```

### 2. Nuevo Cálculo Ponderado de Progreso

**Método:** `calcularProgreso()`

```php
private function calcularProgreso($calificaciones, $curso)
{
    if (empty($calificaciones)) {
        return 0;
    }
    
    $notaFinal = 0;
    $porcentajeTotal = 0;
    
    // Calcular nota ponderada por materiales y sus actividades
    foreach ($curso->materiales as $material) {
        $materialKey = 'material_' . $material->id;
        if (!isset($calificaciones[$materialKey])) {
            continue;
        }
        
        $calificacionesMaterial = $calificaciones[$materialKey];
        
        foreach ($material->actividades as $actividad) {
            $actividadKey = $actividad->tipo . '_' . $actividad->id;
            if (isset($calificacionesMaterial[$actividadKey]) && $calificacionesMaterial[$actividadKey] > 0) {
                // Ponderar por el porcentaje de la actividad
                $porcentajeActividad = floatval($actividad->porcentaje_curso ?? 0);
                if ($porcentajeActividad > 0) {
                    // Calcular aporte: (nota/5) * porcentaje
                    $aporte = ($calificacionesMaterial[$actividadKey] / 5.0) * $porcentajeActividad;
                    $notaFinal += $aporte;
                    $porcentajeTotal += $porcentajeActividad;
                }
            }
        }
    }
    
    // Calcular nota ponderada por actividades independientes
    foreach ($curso->actividades()->whereNull('material_id')->get() as $actividad) {
        $actividadKey = 'actividad_' . $actividad->id;
        if (isset($calificaciones[$actividadKey]) && $calificaciones[$actividadKey] > 0) {
            $porcentajeActividad = floatval($actividad->porcentaje_curso ?? 0);
            if ($porcentajeActividad > 0) {
                // Calcular aporte: (nota/5) * porcentaje
                $aporte = ($calificaciones[$actividadKey] / 5.0) * $porcentajeActividad;
                $notaFinal += $aporte;
                $porcentajeTotal += $porcentajeActividad;
            }
        }
    }
    
    // Si no hay porcentajes asignados, retornar 0
    if ($porcentajeTotal == 0) {
        return 0;
    }
    
    // Convertir de porcentaje a escala 0-5
    // notaFinal está en escala 0-100 (porcentaje), convertir a 0-5
    $notaEscala5 = ($notaFinal / 100) * 5.0;
    
    return round($notaEscala5, 2);
}
```

### 3. Actualizada Visualización en la Vista

**Archivo:** `resources/views/academico/control-pedagogico/index.blade.php`

**Antes:**
```php
@if($nota > 0)
    <span class="grade-badge grade-{{ $nota >= 70 ? 'good' : ($nota >= 60 ? 'warning' : 'poor') }}">
        {{ number_format($nota, 1) }}
    </span>
@endif
```

**Después:**
```php
@php
    $porcentaje = ($nota / 5.0) * 100;
@endphp
@if($nota > 0)
    <span class="grade-badge grade-{{ $porcentaje >= 60 ? 'good' : ($porcentaje >= 50 ? 'warning' : 'poor') }}">
        {{ number_format($nota, 1) }}/5.0
    </span>
@endif
```

## Ejemplo de Cálculo Ponderado

### Configuración del Curso:

**Material 1 (30% del curso):**
- Actividad 1.1 (Tarea): 10% del curso
- Actividad 1.2 (Quiz): 20% del curso

**Material 2 (40% del curso):**
- Actividad 2.1 (Evaluación): 25% del curso
- Actividad 2.2 (Proyecto): 15% del curso

**Actividad Independiente:**
- Actividad 3 (Tarea Final): 30% del curso

**Total:** 100% del curso

### Calificaciones del Estudiante:

| Actividad | Nota (0-5) | Porcentaje | Aporte al Curso |
|-----------|------------|------------|-----------------|
| Actividad 1.1 | 4.5 | 10% | (4.5/5.0) * 10% = 9.0% |
| Actividad 1.2 | 5.0 | 20% | (5.0/5.0) * 20% = 20.0% |
| Actividad 2.1 | 4.0 | 25% | (4.0/5.0) * 25% = 20.0% |
| Actividad 2.2 | 3.5 | 15% | (3.5/5.0) * 15% = 10.5% |
| Actividad 3 | 4.8 | 30% | (4.8/5.0) * 30% = 28.8% |

**Suma de aportes:** 9.0% + 20.0% + 20.0% + 10.5% + 28.8% = **88.3%**

**Conversión a escala 0-5:** (88.3 / 100) * 5.0 = **4.42/5.0**

**Nota final del estudiante:** **4.42/5.0** (88.3%)

## Visualización en el Gradebook

### Celdas de Actividades:

```
┌─────────────┬──────────┬──────────┬──────────┬──────────┬──────────┐
│ Estudiante  │ Act 1.1  │ Act 1.2  │ Act 2.1  │ Act 2.2  │ Act 3    │
├─────────────┼──────────┼──────────┼──────────┼──────────┼──────────┤
│ Juan Pérez  │ 4.5/5.0  │ 5.0/5.0  │ 4.0/5.0  │ 3.5/5.0  │ 4.8/5.0  │
└─────────────┴──────────┴──────────┴──────────┴──────────┴──────────┘
```

### Columna de Promedio Final:

```
┌─────────────┬──────────────┐
│ Estudiante  │ Promedio     │
├─────────────┼──────────────┤
│ Juan Pérez  │ 4.42/5.0     │
└─────────────┴──────────────┘
```

## Colores de Badges

Los colores se determinan convirtiendo la nota a porcentaje:

```php
$porcentaje = ($nota / 5.0) * 100;
```

**Umbrales:**
- 🟢 Verde (Aprobado): >= 60% (>= 3.0/5.0)
- 🟡 Amarillo (En Riesgo): 50-59% (2.5-2.9/5.0)
- 🔴 Rojo (Reprobado): < 50% (< 2.5/5.0)

## Ventajas del Nuevo Sistema

1. ✅ **Ponderación correcta:** Cada actividad aporta según su porcentaje
2. ✅ **Transparencia:** Se muestra la nota real (0-5) que sacó el estudiante
3. ✅ **Flexibilidad:** Permite diferentes distribuciones de porcentajes
4. ✅ **Precisión:** El promedio final refleja correctamente el desempeño
5. ✅ **Claridad:** Es fácil entender cuánto vale cada actividad

## Comparación Antes vs Después

### Antes (Incorrecto):
```
Actividad 1: 4.5/5.0 → Mostraba: 90
Actividad 2: 5.0/5.0 → Mostraba: 100
Promedio: (90 + 100) / 2 = 95
```
❌ No consideraba los porcentajes de las actividades

### Después (Correcto):
```
Actividad 1 (10%): 4.5/5.0 → Aporte: 9.0%
Actividad 2 (20%): 5.0/5.0 → Aporte: 20.0%
Nota final: 29.0% → 1.45/5.0
```
✅ Considera los porcentajes correctamente

## Archivos Modificados

1. ✅ `app/Http/Controllers/ControlPedagogicoController.php`
   - Método `calcularCalificaciones()`: Eliminada conversión *20
   - Método `calcularProgreso()`: Implementado cálculo ponderado
   - Método `determinarEstado()`: Actualizado para escala 0-5

2. ✅ `resources/views/academico/control-pedagogico/index.blade.php`
   - Actualizada visualización de notas: `X.X/5.0`
   - Actualizado cálculo de colores usando porcentajes
   - Actualizada columna de promedio final

## Testing

### Caso 1: Actividad con 15% del curso

**Entrada:**
- Porcentaje: 15%
- Nota: 4.5/5.0

**Cálculo:**
```
Aporte = (4.5 / 5.0) * 15%
Aporte = 0.9 * 15%
Aporte = 13.5%
```

**Visualización:**
- Celda: `4.5/5.0` (verde)
- Aporte al promedio: 13.5%

### Caso 2: Curso completo

**Actividades:**
- A1: 4.5/5.0 (20%) → 18.0%
- A2: 5.0/5.0 (30%) → 30.0%
- A3: 4.0/5.0 (25%) → 20.0%
- A4: 3.5/5.0 (25%) → 17.5%

**Suma:** 85.5%

**Nota final:** (85.5 / 100) * 5.0 = **4.28/5.0**

**Estado:** Aprobado (85.5% >= 60%)

## Conclusión

✅ **Sistema corregido completamente**

Ahora el sistema:
1. Mantiene las notas en escala 0-5.0
2. Pondera correctamente según el porcentaje de cada actividad
3. Calcula el promedio final considerando todos los porcentajes
4. Muestra las notas de forma clara: `X.X/5.0`
5. Usa colores apropiados según el desempeño

El cálculo de notas ahora es correcto y transparente.
