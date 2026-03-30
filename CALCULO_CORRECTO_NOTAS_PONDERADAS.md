# Cálculo Correcto de Notas Ponderadas

## Fecha: 19 de Enero de 2026

## Fórmula Correcta

**Nota ponderada = Nota × Porcentaje**

Donde:
- **Nota:** Calificación del estudiante en escala 0-5.0
- **Porcentaje:** Porcentaje asignado a la actividad (expresado como decimal)

## Ejemplo de Cálculo

### Configuración del Curso:

**Material 1:**
- Actividad 1.1 (Tarea): 10% del curso
- Actividad 1.2 (Quiz): 20% del curso
- **Total Material 1:** 30%

**Material 2:**
- Actividad 2.1 (Evaluación): 25% del curso
- Actividad 2.2 (Proyecto): 15% del curso
- **Total Material 2:** 40%

**Actividad Independiente:**
- Actividad 3 (Tarea Final): 30% del curso

**Total Curso:** 100%

### Calificaciones del Estudiante:

| Actividad | Nota | Porcentaje | Cálculo | Nota Ponderada |
|-----------|------|------------|---------|----------------|
| Act 1.1 | 4.5 | 10% | 4.5 × 0.10 | 0.45 |
| Act 1.2 | 5.0 | 20% | 5.0 × 0.20 | 1.00 |
| Act 2.1 | 4.0 | 25% | 4.0 × 0.25 | 1.00 |
| Act 2.2 | 3.5 | 15% | 3.5 × 0.15 | 0.525 |
| Act 3 | 4.8 | 30% | 4.8 × 0.30 | 1.44 |

### Suma por Material:

**Material 1:**
- Act 1.1: 0.45
- Act 1.2: 1.00
- **Subtotal:** 1.45

**Material 2:**
- Act 2.1: 1.00
- Act 2.2: 0.525
- **Subtotal:** 1.525

**Actividad Independiente:**
- Act 3: 1.44

### Nota Final del Curso:

```
Nota Final = 1.45 + 1.525 + 1.44 = 4.415
```

**Nota Final:** **4.42/5.0** (redondeado a 2 decimales)

## Código Implementado

```php
private function calcularProgreso($calificaciones, $curso)
{
    if (empty($calificaciones)) {
        return 0;
    }
    
    $notaFinal = 0;
    
    // Calcular nota ponderada por materiales y sus actividades
    foreach ($curso->materiales as $material) {
        $materialKey = 'material_' . $material->id;
        if (!isset($calificaciones[$materialKey])) {
            continue;
        }
        
        $calificacionesMaterial = $calificaciones[$materialKey];
        $notaMaterial = 0;
        
        // Sumar las notas ponderadas de todas las actividades del material
        foreach ($material->actividades as $actividad) {
            $actividadKey = $actividad->tipo . '_' . $actividad->id;
            if (isset($calificacionesMaterial[$actividadKey]) && $calificacionesMaterial[$actividadKey] > 0) {
                $nota = $calificacionesMaterial[$actividadKey]; // Nota en escala 0-5
                $porcentaje = floatval($actividad->porcentaje_curso ?? 0) / 100; // Convertir a decimal
                
                // Multiplicar nota por porcentaje
                $notaPonderada = $nota * $porcentaje;
                $notaMaterial += $notaPonderada;
            }
        }
        
        // Agregar la nota del material al total
        $notaFinal += $notaMaterial;
    }
    
    // Calcular nota ponderada por actividades independientes (sin material)
    foreach ($curso->actividades()->whereNull('material_id')->get() as $actividad) {
        $actividadKey = 'actividad_' . $actividad->id;
        if (isset($calificaciones[$actividadKey]) && $calificaciones[$actividadKey] > 0) {
            $nota = $calificaciones[$actividadKey]; // Nota en escala 0-5
            $porcentaje = floatval($actividad->porcentaje_curso ?? 0) / 100; // Convertir a decimal
            
            // Multiplicar nota por porcentaje
            $notaPonderada = $nota * $porcentaje;
            $notaFinal += $notaPonderada;
        }
    }
    
    return round($notaFinal, 2);
}
```

## Flujo de Cálculo

### 1. Por cada Material:
```
notaMaterial = 0
Para cada actividad del material:
    notaPonderada = nota × (porcentaje / 100)
    notaMaterial += notaPonderada
notaFinal += notaMaterial
```

### 2. Por cada Actividad Independiente:
```
Para cada actividad independiente:
    notaPonderada = nota × (porcentaje / 100)
    notaFinal += notaPonderada
```

### 3. Resultado:
```
notaFinal = suma de todas las notas ponderadas
```

## Ejemplo Paso a Paso

### Paso 1: Material 1

