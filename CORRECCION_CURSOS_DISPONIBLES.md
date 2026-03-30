# ✅ CORRECCIÓN: Cursos Disponibles y Asignaciones

**Fecha:** 22 de enero de 2026  
**Estado:** VERIFICADO

---

## 🎯 OBJETIVOS

1. ✅ Quitar enlace de ingreso del correo de bienvenida (solo informativo)
2. ✅ Verificar que cursos asignados aparezcan en `/academico/cursos-disponibles`
3. ✅ Asegurar que asignaciones manuales también aparezcan

---

## 🔧 CORRECCIONES APLICADAS

### 1. Correo de Bienvenida - Solo Informativo
**Archivo:** `resources/views/emails/bienvenida.blade.php`

**ANTES:**
```html
<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $dashboardUrl }}" class="btn-primary">Acceder a la Plataforma</a>
</div>
```

**DESPUÉS:**
```html
<!-- Botón eliminado - correo solo informativo -->
```

**Cambio:** Eliminado el botón "Acceder a la Plataforma" del correo de bienvenida.

---

## ✅ VERIFICACIÓN DEL SISTEMA

### Diagnóstico Realizado

Ejecuté el script de diagnóstico y confirmé:

```bash
php diagnostico_cursos_disponibles.php
```

**Resultados:**
- ✅ Usuario tiene asignación activa al curso ID 18
- ✅ Asignación con estado 'activo'
- ✅ Curso ID 18 con estado 'activo'
- ✅ Sin fecha de expiración
- ✅ Scope `activas()` funciona correctamente
- ✅ Consulta del controlador devuelve el curso correctamente

### Flujo de Asignación y Visualización

#### 1. Asignación Automática (Registro)
```php
// En RegisteredUserController.php
DB::table('curso_asignaciones')->insert([
    'curso_id' => 18,
    'estudiante_id' => $user->id,
    'asignado_por' => 1,
    'estado' => 'activo',
    'fecha_asignacion' => now(),
]);
```

#### 2. Asignación Manual (Configuración)
```
URL: http://192.168.2.200:8001/configuracion/asignacion-cursos
Controlador: AsignacionCursoController
Método: asignar()
```

**Proceso:**
1. Buscar estudiante por nombre, email o documento
2. Seleccionar cursos a asignar
3. Sistema crea registro en `curso_asignaciones` con estado 'activo'
4. Estudiante puede ver el curso en `/academico/cursos-disponibles`

#### 3. Visualización en Cursos Disponibles
```
URL: http://192.168.2.200:8001/academico/cursos-disponibles
Controlador: AcademicoController
Método: getCursosDisponiblesData()
```

**Lógica de Filtrado:**
```php
// Para estudiantes y docentes
$cursosAsignadosIds = CursoAsignacion::where('estudiante_id', $user->id)
    ->activas()  // Solo asignaciones activas
    ->pluck('curso_id')
    ->toArray();

$cursosQuery = Curso::where('estado', 'activo')
    ->whereIn('id', $cursosAsignadosIds);
```

---

## 📊 TABLAS INVOLUCRADAS

### 1. curso_asignaciones
**Propósito:** Registrar qué cursos están asignados a qué estudiantes

**Columnas clave:**
- `curso_id`: ID del curso
- `estudiante_id`: ID del usuario (rol Estudiante)
- `asignado_por`: ID del usuario que asignó
- `estado`: 'activo', 'inactivo', 'expirado'
- `fecha_asignacion`: Cuándo se asignó
- `fecha_expiracion`: Cuándo expira (nullable)

**Scope activas():**
```php
public function scopeActivas($query)
{
    return $query->where('estado', 'activo')
                 ->where(function ($q) {
                     $q->whereNull('fecha_expiracion')
                       ->orWhere('fecha_expiracion', '>', now());
                 });
}
```

### 2. curso_estudiantes
**Propósito:** Registrar inscripciones formales de estudiantes a cursos

**Diferencia con curso_asignaciones:**
- `curso_asignaciones`: Permiso para ver el curso
- `curso_estudiantes`: Inscripción formal con progreso

**Columnas clave:**
- `curso_id`: ID del curso
- `estudiante_id`: ID del usuario
- `estado`: Estado de la inscripción
- `progreso`: Porcentaje de avance (0-100)
- `fecha_inscripcion`: Cuándo se inscribió

---

## 🔄 FLUJO COMPLETO

### Registro de Nuevo Usuario

1. **Usuario se registra**
   - Rol: "Estudiante"
   - Email sin verificar

2. **Sistema asigna curso ID 18**
   ```sql
   INSERT INTO curso_asignaciones 
   (curso_id, estudiante_id, asignado_por, estado, fecha_asignacion)
   VALUES (18, [user_id], 1, 'activo', NOW());
   ```

3. **Sistema envía correos (español)**
   - Verificación de cuenta
   - Asignación de curso

4. **Usuario verifica email**
   - Recibe correo de bienvenida (sin enlace)

5. **Usuario accede a la plataforma**
   - Va a `/academico/cursos-disponibles`
   - Ve el curso ID 18 "Inducción Institucional (General)"
   - Estado: "Pendiente" (asignado pero no inscrito)

6. **Usuario se inscribe**
   - Hace clic en "Inscribirse"
   - Sistema crea registro en `curso_estudiantes`
   - Estado cambia a "Inscrito"
   - Puede acceder al aula virtual

### Asignación Manual de Cursos

