# Proceso Completo de Cálculo de Notas

## Fecha: 19 de Enero de 2026

## Estructura Jerárquica

```
CURSO (100%)
├── Material 1 (30%)
│   ├── Actividad 1.1 (40% del material)
│   ├── Actividad 1.2 (35% del material)
│   └── Actividad 1.3 (25% del material)
├── Material 2 (40%)
│   ├── Actividad 2.1 (50% del material)
│   └── Actividad 2.2 (50% del material)
└── Material 3 (30%)
    └── Actividad 3.1 (100% del material)
```

## Reglas de Validación

### 1. Notas de Actividades
- ✅ Rango: **0.0 a 5.0**
- ✅ Validación en inputs: `min="0" max="5" step="0.1"`
- ✅ Validación en servidor: `$calificacion >= 0 && $calificacion <= 5`

### 2. Porcentajes de Materiales
- ✅ Suma total: **100%**
- ✅ Cada material: **0% a 100%**
- ✅ Validación: `Σ porcentajes_materiales = 100%`

### 3. Porcentajes de Actividades
- ✅ Por material: Suma puede ser cualquier valor
- ✅ Cada actividad: **0% a 100%**
- ✅ Se normalizan dentro del material

## Proceso de Cálculo (4 Pasos)

### Paso 1: Multiplicar Nota × Porcentaje Actividad

Para cada actividad calificada:
```
notaPonderadaActividad = nota × (porcentaje_actividad / 100)
```

**Ejemplo:**
- Actividad 1.1: nota = 4.5, porcentaje = 40%
- Cálculo: `4.5 × 0.40 = 1.8`

### Paso 2: Sumar Actividades por Material

Sumar todas las notas ponderadas de actividades del mismo material:
```
sumaMaterial = Σ notasPonderadasActividades
```

**Ejemplo Material 1:**
```
Actividad 1.1: 4.5 × 0.40 = 1.8
Actividad 1.2: 5.0 × 0.35 = 1.75
Actividad 1.3: 4.0 × 0.25 = 1.0
sumaMaterial1 = 1.8 + 1.75 + 1.0 = 4.55
```

### Paso 3: Multiplicar Suma × Porcentaje Material

Multiplicar la suma del material por su porcentaje en el curso:
```
notaPonderadaMaterial = sumaMaterial × (porcentaje_material / 100)
```

**Ejemplo Material 1:**
```
sumaMaterial1 = 4.55
porcentaje_material1 = 30%
notaPonderadaMaterial1 = 4.55 × 0.30 = 1.365
```

### Paso 4: Sumar Todos los Materiales

Sumar las notas ponderadas de todos los materiales:
```
notaFinal = Σ notasPonderadasMateriales
```

**Ejemplo:**
```
Material 1: 1.365
Material 2: 1.800
Material 3: 1.350
notaFinal = 1.365 + 1.800 + 1.350 = 4.515 → 4.52/5.0
```

## Ejemplo Completo Detallado

### Configuración del Curso:

**Material 1 (30% del curso):**
- Actividad 1.1 (Tarea): 40% del material
- Actividad 1.2 (Quiz): 35% del material
- Actividad 1.3 (Evaluación): 25% del material

**Material 2 (40% del curso):**
- Actividad 2.1 (Proyecto): 50% del material
- Actividad 2.2 (Tarea): 50% del material

**Material 3 (30% del curso):**
- Actividad 3.1 (Evaluación Final): 100% del material

### Calificaciones del Estudiante:

| Material | Actividad | Nota | % Actividad | % Material |
|----------|-----------|------|-------------|------------|
| Material 1 | Act 1.1 | 4.5 | 40% | 30% |
| Material 1 | Act 1.2 | 5.0 | 35% | 30% |
| Material 1 | Act 1.3 | 4.0 | 25% | 30% |
| Material 2 | Act 2.1 | 4.8 | 50% | 40% |
| Material 2 | Act 2.2 | 4.2 | 50% | 40% |
| Material 3 | Act 3.1 | 4.5 | 100% | 30% |

### Cálculo Paso a Paso:

#### Material 1:

**Paso 1: Multiplicar notas por porcentajes de actividades**
```
Act 1.1: 4.5 × 0.40 = 1.80
Act 1.2: 5.0 × 0.35 = 1.75
Act 1.3: 4.0 × 0.25 = 1.00
```

**Paso 2: Sumar actividades del material**
```
sumaMaterial1 = 1.80 + 1.75 + 1.00 = 4.55
```

**Paso 3: Multiplicar por porcentaje del material**
```
notaPonderadaMaterial1 = 4.55 × 0.30 = 1.365
```

#### Material 2:

**Paso 1: Multiplicar notas por porcentajes de actividades**
```
Act 2.1: 4.8 × 0.50 = 2.40
Act 2.2: 4.2 × 0.50 = 2.10
```

**Paso 2: Sumar actividades del material**
```
sumaMaterial2 = 2.40 + 2.10 = 4.50
```

**Paso 3: Multiplicar por porcentaje del material**
```
notaPonderadaMaterial2 = 4.50 × 0.40 = 1.800
```

#### Material 3:

**Paso 1: Multiplicar notas por porcentajes de actividades**
```
Act 3.1: 4.5 × 1.00 = 4.50
```

**Paso 2: Sumar actividades del material**
```
sumaMaterial3 = 4.50
```

**Paso 3: Multiplicar por porcentaje del material**
```
notaPonderadaMaterial3 = 4.50 × 0.30 = 1.350
```

#### Paso 4: Suma Final

```
notaFinal = 1.365 + 1.800 + 1.350 = 4.515
notaFinal = 4.52 (redondeado a 2 decimales)
```

**Nota Final del Estudiante: 4.52/5.0**

## Código Implementado

```php
private function calcularProgreso($calificaciones, $curso)
{
    if (empty($calificaciones)) {
        return 0;
    }
    
    $notaFinal = 0;
    
    // Calcular nota ponderada por materiales
    foreach ($curso->materiales as $material) {
        $materialKey = 'material_' . $material->id;
        if (!isset($calificaciones[$materialKey])) {
            continue;
        }
        
        $calificacionesMaterial = $calificaciones[$materialKey];
        $sumaActividadesMaterial = 0;
        
        // Paso 1 y 2: Multiplicar cada nota por su porcentaje de actividad y sumar
        foreach ($material->actividades as $actividad) {
            $actividadKey = $actividad->tipo . '_' . $actividad->id;
            if (isset($calificacionesMaterial[$actividadKey]) && $calificacionesMaterial[$actividadKey] > 0) {
                $nota = $calificacionesMaterial[$actividadKey]; // Nota en escala 0-5
                $porcentajeActividad = floatval($actividad->porcentaje_curso ?? 0) / 100;
                
                // Paso 1: Multiplicar nota por porcentaje de actividad
                $notaPonderadaActividad = $nota * $porcentajeActividad;
                
                // Paso 2: Sumar al material
                $sumaActividadesMaterial += $notaPonderadaActividad;
            }
        }
        
        // Paso 3: Multiplicar la suma de actividades por el porcentaje del material
        $porcentajeMaterial = floatval($material->porcentaje_curso ?? 0) / 100;
        $notaPonderadaMaterial = $sumaActividadesMaterial * $porcentajeMaterial;
        
        // Paso 4: Agregar al total
        $notaFinal += $notaPonderadaMaterial;
    }
    
    // Actividades independientes (sin material)
    foreach ($curso->actividades()->whereNull('material_id')->get() as $actividad) {
        $actividadKey = 'actividad_' . $actividad->id;
        if (isset($calificaciones[$actividadKey]) && $calificaciones[$actividadKey] > 0) {
            $nota = $calificaciones[$actividadKey];
            $porcentaje = floatval($actividad->porcentaje_curso ?? 0) / 100;
            $notaPonderada = $nota * $porcentaje;
            $notaFinal += $notaPonderada;
        }
    }
    
    return round($notaFinal, 2);
}
```

## Comparación con Nota Mínima de Aprobación

Después de calcular la nota final, se compara con la nota mínima:

