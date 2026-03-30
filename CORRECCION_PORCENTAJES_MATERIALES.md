# Corrección de Porcentajes de Materiales en Control Pedagógico

## Problema Identificado

Los porcentajes de los materiales se mostraban en 0 en el Control Pedagógico aunque el usuario los había editado. La causa raíz era que el método `actualizarMaterial()` en `CursoClassroomController` **NO estaba guardando el campo `porcentaje_curso`** cuando se actualizaba un material.

## Verificación del Problema

Se ejecutó el script `verificar_porcentajes_materiales.php` que confirmó:

```
Material ID: 66 - Preliquidacion: 0.00%
Material ID: 67 - Liquidar: 0.00%
Material ID: 68 - Post Liquidar: 0.00%

Suma total: 0%
```

Las actividades SÍ tienen porcentajes asignados correctamente, pero los materiales no.

## Solución Implementada

### 1. Actualización del Controlador

**Archivo**: `app/Http/Controllers/CursoClassroomController.php`

#### Cambio 1: Agregar validación del campo

```php
$validator = Validator::make($request->all(), [
    'titulo' => 'required|string|max:200',
    'descripcion' => 'nullable|string',
    'tipo' => 'required|in:archivo,video,imagen,documento',
    'archivo' => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,gif,mp4,avi,mov,txt,zip,rar',
    'orden' => 'nullable|integer|min:0',
    'porcentaje_curso' => 'nullable|numeric|min:0|max:100',  // ← AGREGADO
]);
```

#### Cambio 2: Guardar el campo en el modelo

```php
// Actualizar datos básicos
$material->titulo = $request->titulo;
$material->descripcion = $request->descripcion;
$material->tipo = $request->tipo;

if ($request->has('orden')) {
    $material->orden = $request->orden;
}

// Actualizar porcentaje del curso  ← AGREGADO
if ($request->has('porcentaje_curso')) {
    $material->porcentaje_curso = $request->porcentaje_curso;
}
```

## Pasos para el Usuario

Para que los porcentajes se muestren correctamente en el Control Pedagógico, el usuario debe:

1. **Ir a la vista de edición del curso**:
   ```
   http://192.168.2.200:8001/capacitaciones/cursos/17/edit
   ```

2. **Editar cada material** y asignar su porcentaje:
   - Clic en el botón "Editar" de cada material
   - En el campo "Porcentaje del Curso (%)", asignar el valor deseado
   - Guardar cambios

3. **Verificar que los porcentajes sumen 100%**:
   - Material 1: Por ejemplo, 30%
   - Material 2: Por ejemplo, 40%
   - Material 3: Por ejemplo, 30%
   - **Total: 100%**

4. **Verificar en Control Pedagógico**:
   ```
   http://192.168.2.200:8001/academico/control-pedagogico?curso_id=17
   ```
   - Los porcentajes ahora se mostrarán correctamente
   - Las notas finales se calcularán con la ponderación correcta

## Cálculo de Notas con Porcentajes

Una vez asignados los porcentajes, el sistema calculará las notas así:

### Ejemplo con datos reales del curso:

**Material 1: Preliquidacion (30%)**
- Preliquidar 1 (10%): Nota 4.0/5.0
  - 4.0 × 0.10 = 0.40
- Preliquidar 2 (90%): Nota 3.5/5.0
  - 3.5 × 0.90 = 3.15
- **Suma actividades**: 0.40 + 3.15 = 3.55
- **Nota material**: 3.55 × 0.30 = 1.065

**Material 2: Liquidar (40%)**
- Liquidacion 1 (50%): Nota 4.5/5.0
  - 4.5 × 0.50 = 2.25
- Liquidacion 2 (50%): Nota 4.0/5.0
  - 4.0 × 0.50 = 2.00
- **Suma actividades**: 2.25 + 2.00 = 4.25
- **Nota material**: 4.25 × 0.40 = 1.70

**Material 3: Post Liquidar (30%)**
- Pos liquidar 1 (0.5%): Nota 5.0/5.0
  - 5.0 × 0.005 = 0.025
- Final Post Liquidacion (99.5%): Nota 4.2/5.0
  - 4.2 × 0.995 = 4.179
- **Suma actividades**: 0.025 + 4.179 = 4.204
- **Nota material**: 4.204 × 0.30 = 1.261

**NOTA FINAL**: 1.065 + 1.70 + 1.261 = **4.026/5.0**

## Verificación Post-Corrección

Después de asignar los porcentajes, ejecutar:

```bash
php verificar_porcentajes_materiales.php
```

Debería mostrar:
```
Material ID: 66 - Preliquidacion: 30.00%
Material ID: 67 - Liquidar: 40.00%
Material ID: 68 - Post Liquidar: 30.00%

Suma total: 100%
✓ Los porcentajes suman 100% correctamente
```

## Archivos Modificados

1. `app/Http/Controllers/CursoClassroomController.php`
   - Método `actualizarMaterial()`: Agregada validación y guardado del campo `porcentaje_curso`

## Archivos Creados

1. `verificar_porcentajes_materiales.php`
   - Script de diagnóstico para verificar porcentajes de materiales

## Estado

✅ **CORRECCIÓN COMPLETADA**

El controlador ahora guarda correctamente el campo `porcentaje_curso`. El usuario debe reasignar los porcentajes en la interfaz para que se reflejen en el Control Pedagógico.