**Actividad 1.1:**
```
nota = 4.5
porcentaje = 10% = 0.10
notaPonderada = 4.5 × 0.10 = 0.45
```

**Actividad 1.2:**
```
nota = 5.0
porcentaje = 20% = 0.20
notaPonderada = 5.0 × 0.20 = 1.00
```

**Subtotal Material 1:**
```
notaMaterial1 = 0.45 + 1.00 = 1.45
```

### Paso 2: Material 2

**Actividad 2.1:**
```
nota = 4.0
porcentaje = 25% = 0.25
notaPonderada = 4.0 × 0.25 = 1.00
```

**Actividad 2.2:**
```
nota = 3.5
porcentaje = 15% = 0.15
notaPonderada = 3.5 × 0.15 = 0.525
```

**Subtotal Material 2:**
```
notaMaterial2 = 1.00 + 0.525 = 1.525
```

### Paso 3: Actividad Independiente

**Actividad 3:**
```
nota = 4.8
porcentaje = 30% = 0.30
notaPonderada = 4.8 × 0.30 = 1.44
```

### Paso 4: Suma Total

```
notaFinal = notaMaterial1 + notaMaterial2 + actividad3
notaFinal = 1.45 + 1.525 + 1.44
notaFinal = 4.415
notaFinal = 4.42 (redondeado)
```

## Visualización en el Gradebook

### Celdas Individuales:
```
┌─────────────┬──────────┬──────────┬──────────┬──────────┬──────────┐
│ Estudiante  │ Act 1.1  │ Act 1.2  │ Act 2.1  │ Act 2.2  │ Act 3    │
│             │ (10%)    │ (20%)    │ (25%)    │ (15%)    │ (30%)    │
├─────────────┼──────────┼──────────┼──────────┼──────────┼──────────┤
│ Juan Pérez  │ 4.5/5.0  │ 5.0/5.0  │ 4.0/5.0  │ 3.5/5.0  │ 4.8/5.0  │
└─────────────┴──────────┴──────────┴──────────┴──────────┴──────────┘
```

### Columna de Promedio:
```
┌─────────────┬──────────────┐
│ Estudiante  │ Promedio     │
├─────────────┼──────────────┤
│ Juan Pérez  │ 4.42/5.0     │
└─────────────┴──────────────┘
```

## Validación del Cálculo

### Verificación Manual:

Si todas las actividades tuvieran nota 5.0:
```
Act 1.1: 5.0 × 0.10 = 0.50
Act 1.2: 5.0 × 0.20 = 1.00
Act 2.1: 5.0 × 0.25 = 1.25
Act 2.2: 5.0 × 0.15 = 0.75
Act 3:   5.0 × 0.30 = 1.50
Total: 0.50 + 1.00 + 1.25 + 0.75 + 1.50 = 5.00 ✅
```

Si todas las actividades tuvieran nota 0.0:
```
Total: 0.0 ✅
```

## Casos Especiales

### Caso 1: Actividad sin calificar
```
Si nota = 0 o NULL:
    No se incluye en el cálculo
    notaPonderada = 0
```

### Caso 2: Porcentaje no asignado
```
Si porcentaje = 0 o NULL:
    notaPonderada = 0
    No afecta la nota final
```

### Caso 3: Material sin actividades
```
notaMaterial = 0
No afecta la nota final
```

## Comparación con Método Anterior (Incorrecto)

### Método Anterior:
```
Aporte = (nota / 5.0) × porcentaje
Ejemplo: (4.5 / 5.0) × 15% = 0.9 × 15% = 13.5%
Luego convertir: (13.5 / 100) × 5.0 = 0.675
```
❌ Innecesariamente complejo

### Método Actual (Correcto):
```
notaPonderada = nota × (porcentaje / 100)
Ejemplo: 4.5 × 0.15 = 0.675
```
✅ Directo y simple

## Ventajas del Método Actual

1. ✅ **Simplicidad:** Multiplicación directa
2. ✅ **Claridad:** Fácil de entender y verificar
3. ✅ **Precisión:** Sin conversiones innecesarias
4. ✅ **Eficiencia:** Menos operaciones
5. ✅ **Correcto:** Matemáticamente exacto

## Conclusión

El cálculo correcto es:

```
Nota Final = Σ (nota_actividad × porcentaje_actividad)
```

Donde:
- Cada nota está en escala 0-5.0
- Cada porcentaje se divide entre 100 para convertir a decimal
- Se suman todas las notas ponderadas
- El resultado está en escala 0-5.0

**Ejemplo:**
- 4.5 × 0.10 = 0.45
- 5.0 × 0.20 = 1.00
- 4.0 × 0.25 = 1.00
- 3.5 × 0.15 = 0.525
- 4.8 × 0.30 = 1.44
- **Total: 4.415 → 4.42/5.0**

✅ Sistema implementado correctamente