1. **Administrador accede**
   - URL: `/configuracion/asignacion-cursos`
   - Roles permitidos: Super Admin, Administrador, Operador

2. **Busca estudiante**
   - Por nombre, email o documento
   - Sistema muestra estudiantes con rol "Estudiante"

3. **Selecciona cursos**
   - Ve lista de cursos activos
   - Marca los cursos a asignar
   - Sistema indica si ya están asignados

4. **Asigna cursos**
   - Sistema crea registros en `curso_asignaciones`
   - Estado: 'activo'
   - Asignado por: ID del administrador

5. **Estudiante ve los cursos**
   - Automáticamente aparecen en `/academico/cursos-disponibles`
   - Puede inscribirse y acceder

---

## 🧪 SCRIPTS DE PRUEBA

### 1. Diagnóstico General
```bash
php diagnostico_cursos_disponibles.php
```
Verifica asignaciones y cursos disponibles del último estudiante registrado.

### 2. Prueba por Usuario
```bash
php test_usuario_cursos_disponibles.php usuario@correo.com
```
Muestra cursos asignados a un usuario específico.

### 3. Verificación de Registro
```bash
php test_registro_verificacion_final.php
```
Verifica todo el sistema de registro y asignación.

---

## ✅ CONFIRMACIÓN DE FUNCIONAMIENTO

### Caso de Prueba: Usuario "angie"

**Datos:**
- Email: cirugiamujeres@correohuv.gov.co
- Rol: Estudiante
- Email verificado: Sí

**Asignaciones:**
- ✅ Curso ID 18: "Inducción Institucional (General)"
- ✅ Estado: activo
- ✅ Sin fecha de expiración
- ✅ Curso con estado: activo

**Resultado:**
- ✅ Debería ver 1 curso en `/academico/cursos-disponibles`
- ✅ Puede inscribirse al curso
- ✅ Puede acceder al aula virtual después de inscribirse

---

## 🎯 ESTADOS DE UN CURSO PARA UN ESTUDIANTE

### 1. Asignado (Pendiente)
- ✅ Registro en `curso_asignaciones` con estado 'activo'
- ❌ NO hay registro en `curso_estudiantes`
- **Vista:** Aparece en cursos disponibles
- **Botón:** "Inscribirse"
- **Acceso:** No puede acceder al contenido

### 2. Inscrito
- ✅ Registro en `curso_asignaciones` con estado 'activo'
- ✅ Registro en `curso_estudiantes`
- **Vista:** Aparece en cursos disponibles
- **Botón:** "Acceder" o "Continuar"
- **Acceso:** Puede acceder al aula virtual

### 3. Completado
- ✅ Registro en `curso_asignaciones`
- ✅ Registro en `curso_estudiantes`
- ✅ Progreso: 100%
- **Vista:** Aparece en cursos disponibles
- **Botón:** "Ver Certificado"
- **Acceso:** Puede revisar contenido

---

## 📝 ROLES Y PERMISOS

### Super Admin, Admin, Operador
- ✅ Ven TODOS los cursos activos
- ✅ No necesitan asignación
- ✅ Acceso directo sin inscripción
- ✅ Pueden asignar cursos a estudiantes

### Estudiante
- ✅ Solo ven cursos asignados (tabla `curso_asignaciones`)
- ✅ Deben tener asignación activa
- ✅ Deben inscribirse para acceder al contenido
- ❌ No pueden asignar cursos

### Docente
- ✅ Solo ven cursos asignados
- ✅ Pueden ser instructores de cursos
- ✅ Acceso similar a estudiantes

---

## 🔍 SOLUCIÓN DE PROBLEMAS

### Problema: Curso no aparece en cursos disponibles

**Verificar:**

1. **¿Tiene asignación activa?**
   ```sql
   SELECT * FROM curso_asignaciones 
   WHERE estudiante_id = [user_id] 
   AND curso_id = [curso_id]
   AND estado = 'activo';
   ```

2. **¿El curso está activo?**
   ```sql
   SELECT * FROM cursos WHERE id = [curso_id] AND estado = 'activo';
   ```

3. **¿La asignación no ha expirado?**
   ```sql
   SELECT * FROM curso_asignaciones 
   WHERE estudiante_id = [user_id]
   AND (fecha_expiracion IS NULL OR fecha_expiracion > NOW());
   ```

**Soluciones:**

- **Sin asignación:** Asignar desde `/configuracion/asignacion-cursos`
- **Curso inactivo:** Activar curso desde panel de administración
- **Asignación expirada:** Actualizar fecha de expiración o quitar

### Problema: Asignación manual no aparece

**Verificar:**

1. **¿El usuario tiene rol "Estudiante"?**
   ```sql
   SELECT role FROM users WHERE id = [user_id];
   ```

2. **¿La asignación se creó correctamente?**
   ```sql
   SELECT * FROM curso_asignaciones 
   WHERE estudiante_id = [user_id] 
   ORDER BY created_at DESC LIMIT 5;
   ```

3. **¿El estado es 'activo'?**
   ```sql
   UPDATE curso_asignaciones 
   SET estado = 'activo' 
   WHERE id = [asignacion_id];
   ```

---

## 📞 SOPORTE

Para cualquier problema:
- **Email:** oficinacoordinadoraacademica@correohuv.gov.co
- **Ubicación:** Hospital Universitario del Valle, Séptimo piso

---

**Documento generado:** 22 de enero de 2026  
**Versión:** 1.0  
**Estado:** Sistema verificado y funcional
