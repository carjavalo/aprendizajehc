# ✅ CORRECCIÓN: Error "Attempt to read property 'pivot' on null"

**Fecha:** 22 de enero de 2026  
**Estado:** CORREGIDO

---

## 🐛 PROBLEMA DETECTADO

### Error en Vista de Cursos Disponibles
```
DataTables warning: table id=cursosTable - Exception Message:
Attempt to read property "pivot" on null
```

**Causa:**
El código intentaba acceder a la propiedad `pivot` de una relación que era null. Esto ocurría porque:

1. El método `tieneEstudiante()` devuelve `true` si el usuario tiene:
   - Inscripción en `curso_estudiantes` (con pivot), O
   - Asignación activa en `curso_asignaciones` (sin pivot)

2. El código asumía que si `tieneEstudiante()` era true, siempre existía un pivot
3. Cuando el usuario solo tenía asignación (sin inscripción), el pivot era null

---

## 🔧 CORRECCIONES APLICADAS

### 1. Columna estado_inscripcion
**Archivo:** `app/Http/Controllers/AcademicoController.php`

**ANTES:**
```php
if ($curso->tieneEstudiante($user->id)) {
    $estudiante = $curso->estudiantes()->where('users.id', $user->id)->first();
    return $estudiante->pivot->estado ?? 'inscrito'; // ❌ Error si pivot es null
}
return 'no_inscrito';
```

**DESPUÉS:**
```php
// Verificar si está inscrito (tiene registro en curso_estudiantes)
$estudiante = $curso->estudiantes()->where('users.id', $user->id)->first();
if ($estudiante && $estudiante->pivot) {
    return $estudiante->pivot->estado ?? 'inscrito';
}

// Si no está inscrito pero tiene asignación activa
$tieneAsignacion = \App\Models\CursoAsignacion::where('curso_id', $curso->id)
    ->where('estudiante_id', $user->id)
    ->activas()
    ->exists();

if ($tieneAsignacion) {
    return 'no_inscrito'; // Asignado pero no inscrito
}

return 'sin_acceso';
```

### 2. Columna fecha_inscripcion
**ANTES:**
```php
if ($curso->tieneEstudiante($user->id)) {
    $estudiante = $curso->estudiantes()->where('users.id', $user->id)->first();
    return $estudiante->pivot->created_at->format('d/m/Y'); // ❌ Error si pivot es null
}
return '-';
```

**DESPUÉS:**
```php
// Verificar si está inscrito
$estudiante = $curso->estudiantes()->where('users.id', $user->id)->first();
if ($estudiante && $estudiante->pivot && $estudiante->pivot->created_at) {
    return $estudiante->pivot->created_at->format('d/m/Y');
}

// Si tiene asignación, mostrar fecha de asignación
$asignacion = \App\Models\CursoAsignacion::where('curso_id', $curso->id)
    ->where('estudiante_id', $user->id)
    ->activas()
    ->first();

if ($asignacion) {
    return $asignacion->fecha_asignacion->format('d/m/Y');
}

return '-';
```

### 3. Columna acciones
**ANTES:**
```php
$isEnrolled = $curso->tieneEstudiante($user->id); // ❌ Incluye asignaciones
```

**DESPUÉS:**
```php
// Verificar si está inscrito (no solo asignado)
$isEnrolled = $curso->estudiantes()->where('users.id', $user->id)->exists();
```

### 4. Render de Estado en Vista
**Archivo:** `resources/views/academico/cursos-disponibles/index.blade.php`

**ANTES:**
```javascript
render: function(data, type, row) {
    let badgeClass = data === 'inscrito' ? 'badge-success' : 'badge-warning';
    let text = data === 'inscrito' ? 'Inscrito' : 'Pendiente';
    return '<span class="badge ' + badgeClass + ' badge-estado">' + text + '</span>';
}
```

**DESPUÉS:**
```javascript
render: function(data, type, row) {
    if (data === 'inscrito') {
        return '<span class="badge badge-success badge-estado">Inscrito</span>';
    } else if (data === 'no_inscrito') {
        return '<span class="badge badge-warning badge-estado">Pendiente</span>';
    } else if (data === 'acceso_directo') {
        return '<span class="badge badge-info badge-estado">Acceso Directo</span>';
    } else {
        return '<span class="badge badge-secondary badge-estado">Sin Acceso</span>';
    }
}
```

---

## ✅ ESTADOS CLARIFICADOS

### Estado: "inscrito"
- ✅ Tiene registro en `curso_estudiantes`
- ✅ Tiene pivot con datos de inscripción
- **Badge:** Verde "Inscrito"
- **Botones:** Ver, Aula Virtual, Acceder
- **Fecha:** Fecha de inscripción

### Estado: "no_inscrito"
- ✅ Tiene asignación activa en `curso_asignaciones`
- ❌ NO tiene registro en `curso_estudiantes`
- ❌ NO tiene pivot
- **Badge:** Amarillo "Pendiente"
- **Botones:** Ver, Inscribirse
- **Fecha:** Fecha de asignación

