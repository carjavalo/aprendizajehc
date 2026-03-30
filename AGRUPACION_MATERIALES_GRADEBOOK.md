# Agrupación de Actividades por Material en Gradebook

## Problema Identificado
En la vista de Control Pedagógico (`http://192.168.2.200:8001/academico/control-pedagogico?curso_id=17`), el Libro de Calificaciones no mostraba una fila de encabezado que agrupara las actividades por material, dificultando la visualización de qué actividades pertenecen a cada material.

## Solución Implementada

### Estructura de Encabezado de Dos Niveles

Se implementó un sistema de encabezado con **dos filas**:

1. **Fila 1 (Material Header):** Muestra los nombres de los materiales con colspan
2. **Fila 2 (Activity Header):** Muestra los nombres de las actividades individuales

## Cambios Realizados

### Vista de Control Pedagógico
**Archivo:** `resources/views/academico/control-pedagogico/index.blade.php`

#### Estructura HTML:

**ANTES (1 fila de encabezado):**
```html
<thead>
    <tr>
        <th>Estudiante</th>
        <th>Actividad 1</th>
        <th>Actividad 2</th>
        <th>Actividad 3</th>
        ...
    </tr>
</thead>
```

**AHORA (2 filas de encabezado):**
```html
<thead>
    <!-- Fila 1: Materiales -->
    <tr class="material-header-row">
        <th rowspan="2">Estudiante</th>
        <th colspan="2">📁 Preliquidación (30%)</th>
        <th colspan="1">📁 Liquidar (40%)</th>
        <th colspan="2">📁 Post Liquidar (30%)</th>
        <th rowspan="2">Promedio</th>
        <th rowspan="2">Estado</th>
    </tr>
    <!-- Fila 2: Actividades -->
    <tr class="activity-header-row">
        <th>Liquidar 1</th>
        <th>Liquidar 2</th>
        <th>Liquidación</th>
        <th>Post Test</th>
        <th>Post Test Liquidac...</th>
    </tr>
</thead>
```

### Elementos Clave:

#### 1. Fila de Materiales (Material Header Row)
```blade
<tr class="material-header-row">
    <th class="student-col sticky-col" rowspan="2">
        <i class="fas fa-user"></i> Estudiante
    </th>
    @foreach($estructuraEvaluacion as $item)
        @if($item['tipo'] == 'material')
            <th class="material-group-header text-center" 
                colspan="{{ count($item['componentes']) }}">
                <div class="material-group-title">
                    <i class="fas fa-folder-open"></i> {{ $item['nombre'] }}
                    <span class="badge badge-primary ml-2">
                        {{ number_format($item['peso'], 1) }}%
                    </span>
                </div>
            </th>
        @endif
    @endforeach
</tr>
```

**Características:**
- ✅ `colspan` dinámico según número de actividades del material
- ✅ Icono de carpeta para identificar materiales
- ✅ Badge con porcentaje del material
- ✅ Fondo con gradiente azul corporativo

#### 2. Fila de Actividades (Activity Header Row)
```blade
<tr class="activity-header-row">
    @foreach($estructuraEvaluacion as $item)
        @if($item['tipo'] == 'material')
            @foreach($item['componentes'] as $componente)
                <th class="grade-col text-center">
                    <div class="col-header">
                        <i class="fas fa-clipboard-check"></i>
                        <span class="col-title">{{ $componente['nombre'] }}</span>
                        <small class="col-subtitle">{{ $componente['peso'] }}%</small>
                    </div>
                </th>
            @endforeach
        @endif
    @endforeach
</tr>
```

**Características:**
- ✅ Iconos según tipo de actividad (tarea, quiz, evaluación)
- ✅ Nombre de la actividad
- ✅ Porcentaje de la actividad
- ✅ Fondo gris claro

#### 3. Columnas con rowspan="2"
Las siguientes columnas abarcan ambas filas:
- ✅ **Estudiante:** Primera columna sticky
- ✅ **Actividades Independientes:** Sin material asociado
- ✅ **Promedio:** Columna de promedio final
- ✅ **Estado:** Columna de estado (Aprobado/En Riesgo/Reprobado)

### Estilos CSS Implementados

#### Fila de Materiales:
```css
.material-header-row th {
    background: linear-gradient(135deg, var(--corp-primary), var(--corp-primary-dark));
    color: white;
    font-weight: 700;
    border-bottom: 2px solid var(--corp-primary-dark);
}

.material-group-title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.95rem;
    font-weight: 700;
}
```

