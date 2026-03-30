# Mostrar TODOS los Materiales en Gradebook

## Problema Identificado
En la vista de Control Pedagógico (`http://192.168.2.200:8001/academico/control-pedagogico`), solo se mostraban los materiales que tenían actividades asociadas. Por ejemplo, el curso "Inducción 2026" tiene 16 materiales pero solo se veían 6 en el gradebook.

## Causa del Problema
El controlador tenía una condición que filtraba los materiales:
```php
// Solo agregar el material si tiene actividades
if (!empty($componentes)) {
    $estructura[] = [...];
}
```

Esto causaba que materiales sin actividades no aparecieran en el gradebook.

## Solución Implementada

### Se modificó para mostrar TODOS los materiales, incluso sin actividades

## Cambios Realizados

### 1. Controlador
**Archivo:** `app/Http/Controllers/ControlPedagogicoController.php`

#### Método `getEstructuraEvaluacion()` Modificado:

**ANTES:**
```php
// Solo agregar el material si tiene actividades
if (!empty($componentes)) {
    $estructura[] = [
        'tipo' => 'material',
        'id' => $material->id,
        'nombre' => $material->titulo,
        'peso' => floatval($material->porcentaje_curso ?? 0),
        'componentes' => $componentes
    ];
}
```

**AHORA:**
```php
// CAMBIO: Agregar TODOS los materiales, incluso sin actividades
$estructura[] = [
    'tipo' => 'material',
    'id' => $material->id,
    'nombre' => $material->titulo,
    'peso' => floatval($material->porcentaje_curso ?? 0),
    'componentes' => $componentes,
    'sin_actividades' => empty($componentes) // Flag para identificar materiales sin actividades
];
```

**Mejoras:**
- ✅ Eliminada la condición `if (!empty($componentes))`
- ✅ Agregado flag `sin_actividades` para identificar materiales sin actividades
- ✅ TODOS los materiales se agregan a la estructura

### 2. Vista - Fila de Materiales
**Archivo:** `resources/views/academico/control-pedagogico/index.blade.php`

#### Encabezado de Materiales Mejorado:

**Lógica Implementada:**
```blade
@if($item['tipo'] == 'material')
    @if(!empty($item['componentes']))
        {{-- Material con actividades: usa colspan --}}
        <th colspan="{{ count($item['componentes']) }}">
            <i class="fas fa-folder-open"></i> {{ $item['nombre'] }}
            <span class="badge badge-primary">{{ $item['peso'] }}%</span>
        </th>
    @else
        {{-- Material sin actividades: usa rowspan --}}
        <th rowspan="2">
            <i class="fas fa-folder"></i> {{ $item['nombre'] }}
            <span class="badge badge-secondary">{{ $item['peso'] }}%</span>
            <br><small>Sin actividades</small>
        </th>
    @endif
@endif
```

**Características:**
- ✅ Materiales con actividades: `colspan` dinámico
- ✅ Materiales sin actividades: `rowspan="2"` (abarca ambas filas)
- ✅ Icono diferente: 📁 (folder) vs 📂 (folder-open)
- ✅ Badge diferente: gris (secondary) vs azul (primary)
- ✅ Texto "Sin actividades" para claridad

### 3. Vista - Fila de Actividades

**Lógica Implementada:**
```blade
@foreach($estructuraEvaluacion as $item)
    @if($item['tipo'] == 'material' && !empty($item['componentes']))
        {{-- Solo mostrar actividades si el material las tiene --}}
        @foreach($item['componentes'] as $componente)
            <th>{{ $componente['nombre'] }}</th>
        @endforeach
    @endif
@endforeach
```

**Características:**
- ✅ Solo muestra actividades de materiales que las tienen
- ✅ Materiales sin actividades no generan columnas vacías en esta fila

### 4. Vista - Cuerpo de la Tabla (tbody)

**Lógica Implementada:**
```blade
@if($item['tipo'] == 'material')
    @if(!empty($item['componentes']))
        {{-- Material con actividades: mostrar calificaciones --}}
        @foreach($item['componentes'] as $componente)
            <td>{{ $nota }}</td>
        @endforeach
    @else
        {{-- Material sin actividades: mostrar guión --}}
        <td><i class="fas fa-minus"></i></td>
    @endif
@endif
```

**Características:**
- ✅ Materiales con actividades: muestra calificaciones normalmente
- ✅ Materiales sin actividades: muestra icono de guión (-)
- ✅ Mantiene alineación de columnas

## Visualización Resultante

### Estructura de la Tabla:

```
┌─────────────┬──────────────┬──────────────┬──────────────┬──────────────┬──────────┬────────┐
│             │ 📂 Material 1│ 📁 Material 2│ 📂 Material 3│ 📁 Material 4│          │        │
│  Estudiante │  (Con Act)   │ (Sin Act)    │  (Con Act)   │ (Sin Act)    │ Promedio │ Estado │
│             │    (30%)     │    (10%)     │    (40%)     │    (20%)     │          │        │
├─────────────┼──────┬───────┼──────────────┼──────┬───────┼──────────────┼──────────┼────────┤
│             │ Act1 │ Act2  │              │ Act3 │ Act4  │              │          │        │
│             │ (15%)│ (15%) │              │ (20%)│ (20%) │              │          │        │
├─────────────┼──────┼───────┼──────────────┼──────┼───────┼──────────────┼──────────┼────────┤
│ Juan Pérez  │ 85.0 │ 90.0  │      -       │ 78.0 │ 92.0  │      -       │   86.3   │   ✓    │
│ María López │ 78.0 │ 82.0  │      -       │ 85.0 │ 88.0  │      -       │   83.3   │   ✓    │
└─────────────┴──────┴───────┴──────────────┴──────┴───────┴──────────────┴──────────┴────────┘
```

