# Implementación Campo Teléfono en Usuarios

## Descripción

Se ha agregado el campo de teléfono de contacto a la tabla de usuarios y a todos los formularios de registro y edición de usuarios en el sistema.

## Cambios Realizados

### 1. Base de Datos

**Migración:** `2026_01_21_150335_add_phone_to_users_table.php`

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('phone', 20)->nullable()->after('email');
});
```

- **Campo:** `phone`
- **Tipo:** VARCHAR(20)
- **Nullable:** Sí
- **Posición:** Después del campo `email`

### 2. Modelo User

**Archivo:** `app/Models/User.php`

Agregado `'phone'` al array `$fillable`:

```php
protected $fillable = [
    'name',
    'apellido1',
    'apellido2',
    'email',
    'phone',  // ✅ Nuevo campo
    'password',
    'role',
    'tipo_documento',
    'numero_documento',
    'servicio_area_id',
    'vinculacion_contrato_id',
    'sede_id',
];
```

### 3. Vista de Registro Público

**Archivo:** `resources/views/welcome.blade.php`

Agregado campo de teléfono después del email en el formulario de registro:

```html
<div class="mb-3">
    <label for="phone" class="form-label">Teléfono de Contacto</label>
    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
           id="phone" name="phone" value="{{ old('phone') }}" 
           placeholder="Ej: 3001234567" maxlength="20" autocomplete="tel">
    @error('phone')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
```

**Características:**
- Tipo de input: `tel` (optimizado para móviles)
- Placeholder: "Ej: 3001234567"
- Máximo 20 caracteres
- Autocomplete: `tel`
- Validación de errores incluida
- Campo opcional (no required)

### 4. Vista de Creación de Usuarios (Admin)

**Archivo:** `resources/views/admin/users/create.blade.php`

Reorganizada la sección de email y teléfono:

**Antes:**
- Email: col-md-8
- Rol: col-md-4

**Después:**
- Email: col-md-6
- Teléfono: col-md-6
- Rol: col-md-12 (nueva fila)

```html
<div class="row">
    <div class="col-md-6">
        <!-- Email -->
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="phone">Teléfono de Contacto</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                </div>
                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                    id="phone" name="phone" value="{{ old('phone') }}" 
                    placeholder="3001234567" maxlength="20">
                @error('phone')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
</div>
```

**Características:**
- Icono: `fa-phone`
- Input group con prepend
- Validación de errores
- Campo opcional

### 5. Vista de Edición de Usuarios (Admin)

**Archivo:** `resources/views/admin/users/edit.blade.php`

Misma reorganización que en la vista de creación:

```html
<div class="row">
    <div class="col-md-6">
        <!-- Email -->
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="phone">Teléfono de Contacto</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                </div>
                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                    id="phone" name="phone" value="{{ old('phone', $user->phone) }}" 
                    placeholder="3001234567" maxlength="20">
                @error('phone')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
</div>
```

**Nota:** Usa `old('phone', $user->phone)` para mantener el valor existente.

## Validación en Controladores

Los controladores que manejan el registro y actualización de usuarios deben incluir la validación del campo phone si es necesario:

```php
// Ejemplo de validación (opcional)
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email',
    'phone' => 'nullable|string|max:20',  // ✅ Validación del teléfono
    // ... otros campos
]);
```

## Uso del Campo

El campo `phone` ahora está disponible en:

1. **Registro de nuevos usuarios** (vista pública)
2. **Creación de usuarios** (panel admin)
3. **Edición de usuarios** (panel admin)
4. **Modelo User** (accesible como `$user->phone`)

### Ejemplos de Uso

```php
// Obtener teléfono de un usuario
$telefono = $user->phone;

// Actualizar teléfono
$user->update(['phone' => '3001234567']);

// Verificar si tiene teléfono
if ($user->phone) {
    // Enviar SMS, WhatsApp, etc.
}
```

## Consideraciones

1. **Campo Opcional:** El teléfono no es obligatorio para permitir flexibilidad
2. **Formato Libre:** No se impone un formato específico para soportar diferentes países
3. **Longitud:** Máximo 20 caracteres para soportar códigos internacionales
4. **Tipo de Input:** `tel` para mejor experiencia en móviles
5. **Sin Datos Borrados:** Todos los datos existentes se mantienen intactos

## Migraciones Futuras

Si se requiere hacer el campo obligatorio:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('phone', 20)->nullable(false)->change();
});
```

Si se requiere agregar validación de formato:

```php
'phone' => 'nullable|regex:/^[0-9]{10}$/',  // Ejemplo: 10 dígitos
```

## Verificación

Para verificar que el campo funciona correctamente:

1. **Registro Público:**
   - Ir a: `http://192.168.2.200:8001/`
   - Hacer clic en "Registrarse"
   - Verificar que aparece el campo "Teléfono de Contacto"
   - Registrar un usuario con teléfono

2. **Panel Admin - Crear Usuario:**
   - Ir a: Usuarios > Crear Usuario
   - Verificar campo de teléfono
   - Crear usuario con teléfono

3. **Panel Admin - Editar Usuario:**
   - Ir a: Usuarios > Editar
   - Verificar que el teléfono se muestra y puede editarse
   - Actualizar teléfono

4. **Base de Datos:**
   ```sql
   SELECT id, name, email, phone FROM users LIMIT 10;
   ```

## Archivos Modificados

1. `database/migrations/2026_01_21_150335_add_phone_to_users_table.php` - Nueva migración
2. `app/Models/User.php` - Agregado 'phone' a $fillable
3. `resources/views/welcome.blade.php` - Campo en formulario de registro
4. `resources/views/admin/users/create.blade.php` - Campo en creación de usuarios
5. `resources/views/admin/users/edit.blade.php` - Campo en edición de usuarios

## Notas Adicionales

- El campo es compatible con todos los usuarios existentes (nullable)
- No se requiere actualización de datos existentes
- El campo está listo para ser usado en notificaciones SMS/WhatsApp
- Se puede agregar validación adicional según necesidades del negocio
