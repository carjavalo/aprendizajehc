# Guía de Despliegue en cPanel - ProAHUV

## Problema
Las vistas se muestran sin estilos CSS y las DataTables no funcionan correctamente después de subir a cPanel.

## Causa del Problema
1. **Rutas CSS hardcodeadas** - Ya corregidas en este commit
2. **APP_URL incorrecto** - La URL de la aplicación no coincide con el dominio real
3. **Cache de vistas** - Las vistas compiladas tienen rutas antiguas
4. **Configuración del servidor** - El dominio no apunta correctamente a la carpeta `public`

---

## Pasos para Solucionar

### 1. Configurar el archivo .env en el servidor

Edita el archivo `.env` en el servidor y asegúrate de que:

```env
APP_NAME=ProAHUV
APP_ENV=production
APP_DEBUG=false
APP_URL=https://TU-DOMINIO.com

# Si usas HTTPS, también agrega:
FORCE_HTTPS=true
```

**Importante:** Reemplaza `TU-DOMINIO.com` con tu dominio real.

---

### 2. Limpiar Cache en el Servidor

Conéctate por SSH o usa el Terminal de cPanel y ejecuta estos comandos en la raíz de tu proyecto:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

O puedes crear un archivo temporal para ejecutar esto:

```php
// Crea: public/clear-cache.php
<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->call('config:clear');
$kernel->call('cache:clear');
$kernel->call('view:clear');
$kernel->call('route:clear');

echo "✅ Cache limpiada correctamente!";

// IMPORTANTE: Elimina este archivo después de usarlo
```

Visita `https://tu-dominio.com/clear-cache.php` y luego **elimina el archivo**.

---

### 3. Configurar el Dominio para Apuntar a /public

#### Opción A: Usando Subdominios (Recomendado)
En cPanel > Dominios, configura que tu dominio apunte directamente a la carpeta `public` de Laravel.

#### Opción B: Usando .htaccess en la raíz
Si no puedes cambiar el document root, crea un archivo `.htaccess` en la raíz (fuera de public):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

#### Opción C: Mover contenido de public
Si ninguna de las anteriores funciona:

1. Mueve todo el contenido de la carpeta `public/` a `public_html/`
2. Edita `public_html/index.php` y cambia:

```php
// Cambiar de:
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// A:
require __DIR__.'/../tu-carpeta-laravel/vendor/autoload.php';
$app = require_once __DIR__.'/../tu-carpeta-laravel/bootstrap/app.php';
```

---

### 4. Permisos de Carpetas

Asegúrate de que estas carpetas tengan permisos de escritura (755 o 775):

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

---

### 5. Crear Symlink de Storage

Si las imágenes y archivos subidos no se ven, crea el symlink:

```bash
php artisan storage:link
```

O manualmente:
```bash
cd public
ln -s ../storage/app/public storage
```

---

### 6. Verificar HTTPS (Mixed Content)

Si tu sitio usa HTTPS pero ves errores de "Mixed Content":

Agrega esto al archivo `app/Providers/AppServiceProvider.php`:

```php
public function boot(): void
{
    if (config('app.env') === 'production') {
        \URL::forceScheme('https');
    }
}
```

---

### 7. Archivos Modificados en este Commit

Se corrigieron las rutas hardcodeadas en estos archivos:

- `resources/views/admin/layouts/master.blade.php`
- `resources/views/admin/users/index.blade.php`
- `resources/views/admin/users/create.blade.php`
- `resources/views/admin/users/edit.blade.php`
- `resources/views/admin/users/show.blade.php`
- `resources/views/dashboard.blade.php`

**Cambio realizado:**
```blade
<!-- Antes (incorrecto) -->
<link rel="stylesheet" href="/css/admin_custom.css">

<!-- Después (correcto) -->
<link rel="stylesheet" href="{{ asset('css/admin_custom.css') }}">
```

---

### 8. Checklist Final

- [ ] Archivo `.env` con `APP_URL` correcto
- [ ] Cache limpiada (`php artisan cache:clear`, etc.)
- [ ] Dominio apuntando a carpeta `public`
- [ ] Permisos correctos en `storage/` y `bootstrap/cache/`
- [ ] Symlink de storage creado
- [ ] HTTPS forzado si es necesario

---

## Estructura Correcta en cPanel

```
/home/usuario/
├── public_html/           <- Aquí debe apuntar el dominio (contenido de public/)
│   ├── .htaccess
│   ├── index.php
│   ├── css/
│   ├── js/
│   ├── vendor/
│   └── build/
│
└── laravel-app/           <- Resto de la aplicación Laravel
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── resources/
    ├── routes/
    ├── storage/
    └── vendor/
```

---

## Soporte

Si después de seguir estos pasos el problema persiste:

1. Verifica la consola del navegador (F12) para ver qué recursos no cargan
2. Revisa los logs de Laravel en `storage/logs/laravel.log`
3. Verifica que todos los archivos se subieron correctamente (especialmente `public/vendor/adminlte/`)