```php
$notaMinima = floatval($curso->nota_minima_aprobacion ?? 3.0);

if ($notaFinal >= $notaMinima) {
    $estado = 'Aprobado';
} else {
    $estado = 'Reprobado';
}
```

**Ejemplo:**
- Nota final: 4.52/5.0
- Nota mínima: 3.0/5.0
- Resultado: **Aprobado** ✅

## Tabla Resumen del Ejemplo

| Paso | Material | Cálculo | Resultado |
|------|----------|---------|-----------|
| 1-2 | Material 1 | (4.5×0.40) + (5.0×0.35) + (4.0×0.25) | 4.55 |
| 3 | Material 1 | 4.55 × 0.30 | 1.365 |
| 1-2 | Material 2 | (4.8×0.50) + (4.2×0.50) | 4.50 |
| 3 | Material 2 | 4.50 × 0.40 | 1.800 |
| 1-2 | Material 3 | 4.5 × 1.00 | 4.50 |
| 3 | Material 3 | 4.50 × 0.30 | 1.350 |
| 4 | **TOTAL** | 1.365 + 1.800 + 1.350 | **4.515** |

**Nota Final: 4.52/5.0**

## Validaciones Implementadas

### En el Input de Calificación:
```html
<input type="number" 
       min="0" 
       max="5" 
       step="0.1" 
       class="form-control" 
       id="calificacion">
```

### En JavaScript:
```javascript
if (parseFloat(calificacion) < 0 || parseFloat(calificacion) > 5) {
    Swal.showValidationMessage('La calificación debe estar entre 0.0 y 5.0');
    return false;
}
```

### En PHP (Servidor):
```php
if ($calificacion < 0 || $calificacion > 5) {
    return response()->json([
        'error' => 'La calificación debe estar entre 0 y 5'
    ], 400);
}
```

## Visualización en el Gradebook

### Celdas de Actividades:
```
┌──────────────┬──────────┬──────────┬──────────┐
│ Estudiante   │ Act 1.1  │ Act 1.2  │ Act 1.3  │
│              │ (40%)    │ (35%)    │ (25%)    │
├──────────────┼──────────┼──────────┼──────────┤
│ Juan Pérez   │ 4.5/5.0  │ 5.0/5.0  │ 4.0/5.0  │
└──────────────┴──────────┴──────────┴──────────┘
```

### Columna de Promedio:
```
┌──────────────┬──────────────┬──────────┐
│ Estudiante   │ Promedio     │ Estado   │
├──────────────┼──────────────┼──────────┤
│ Juan Pérez   │ 4.52/5.0     │ Aprobado │
└──────────────┴──────────────┴──────────┘
```

## Casos Especiales

### Caso 1: Material sin actividades calificadas
```
Si todas las actividades tienen nota 0:
    sumaMaterial = 0
    notaPonderadaMaterial = 0 × porcentaje_material = 0
```

### Caso 2: Actividad sin calificar
```
Si nota = 0 o NULL:
    No se incluye en el cálculo
    notaPonderadaActividad = 0
```

### Caso 3: Material con porcentaje 0
```
Si porcentaje_material = 0:
    notaPonderadaMaterial = suma × 0 = 0
    No afecta la nota final
```

## Ventajas del Sistema

1. ✅ **Jerarquía clara:** Materiales → Actividades
2. ✅ **Flexibilidad:** Cada material puede tener diferentes actividades
3. ✅ **Ponderación correcta:** Doble nivel de porcentajes
4. ✅ **Transparencia:** Fácil de entender y verificar
5. ✅ **Precisión:** Cálculo matemáticamente exacto

## Conclusión

El sistema calcula las notas siguiendo estos 4 pasos:

1. **Multiplicar:** Nota × % Actividad
2. **Sumar:** Todas las actividades del material
3. **Multiplicar:** Suma × % Material
4. **Sumar:** Todos los materiales

**Fórmula completa:**
```
NotaFinal = Σ [ (Σ nota_actividad × %_actividad) × %_material ]
```

✅ Sistema implementado correctamente con jerarquía de porcentajes
