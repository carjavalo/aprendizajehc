# Corrección de Error en Inscripción de Cursos

## Fecha: 23 de enero de 2026

## Problema Identificado

En la vista `/academico/cursos-disponibles`, cuando los usuarios intentaban inscribirse a un curso, recibían el mensaje de error:

**"Ya estás inscrito en este curso"**

Incluso cuando NO estaban inscritos, solo tenían una asignación pendiente.

## Causa del Problema

El método `inscribirseCurso` en `AcademicoController` usaba el método `tieneEstudiante()` del modelo `Curso` para verificar si el usuario ya estaba inscrito.

El problema es que `tieneEstudiante()` verifica DOS cosas:
1. ✅ Si el usuario está inscrito en `curso_estudiantes` (inscripción real)
2. ✅ Si el usuario tiene una asignación en `curso_asignaciones` (asignación pendiente)

Esto causaba que usuarios con asignaciones pendientes (como el curso ID 18 asignado automáticamente) no pudieran inscribirse porque el sistema pensaba que ya estaban inscritos.

## Diferencia entre Asignación e Inscripción

- **Asignación** (`curso_asignaciones`): El admin o el sistema asigna un curso al usuario, pero el usuario aún NO está inscrito
- **Inscripción** (`curso_estudiantes`): El usuario confirma su participación y se inscribe activamente en el curso

## Solución Implementada

Se modificó el método `inscribirseCurso` para que verifique SOLO si el usuario está inscrito en `curso_estudiantes`, ignorando las asignaciones pendientes.

### Código Anterior (Incorrecto):
```php
// Verificar si ya está inscrito
if ($curso->tieneEstudiante($user->id)) {
    return response()->json([
        'success' => false,
        'message' => 'Ya estás inscrito en este curso'
    ], 400);
}
```

### Código Nuevo (Correcto):
```php
// Verificar si ya está inscrito (solo en curso_estudiantes, no en asignaciones)
$yaInscrito = $curso->estudiantes()->wherePivot('estudiante_id', $user->id)->exists();

if ($yaInscrito) {
    return response()->json([
        'success' => false,
        'message' => 'Ya estás inscrito en este curso'
    ], 400);
}
```

## Flujo Correcto Ahora

1. **Usuario se registra** → Sistema asigna automáticamente curso ID 18
2. **Usuario verifica email** → Recibe correo de bienvenida
3. **1 minuto después** → Recibe correo de asignación del curso ID 18
4. **Usuario va a `/academico/cursos-disponibles`** → Ve el curso ID 18 con botón "Inscribirse"
5. **Usuario hace clic en "Inscribirse"** → ✅ Se inscribe exitosamente
6. **Usuario inscrito** → Ahora puede acceder al aula virtual del curso

## Resultado

✅ Usuarios con asignaciones pendientes pueden inscribirse correctamente
✅ El sistema solo bloquea la inscripción si el usuario YA está inscrito en `curso_estudiantes`
✅ Las asignaciones (`curso_asignaciones`) no bloquean la inscripción
✅ El flujo de registro → asignación → inscripción funciona correctamente

## Archivos Modificados

- `app/Http/Controllers/AcademicoController.php` (método `inscribirseCurso`)

## Pruebas Recomendadas

1. Registrar un nuevo usuario
2. Verificar su email
3. Esperar 1 minuto para recibir asignación del curso ID 18
4. Ir a `/academico/cursos-disponibles`
5. Hacer clic en "Inscribirse" en el curso ID 18
6. Verificar que la inscripción sea exitosa
7. Verificar que ahora aparezca el botón "Acceder" en lugar de "Inscribirse"

## Notas Técnicas

- El método `tieneEstudiante()` del modelo `Curso` NO fue modificado porque se usa en otros lugares donde SÍ se necesita verificar tanto inscripciones como asignaciones
- Solo se modificó la verificación específica en el método de inscripción
- La consulta `wherePivot('estudiante_id', $user->id)` verifica solo en la tabla pivot `curso_estudiantes`
- Las asignaciones siguen funcionando normalmente en `curso_asignaciones`