### Leyenda:
- **📂 Material con actividades:** Icono folder-open, badge azul, colspan dinámico
- **📁 Material sin actividades:** Icono folder, badge gris, rowspan=2, texto "Sin actividades"
- **-** Celda de material sin actividades en el cuerpo

## Tipos de Materiales

### Material con Actividades:
```
┌─────────────────────────────┐
│ 📂 Preliquidación (30%)     │
├──────────────┬──────────────┤
│ Liquidar 1   │ Liquidar 2   │
│   (15%)      │   (15%)      │
└──────────────┴──────────────┘
```

### Material sin Actividades:
```
┌─────────────────────────────┐
│ 📁 Introducción (10%)       │
│    Sin actividades          │
│                             │
└─────────────────────────────┘
```

## Beneficios

### 1. Visualización Completa
- ✅ TODOS los 16 materiales visibles (no solo 6)
- ✅ Información completa del curso
- ✅ No se oculta ningún material

### 2. Claridad Visual
- ✅ Materiales sin actividades claramente identificados
- ✅ Iconos diferentes para distinguir tipos
- ✅ Badges de colores diferentes (azul vs gris)
- ✅ Texto explicativo "Sin actividades"

### 3. Estructura Correcta
- ✅ Colspan para materiales con actividades
- ✅ Rowspan para materiales sin actividades
- ✅ Alineación correcta de columnas
- ✅ No hay columnas vacías innecesarias

### 4. Scroll Horizontal
- ✅ Se activa automáticamente con muchos materiales
- ✅ Columnas sticky funcionan correctamente
- ✅ Navegación fluida

## Casos de Uso

### Caso 1: Curso con 16 Materiales (Inducción 2026)
**Antes:**
- Solo 6 materiales visibles (los que tenían actividades)
- 10 materiales ocultos

**Ahora:**
- ✅ 16 materiales visibles
- ✅ Scroll horizontal activado
- ✅ Todos los materiales accesibles

### Caso 2: Material sin Actividades
**Visualización:**
- Encabezado con rowspan=2
- Badge gris con porcentaje
- Texto "Sin actividades"
- Celda con guión (-) en el cuerpo

### Caso 3: Material con Actividades
**Visualización:**
- Encabezado con colspan según número de actividades
- Badge azul con porcentaje
- Actividades listadas en segunda fila
- Calificaciones en el cuerpo

## Scroll Horizontal

### Activación Automática:
- Se activa cuando hay muchos materiales/actividades
- Ancho de tabla: `width: max-content`
- Columnas sticky funcionan correctamente

### Navegación:
- **Columna Estudiante:** Fija a la izquierda
- **Columnas Promedio y Estado:** Fijas a la derecha
- **Materiales y Actividades:** Scroll horizontal

## Testing

### Para verificar la corrección:

1. **Acceder al Control Pedagógico:**
   ```
   http://192.168.2.200:8001/academico/control-pedagogico
   ```

2. **Seleccionar curso "Inducción 2026":**
   - ✅ Verificar que se muestren los 16 materiales
   - ✅ Verificar scroll horizontal activado
   - ✅ Verificar materiales sin actividades con badge gris

3. **Verificar Estructura:**
   - ✅ Materiales con actividades usan colspan
   - ✅ Materiales sin actividades usan rowspan
   - ✅ Alineación correcta de columnas
   - ✅ No hay columnas vacías

4. **Verificar Calificaciones:**
   - ✅ Materiales con actividades muestran notas
   - ✅ Materiales sin actividades muestran guión (-)
   - ✅ Promedio se calcula correctamente

5. **Verificar Scroll:**
   - ✅ Scroll horizontal funciona
   - ✅ Columnas sticky permanecen fijas
   - ✅ Headers sticky permanecen visibles

## Archivos Modificados

1. ✅ `app/Http/Controllers/ControlPedagogicoController.php`
   - Eliminada condición que filtraba materiales
   - Agregado flag `sin_actividades`
   - TODOS los materiales se incluyen en la estructura

2. ✅ `resources/views/academico/control-pedagogico/index.blade.php`
   - Lógica para materiales con/sin actividades
   - Colspan dinámico para materiales con actividades
   - Rowspan para materiales sin actividades
   - Iconos y badges diferenciados
   - Celdas con guión para materiales sin actividades

## Conclusión

El Libro de Calificaciones ahora muestra TODOS los materiales del curso:
- ✅ 16/16 materiales visibles (no 6/16)
- ✅ Materiales sin actividades claramente identificados
- ✅ Scroll horizontal activado automáticamente
- ✅ Estructura correcta con colspan/rowspan
- ✅ Navegación fluida con columnas sticky
- ✅ Información completa del curso

**Estado:** ✅ COMPLETADO
**Fecha:** 19 de Enero, 2026
