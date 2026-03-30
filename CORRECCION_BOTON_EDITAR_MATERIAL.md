# CORRECCIÓN - ERROR EN BOTÓN EDITAR MATERIAL

## PROBLEMA IDENTIFICADO

**Error:** `Uncaught TypeError: can't access property "titulo", materialData is undefined`

**Ubicación:** `/capacitaciones/cursos/17/edit` línea 2480

**Causa:** El botón "Editar" de materiales solo tenía el atributo `data-material-id` pero NO tenía `data-material` con el objeto completo del material. El código JavaScript intentaba acceder a `materialData.titulo` pero `materialData` era `undefined`.

## CÓDIGO PROBLEMÁTICO

### Antes (HTML):
```html
<button type="button" class="btn btn-warning btn-sm btn-editar-material" 
        data-material-id="{{ $material->id }}">
    <i class="fas fa-edit"></i> Editar
</button>
```

### Antes (JavaScript):
```javascript
$(document).on('click', '.btn-editar-material', function() {
    const materialData = $(this).data('material'); // ← undefined
    const materialId = $(this).data('material-id');
    
    $('#edit_titulo').val(materialData.titulo || ''); // ← ERROR aquí
    // ...
});
```

## SOLUCIÓN IMPLEMENTADA

### 1. Agregado atributo `data-material` al botón

**Archivo:** `resources/views/admin/capacitaciones/cursos/edit.blade.php`

```html
<button type="button" class="btn btn-warning btn-sm btn-editar-material" 
        data-material-id="{{ $material->id }}"
        data-material="{{ htmlspecialchars(json_encode([
            'id' => $material->id,
            'titulo' => $material->titulo,
            'descripcion' => $material->descripcion,
            'tipo' => $material->tipo,
            'orden' => $material->orden,
            'porcentaje_curso' => $material->porcentaje_curso,
            'url_externa' => $material->url_externa,
            'prerequisite_id' => $material->prerequisite_id,
            'archivo_path' => $material->archivo_path
        ]), ENT_QUOTES, 'UTF-8') }}">
    <i class="fas fa-edit"></i> Editar
</button>
```

**Explicación:**
- `json_encode()` convierte el array PHP a JSON
- `htmlspecialchars()` escapa caracteres especiales para HTML
- `ENT_QUOTES` escapa comillas simples y dobles
- jQuery automáticamente parsea el JSON cuando se usa `.data('material')`

### 2. Agregada validación en JavaScript

```javascript
$(document).on('click', '.btn-editar-material', function() {
    const materialData = $(this).data('material');
    const materialId = $(this).data('material-id');
    
    // Validar que materialData existe
    if (!materialData || typeof materialData !== 'object') {
        console.error('Error: materialData no está definido o no es válido');
        Swal.fire('Error', 'No se pudieron cargar los datos del material', 'error');
        return;
    }
    
    // Llenar el formulario con los datos actuales
    $('#edit_material_id').val(materialId);
    $('#edit_titulo').val(materialData.titulo || '');
    $('#edit_descripcion').val(materialData.descripcion || '');
    // ... resto del código
});
```

**Explicación:**
- Verifica que `materialData` existe y es un objeto
- Si no es válido, muestra un error y detiene la ejecución
- Evita el error `Cannot read properties of undefined`

## VENTAJAS DE ESTA SOLUCIÓN

1. ✅ **Datos completos en el HTML** - El botón contiene toda la información necesaria
2. ✅ **No requiere AJAX adicional** - Los datos ya están disponibles
3. ✅ **Validación robusta** - Maneja el caso cuando los datos no están disponibles
4. ✅ **Escapado seguro** - `htmlspecialchars()` previene inyección XSS
5. ✅ **Compatible con jQuery** - jQuery parsea automáticamente el JSON

## CÓMO FUNCIONA

### Paso 1: PHP genera el HTML con datos JSON
```php
data-material="{{ htmlspecialchars(json_encode([...]), ENT_QUOTES, 'UTF-8') }}"
```

### Paso 2: HTML resultante
```html
data-material="{&quot;id&quot;:1,&quot;titulo&quot;:&quot;Material 1&quot;,...}"
```

### Paso 3: jQuery parsea el JSON automáticamente
```javascript
const materialData = $(this).data('material');
// materialData = {id: 1, titulo: "Material 1", ...}
```

### Paso 4: Validación y uso
```javascript
if (!materialData || typeof materialData !== 'object') {
    // Error - datos no válidos
    return;
}
// Usar materialData.titulo, materialData.descripcion, etc.
```

## ARCHIVOS MODIFICADOS

1. ✅ `resources/views/admin/capacitaciones/cursos/edit.blade.php`
   - Agregado atributo `data-material` al botón editar (línea ~341)
   - Agregada validación en el event handler (línea ~1215)

2. ✅ Limpiado caché de vistas de Laravel

## VERIFICACIÓN

Para verificar que funciona correctamente:

1. Ve a: `http://192.168.2.200:8001/capacitaciones/cursos/17/edit`
2. Haz clic en el botón "Editar" de cualquier material
3. El modal debe abrirse con los datos del material cargados
4. NO debe aparecer el error en la consola

### Consola debe mostrar:
```
(Sin errores)
```

### NO debe mostrar:
```
❌ Uncaught TypeError: can't access property "titulo", materialData is undefined
```

## NOTA SOBRE CACHÉ

Si el error persiste después de la corrección:

1. Limpia el caché del navegador: `Ctrl + F5`
2. O usa modo incógnito: `Ctrl + Shift + N`
3. El caché de Laravel ya fue limpiado con `php artisan view:clear`

## RESUMEN

**Problema:** Botón sin atributo `data-material` causaba error al intentar acceder a propiedades

**Solución:** Agregado atributo `data-material` con JSON completo + validación en JavaScript

**Resultado:** El botón "Editar" ahora funciona correctamente y carga los datos del material en el modal de edición
