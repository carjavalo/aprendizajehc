# Corrección Error 403 - Marcar Material como Visto

## Problema Identificado
Al intentar marcar un material como visto en la ruta:
```
POST http://192.168.2.200:8001/academico/curso/17/marcar-material/66
```

Se recibía un error **403 Forbidden**, impidiendo que los estudiantes marquen materiales como vistos.

## Causa del Problema

### Método `tieneEstudiante()` Incompleto

El método en el modelo `Curso` solo verificaba inscripciones en la tabla `curso_estudiantes`:

```php
public function tieneEstudiante($userId): bool
{
    return $this->estudiantes()->wherePivot('estudiante_id', $userId)->exists();
}
```

**Problema:**
- Solo verifica tabla `curso_estudiantes` (inscripción directa)
- NO verifica tabla `curso_asignaciones` (asignación por admin)
- Usuarios asignados por admin recibían 403

### Flujo del Error:

1. Usuario asignado al curso por admin (tabla `curso_asignaciones`)
2. Usuario intenta marcar material como visto
3. Controlador verifica: `$curso->tieneEstudiante($user->id)`
4. Método solo busca en `curso_estudiantes`
5. No encuentra al usuario
6. Retorna 403: "No tienes acceso a este curso"

## Solución Implementada

### Método `tieneEstudiante()` Mejorado

**Archivo:** `app/Models/Curso.php`

**ANTES:**
```php
public function tieneEstudiante($userId): bool
{
    return $this->estudiantes()->wherePivot('estudiante_id', $userId)->exists();
}
```

**AHORA:**
```php
public function tieneEstudiante($userId): bool
{
    // Verificar en curso_estudiantes (inscripción directa)
    $inscritoDirecto = $this->estudiantes()
        ->wherePivot('estudiante_id', $userId)
        ->exists();
    
    if ($inscritoDirecto) {
        return true;
    }
    
    // Verificar en curso_asignaciones (asignación por admin)
    $asignado = DB::table('curso_asignaciones')
        ->where('curso_id', $this->id)
        ->where('estudiante_id', $userId)
        ->where('estado', 'activo')
        ->exists();
    
    return $asignado;
}
```

**Mejoras:**
- ✅ Verifica AMBAS tablas de inscripción
- ✅ Primero busca en `curso_estudiantes` (inscripción directa)
- ✅ Si no encuentra, busca en `curso_asignaciones` (asignación admin)
- ✅ Solo considera asignaciones activas
- ✅ Retorna true si encuentra en cualquiera de las dos

## Tablas de Inscripción

### 1. curso_estudiantes
**Uso:** Inscripción directa del estudiante
**Campos:**
- `curso_id`
- `estudiante_id`
- `estado`
- `progreso`
- `fecha_inscripcion`

**Cómo se crea:**
- Estudiante se inscribe por código de acceso
- Estudiante se inscribe desde "Cursos Disponibles"

### 2. curso_asignaciones
**Uso:** Asignación por administrador
**Campos:**
- `curso_id`
- `estudiante_id`
- `estado`
- `fecha_asignacion`
- `fecha_expiracion`
- `asignado_por`

**Cómo se crea:**
- Admin asigna curso desde "Asignación de Cursos"
- Admin asigna curso masivamente
- Sistema asigna automáticamente

## Flujo Corregido

### Escenario 1: Inscripción Directa
1. Usuario se inscribe al curso
2. Registro en `curso_estudiantes`
3. `tieneEstudiante()` encuentra en primera verificación
4. ✅ Puede marcar materiales como vistos

### Escenario 2: Asignación por Admin
1. Admin asigna curso al usuario
2. Registro en `curso_asignaciones`
3. `tieneEstudiante()` no encuentra en `curso_estudiantes`
4. `tieneEstudiante()` encuentra en `curso_asignaciones`
5. ✅ Puede marcar materiales como vistos

### Escenario 3: Sin Acceso
1. Usuario no inscrito ni asignado
2. `tieneEstudiante()` no encuentra en ninguna tabla
3. ❌ Retorna 403: "No tienes acceso a este curso"

## Validaciones Adicionales

### En el Controlador:
```php
public function marcarMaterialVisto(Request $request, Curso $curso, CursoMaterial $material)
{
    $user = Auth::user();
    
    // Verificación de acceso al curso
    if (!$curso->tieneEstudiante($user->id)) {
        return response()->json([
            'success' => false, 
            'message' => 'No tienes acceso a este curso'
        ], 403);
    }
    
    // Verificación de prerrequisitos
    if ($material->prerequisite_id) {
        // ... validación de prerrequisito
    }
    
    // Marcar como visto
    // ...
}
```

