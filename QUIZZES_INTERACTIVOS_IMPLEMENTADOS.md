# Implementación de Calificación Interactiva en Control Pedagógico

## Fecha: 19 de Enero de 2026

## Cambios Realizados

### 1. Controlador: `app/Http/Controllers/ControlPedagogicoController.php`

#### Métodos AJAX Agregados:

**a) `handleAjaxRequest(Request $request)`**
- Maneja todas las peticiones AJAX del gradebook
- Enruta a los métodos específicos según el parámetro `action`

**b) `getEntrega(Request $request)`**
- Obtiene los detalles de la entrega de un estudiante
- Parámetros: `curso_id`, `estudiante_id`, `actividad_id`
- Retorna:
  - Para **Quiz/Evaluación**: 
    - Calificación automática (escala 0-5)
    - Respuestas formateadas con pregunta, respuesta, si es correcta y puntos
  - Para **Tarea**:
    - Archivo entregado (path)
    - Comentario del estudiante
    - Fecha de entrega
    - Calificación actual (si existe)
    - Retroalimentación del instructor (si existe)

**c) `guardarCalificacion(Request $request)`**
- Guarda la calificación manual de una tarea
- Parámetros: `curso_id`, `estudiante_id`, `actividad_id`, `calificacion`, `retroalimentacion`
- Validaciones:
  - Calificación entre 0 y 5
  - Entrega debe existir
- Actualiza:
  - `calificacion`: Nota asignada
  - `comentarios_instructor`: Retroalimentación
  - `estado`: Cambia a 'revisado'
  - `revisado_at`: Timestamp de revisión

### 2. Modelo: `app/Models/CursoActividad.php`

**Corrección en `calcularNotaEstudiante()`:**
- Ahora busca las respuestas en el campo `contenido` (JSON) en lugar de `respuestas_json`
- Decodifica el JSON y extrae el array `respuestas`
- Calcula la nota usando el método `calcularNotaQuiz()`

### 3. Vista: `resources/views/academico/control-pedagogico/index.blade.php`

#### Celdas Interactivas:
- Todas las celdas de calificaciones ahora tienen:
  - `cursor: pointer`
  - Efecto hover con fondo azul claro y escala 1.05
  - Icono de edición en hover
  - Data attributes: `estudiante-id`, `actividad-id`, `actividad-tipo`, `actividad-nombre`, `estudiante-nombre`
  - Evento `onclick="abrirDetalleCalificacion(this)"`

#### JavaScript Implementado:

**a) `abrirDetalleCalificacion(cell)`**
- Extrae los datos de la celda clickeada
- Muestra loading con SweetAlert2
- Hace petición AJAX GET con `action=get_entrega`
- Llama a `mostrarModalCalificacion()` con la respuesta

**b) `mostrarModalCalificacion(entrega, datos)`**
- Construye el HTML del modal según el tipo de actividad
- **Para Quiz/Evaluación:**
  - Muestra calificación automática (0-5 y porcentaje)
  - Lista de respuestas con indicador visual (verde=correcta, rojo=incorrecta)
  - Puntos obtenidos por pregunta
  - Botón "Cerrar" (solo lectura)
- **Para Tarea:**
  - Botón para descargar archivo entregado
  - Comentario del estudiante
  - Fecha de entrega
  - Formulario de calificación:
    - Input numérico (0-5, step 0.1)
    - Textarea para retroalimentación
  - Botón "Guardar Calificación"
  - Validaciones en `preConfirm`

**c) `formatearRespuestas(respuestas)`**
- Formatea las respuestas de quiz para visualización
- Maneja diferentes formatos de datos
- Muestra:
  - Número de pregunta
  - Texto de la pregunta
  - Respuesta del estudiante
  - Icono de correcto/incorrecto
  - Puntos obtenidos
- Usa clases Bootstrap: `list-group-item-success` / `list-group-item-danger`

**d) `guardarCalificacion(datos, valores)`**
- Muestra loading
- Hace petición AJAX POST con `action=guardar_calificacion`
- Envía: `calificacion`, `retroalimentacion`, `curso_id`, `estudiante_id`, `actividad_id`
- Al éxito: Muestra mensaje y recarga la página para actualizar el gradebook