### Estado: "acceso_directo"
- ✅ Usuario con rol privilegiado (Admin, Super Admin, Operador)
- **Badge:** Azul "Acceso Directo"
- **Botones:** Ver, Aula Virtual, Ejecutar
- **Fecha:** -

### Estado: "sin_acceso"
- ❌ NO tiene asignación
- ❌ NO tiene inscripción
- **Badge:** Gris "Sin Acceso"
- **Botones:** Ninguno
- **Fecha:** -

---

## 🔄 FLUJO CORRECTO

### 1. Usuario se Registra
```
curso_asignaciones:
  curso_id: 18
  estudiante_id: [user_id]
  estado: 'activo'
  
curso_estudiantes:
  (vacío - no inscrito aún)
```

**Vista cursos disponibles:**
- ✅ Aparece curso ID 18
- 🟡 Estado: "Pendiente"
- 📅 Fecha: Fecha de asignación
- 🔘 Botón: "Inscribirse"

### 2. Usuario se Inscribe
```
curso_asignaciones:
  (sin cambios)
  
curso_estudiantes:
  curso_id: 18
  estudiante_id: [user_id]
  estado: 'activo'
  progreso: 0
```

**Vista cursos disponibles:**
- ✅ Aparece curso ID 18
- 🟢 Estado: "Inscrito"
- 📅 Fecha: Fecha de inscripción
- 🔘 Botones: "Ver", "Aula Virtual", "Acceder"

---

## 🧪 PRUEBAS

### Prueba 1: Usuario con Asignación (Sin Inscripción)
```bash
php diagnostico_cursos_disponibles.php
```

**Resultado esperado:**
- ✅ Curso aparece en la lista
- ✅ Estado: "Pendiente"
- ✅ Fecha: Fecha de asignación
- ✅ Botón: "Inscribirse"
- ✅ NO hay error de pivot

### Prueba 2: Usuario Inscrito
```bash
# Inscribir usuario al curso
# Luego verificar vista
```

**Resultado esperado:**
- ✅ Curso aparece en la lista
- ✅ Estado: "Inscrito"
- ✅ Fecha: Fecha de inscripción
- ✅ Botones: "Ver", "Aula Virtual", "Acceder"
- ✅ NO hay error de pivot

### Prueba 3: Admin/Super Admin
**Resultado esperado:**
- ✅ Ve TODOS los cursos activos
- ✅ Estado: "Acceso Directo"
- ✅ Botones: "Ver", "Aula Virtual", "Ejecutar"
- ✅ NO necesita asignación ni inscripción

---

## 📊 DIFERENCIAS CLAVE

### curso_asignaciones (Asignación)
**Propósito:** Dar permiso para VER el curso

**Características:**
- Creada por sistema o administrador
- Permite ver el curso en cursos disponibles
- NO permite acceder al contenido
- NO tiene pivot (tabla independiente)

### curso_estudiantes (Inscripción)
**Propósito:** Registrar participación ACTIVA en el curso

**Características:**
- Creada cuando el usuario se inscribe
- Permite acceder al contenido del curso
- Registra progreso y actividad
- SÍ tiene pivot (tabla pivote many-to-many)

---

## 🎯 LÓGICA DE VERIFICACIÓN

### Verificar Inscripción (con pivot)
```php
$estudiante = $curso->estudiantes()->where('users.id', $user->id)->first();
if ($estudiante && $estudiante->pivot) {
    // Usuario INSCRITO - tiene pivot
}
```

### Verificar Asignación (sin pivot)
```php
$asignacion = CursoAsignacion::where('curso_id', $curso->id)
    ->where('estudiante_id', $user->id)
    ->activas()
    ->first();
    
if ($asignacion) {
    // Usuario ASIGNADO - no tiene pivot
}
```

### Verificar Ambos (método tieneEstudiante)
```php
// ⚠️ CUIDADO: Este método devuelve true para AMBOS casos
if ($curso->tieneEstudiante($user->id)) {
    // Puede ser inscrito O asignado
    // NO asumir que existe pivot
}
```

---

## ✅ RESULTADO FINAL

### Error Corregido
- ✅ NO más error "Attempt to read property 'pivot' on null"
- ✅ Vista de cursos disponibles funciona correctamente
- ✅ Usuarios con asignación ven sus cursos
- ✅ Usuarios inscritos ven sus cursos
- ✅ Estados claramente diferenciados

### Funcionalidad Mejorada
- ✅ Distinción clara entre asignado e inscrito
- ✅ Fechas correctas según el estado
- ✅ Botones apropiados según el estado
- ✅ Badges con colores significativos

---

## 📞 SOPORTE

Para cualquier problema:
- **Email:** oficinacoordinadoraacademica@correohuv.gov.co
- **Ubicación:** Hospital Universitario del Valle, Séptimo piso

---

**Documento generado:** 22 de enero de 2026  
**Versión:** 1.0  
**Estado:** Error corregido - Sistema funcional