**Validaciones:**
1. ✅ Usuario autenticado
2. ✅ Usuario tiene acceso al curso (inscrito o asignado)
3. ✅ Material no tiene prerrequisito pendiente
4. ✅ Registro de visualización

## Beneficios de la Corrección

### 1. Acceso Unificado
- ✅ Usuarios inscritos directamente pueden marcar materiales
- ✅ Usuarios asignados por admin pueden marcar materiales
- ✅ Ambos tipos de inscripción funcionan igual

### 2. Flexibilidad
- ✅ Soporta múltiples formas de inscripción
- ✅ Compatible con sistema de asignaciones
- ✅ Escalable para futuros tipos de inscripción

### 3. Seguridad Mantenida
- ✅ Solo usuarios con acceso real pueden marcar materiales
- ✅ Verifica estado activo en asignaciones
- ✅ Mantiene validación de prerrequisitos

### 4. Experiencia de Usuario
- ✅ Sin errores 403 inesperados
- ✅ Funcionalidad consistente
- ✅ Progreso se registra correctamente

## Testing

### Para verificar la corrección:

1. **Usuario con Inscripción Directa:**
   ```
   - Inscribirse al curso desde "Cursos Disponibles"
   - Acceder a materiales
   - Intentar marcar como visto
   - ✅ Debe funcionar sin error 403
   ```

2. **Usuario con Asignación por Admin:**
   ```
   - Admin asigna curso al usuario
   - Usuario accede al curso
   - Intentar marcar material como visto
   - ✅ Debe funcionar sin error 403
   ```

3. **Usuario Sin Acceso:**
   ```
   - Usuario no inscrito ni asignado
   - Intentar acceder al curso
   - ❌ Debe recibir 403 correctamente
   ```

4. **Verificar en Base de Datos:**
   ```sql
   -- Verificar inscripción directa
   SELECT * FROM curso_estudiantes 
   WHERE curso_id = 17 AND estudiante_id = [USER_ID];
   
   -- Verificar asignación
   SELECT * FROM curso_asignaciones 
   WHERE curso_id = 17 AND estudiante_id = [USER_ID] AND estado = 'activo';
   
   -- Verificar material visto
   SELECT * FROM curso_material_visto 
   WHERE curso_id = 17 AND material_id = 66 AND user_id = [USER_ID];
   ```

## Otros Métodos que Usan `tieneEstudiante()`

La corrección beneficia a todos los métodos que verifican acceso al curso:

1. ✅ `marcarMaterialVisto()` - Marcar material como visto
2. ✅ `entregarActividad()` - Entregar actividades
3. ✅ `resolverQuiz()` - Resolver quizzes
4. ✅ `verCurso()` - Ver contenido del curso
5. ✅ `aulaVirtual()` - Acceder al aula virtual

## Archivos Modificados

1. ✅ `app/Models/Curso.php`
   - Método `tieneEstudiante()` mejorado
   - Verifica ambas tablas de inscripción
   - Considera estado activo en asignaciones

## Mejores Prácticas Aplicadas

### 1. Verificación Múltiple:
```php
// ✅ Bueno - Verifica múltiples fuentes
$inscritoDirecto = $this->estudiantes()->exists();
$asignado = DB::table('curso_asignaciones')->exists();
return $inscritoDirecto || $asignado;

// ❌ Evitar - Solo una fuente
return $this->estudiantes()->exists();
```

### 2. Early Return:
```php
// ✅ Bueno - Return temprano si encuentra
if ($inscritoDirecto) {
    return true;
}
// Continuar buscando...

// ❌ Evitar - Verificar todo siempre
$inscrito = $this->estudiantes()->exists();
$asignado = DB::table('curso_asignaciones')->exists();
return $inscrito || $asignado;
```

### 3. Estado Activo:
```php
// ✅ Bueno - Solo asignaciones activas
->where('estado', 'activo')

// ❌ Evitar - Cualquier estado
// Sin filtro de estado
```

## Conclusión

El error 403 al marcar materiales como vistos ha sido corregido:
- ✅ Método `tieneEstudiante()` verifica ambas tablas
- ✅ Usuarios inscritos directamente funcionan
- ✅ Usuarios asignados por admin funcionan
- ✅ Seguridad mantenida
- ✅ Experiencia de usuario mejorada

**Estado:** ✅ COMPLETADO
**Fecha:** 19 de Enero, 2026