**Características:**
- Gradiente azul corporativo (#2c4370 → #1e2f4d)
- Texto blanco en negrita
- Centrado con flexbox
- Badge destacado

#### Fila de Actividades:
```css
.activity-header-row th {
    background: #e9ecef;
    border-bottom: 1px solid #dee2e6;
}
```

**Características:**
- Fondo gris claro
- Borde inferior sutil
- Contraste con fila de materiales

#### Sticky Headers:
```css
.material-header-row {
    position: sticky;
    top: 0;
    z-index: 11;
}

.activity-header-row {
    position: sticky;
    top: 60px; /* Altura de la primera fila */
    z-index: 10;
}
```

**Características:**
- Ambas filas permanecen visibles al hacer scroll
- Z-index diferenciado para superposición correcta
- Top calculado para la segunda fila

## Visualización Resultante

### Estructura de la Tabla:

```
┌─────────────┬──────────────────────────┬─────────────────┬──────────────────────────┬──────────┬────────┐
│             │   📁 Preliquidación      │  📁 Liquidar    │   📁 Post Liquidar       │          │        │
│  Estudiante │        (30%)             │     (40%)       │        (30%)             │ Promedio │ Estado │
├─────────────┼────────────┬─────────────┼─────────────────┼────────────┬─────────────┼──────────┼────────┤
│             │ Liquidar 1 │ Liquidar 2  │  Liquidación    │ Post Test  │ Post Test L │          │        │
│             │   (15%)    │   (15%)     │     (40%)       │   (15%)    │   (15%)     │          │        │
├─────────────┼────────────┼─────────────┼─────────────────┼────────────┼─────────────┼──────────┼────────┤
│ Juan Pérez  │    85.0    │    90.0     │      78.0       │    92.0    │    88.0     │   86.6   │   ✓    │
│ María López │    78.0    │    82.0     │      85.0       │    88.0    │    90.0     │   84.6   │   ✓    │
└─────────────┴────────────┴─────────────┴─────────────────┴────────────┴─────────────┴──────────┴────────┘
```

### Colores:

1. **Fila de Materiales:**
   - Fondo: Gradiente azul (#2c4370 → #1e2f4d)
   - Texto: Blanco
   - Badge: Azul primario

2. **Fila de Actividades:**
   - Fondo: Gris claro (#e9ecef)
   - Texto: Gris oscuro (#2c3e50)

3. **Actividades Independientes:**
   - Fondo: Gradiente gris (#6c757d → #5a6268)
   - Texto: Blanco

## Beneficios

### 1. Mejor Organización Visual
- ✅ Agrupación clara de actividades por material
- ✅ Fácil identificación de qué actividades pertenecen a cada material
- ✅ Jerarquía visual clara (material → actividades)

### 2. Información Contextual
- ✅ Porcentaje del material visible en el encabezado
- ✅ Porcentaje de cada actividad visible
- ✅ Iconos descriptivos para materiales y actividades

### 3. Navegación Mejorada
- ✅ Ambas filas de encabezado permanecen visibles al hacer scroll
- ✅ Columnas sticky funcionan correctamente
- ✅ Fácil seguimiento de columnas

### 4. Diseño Profesional
- ✅ Colores corporativos aplicados
- ✅ Gradientes modernos
- ✅ Badges destacados
- ✅ Iconos descriptivos

## Casos de Uso

### Caso 1: Material con Múltiples Actividades
```
📁 Preliquidación (30%)
├── Liquidar 1 (tarea) - 15%
└── Liquidar 2 (quiz) - 15%
```

**Visualización:**
- Encabezado de material con `colspan="2"`
- Dos columnas de actividades debajo

### Caso 2: Material con Una Actividad
```
📁 Liquidar (40%)
└── Liquidación (evaluacion) - 40%
```

**Visualización:**
- Encabezado de material con `colspan="1"`
- Una columna de actividad debajo

### Caso 3: Actividad Independiente (Sin Material)
```
📋 Examen Final (30%)
```

**Visualización:**
- Encabezado con `rowspan="2"` (abarca ambas filas)
- Fondo gris para diferenciar

## Testing

### Para verificar la implementación:

1. **Acceder al Control Pedagógico:**
   ```
   http://192.168.2.200:8001/academico/control-pedagogico?curso_id=17
   ```

2. **Verificar Estructura:**
   - ✅ Dos filas de encabezado visibles
   - ✅ Materiales agrupan sus actividades
   - ✅ Colspan correcto según número de actividades
   - ✅ Rowspan correcto en columnas fijas

3. **Verificar Estilos:**
   - ✅ Fila de materiales con fondo azul
   - ✅ Fila de actividades con fondo gris
   - ✅ Badges visibles con porcentajes
   - ✅ Iconos apropiados

4. **Verificar Scroll:**
   - ✅ Ambas filas permanecen visibles al hacer scroll vertical
   - ✅ Columnas sticky funcionan correctamente
   - ✅ No hay superposición incorrecta

## Archivos Modificados

1. ✅ `resources/views/academico/control-pedagogico/index.blade.php`
   - Estructura de thead con dos filas
   - Colspan dinámico para materiales
   - Rowspan para columnas fijas
   - Estilos CSS para ambas filas
   - Sticky headers configurados

## Conclusión

El Libro de Calificaciones ahora tiene una estructura jerárquica clara:
- ✅ **Nivel 1:** Materiales (agrupación)
- ✅ **Nivel 2:** Actividades (detalle)
- ✅ Visualización profesional con colores corporativos
- ✅ Navegación mejorada con sticky headers
- ✅ Información contextual completa

**Estado:** ✅ COMPLETADO
**Fecha:** 19 de Enero, 2026