### 4. Estilos CSS Agregados:

```css
.grade-cell-interactive:hover {
    background: #e3f2fd !important;
    transform: scale(1.05);
    transition: all 0.2s ease;
}

.grade-cell-interactive {
    position: relative;
}

.grade-cell-interactive:hover::after {
    content: '\f044'; /* fa-edit */
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    top: 2px;
    right: 2px;
    font-size: 0.7rem;
    color: var(--corp-primary);
}
```

## Flujo de Funcionamiento

### Para Quiz/Evaluación (Calificación Automática):
1. Usuario hace click en celda de calificación
2. Sistema obtiene la entrega del estudiante
3. Calcula la nota automáticamente usando `calcularNotaEstudiante()`
4. Muestra modal con:
   - Nota final (0-5 y porcentaje)
   - Lista de respuestas con indicadores visuales
   - Puntos por pregunta
5. Usuario solo puede cerrar el modal (no editar)

### Para Tarea (Calificación Manual):
1. Usuario hace click en celda de calificación
2. Sistema obtiene la entrega del estudiante
3. Muestra modal con:
   - Archivo entregado (botón de descarga)
   - Comentario del estudiante
   - Fecha de entrega
   - Formulario de calificación
4. Usuario ingresa:
   - Calificación (0-5)
   - Retroalimentación (opcional)
5. Sistema valida y guarda en base de datos
6. Actualiza estado a 'revisado'
7. Recarga página para mostrar nueva calificación

## Estructura de Datos

### Tabla: `curso_actividad_entrega`

**Campos utilizados:**
- `id`: ID único
- `curso_id`: ID del curso
- `actividad_id`: ID de la actividad
- `user_id`: ID del estudiante
- `contenido`: JSON con respuestas (quiz) o texto (tarea)
- `archivo_path`: Ruta del archivo entregado (tareas)
- `calificacion`: Nota (0-5)
- `comentarios_instructor`: Retroalimentación
- `estado`: 'entregado', 'revisado', 'aprobado', 'rechazado'
- `entregado_at`: Timestamp de entrega
- `revisado_at`: Timestamp de revisión

**Formato del campo `contenido` para Quiz:**
```json
{
  "respuestas": {...},
  "resultados": [
    {
      "pregunta": "¿Texto de la pregunta?",
      "respuesta_estudiante": "Respuesta del estudiante",
      "respuesta_correcta": "Respuesta correcta",
      "es_correcta": true,
      "puntos": 1.0
    }
  ],
  "tiempo_transcurrido": 300
}
```

**Formato del campo `contenido` para Tarea:**
```
Texto simple con el comentario del estudiante
```

## Validaciones Implementadas

1. **Calificación:**
   - Debe estar entre 0.0 y 5.0
   - Campo requerido para guardar
   - Step de 0.1 para precisión

2. **Entrega:**
   - Debe existir en la base de datos
   - Si no existe, muestra mensaje de advertencia

3. **Tipo de Actividad:**
   - Quiz/Evaluación: Solo lectura (calificación automática)
   - Tarea: Permite edición manual

## Colores Corporativos Aplicados

- Primary: `#2c4370`
- Hover: `#e3f2fd` (azul claro)
- Success: `#27ae60` (verde)
- Warning: `#f39c12` (amarillo)
- Danger: `#e74c3c` (rojo)

## Próximos Pasos (Opcional)

1. Agregar filtros por estado (entregado, revisado, etc.)
2. Permitir exportar calificaciones a Excel/PDF
3. Agregar notificaciones al estudiante cuando se califica
4. Implementar historial de cambios en calificaciones
5. Agregar comentarios múltiples (conversación)
6. Permitir rúbricas de evaluación personalizadas

## Notas Técnicas

- Las calificaciones se manejan en escala 0-5 en base de datos
- Se convierten a 0-100 para visualización en el gradebook
- Los quiz calculan la nota sumando los puntos de cada pregunta
- Las tareas requieren calificación manual del instructor
- El sistema recarga la página después de guardar para actualizar todas las calificaciones
