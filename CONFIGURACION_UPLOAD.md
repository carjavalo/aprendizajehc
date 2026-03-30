# Configuración de Carga de Archivos Grandes

## Problema Resuelto
Error 413 (Content Too Large) al crear cursos con archivos grandes.

## Soluciones Implementadas

### 1. ✅ Configuración en .htaccess
Se ha agregado configuración automática en `public/.htaccess`:
- `upload_max_filesize`: 500M
- `post_max_size`: 500M
- `max_execution_time`: 600 segundos
- `max_input_time`: 600 segundos
- `memory_limit`: 512M
- `max_file_uploads`: 50 archivos

### 2. ✅ Validación JavaScript
Se agregó validación antes de enviar:
- Límite por archivo: 100 MB
- Límite total por curso: 450 MB
- Mensajes claros al usuario

## Si el .htaccess NO Funciona

### Configurar XAMPP Manualmente

#### Paso 1: Editar php.ini
1. Abre XAMPP Control Panel
2. Click en "Config" junto a Apache
3. Selecciona "PHP (php.ini)"
4. Busca y modifica estas líneas:

```ini
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 600
max_input_time = 600
memory_limit = 512M
max_file_uploads = 50
```

5. Guarda el archivo
6. Reinicia Apache

#### Paso 2: Verificar Configuración de Apache

Si usas Apache, edita `httpd.conf`:

1. En XAMPP Control Panel, click "Config" → "Apache (httpd.conf)"
2. Busca `LimitRequestBody` y asegúrate que esté comentado o tenga un valor alto:

```apache
# LimitRequestBody 0
```

O configura un límite:

```apache
LimitRequestBody 524288000
```

(524288000 bytes = 500 MB)

3. Guarda y reinicia Apache

### Verificar Configuración Actual

Crea un archivo `info.php` en `public/`:

```php
<?php
phpinfo();
?>
```

Visita `http://localhost/info.php` y busca:
- `upload_max_filesize`
- `post_max_size`
- `max_execution_time`

**IMPORTANTE: Elimina `info.php` después de verificar.**

## Recomendaciones de Uso

### Para Archivos Muy Grandes (>100MB)

**Videos:**
- ✅ Sube a YouTube/Vimeo (GRATIS)
- ✅ Usa Google Drive/Dropbox
- ✅ Comprime con Handbrake/FFmpeg

**Documentos:**
- ✅ Comprime PDFs con herramientas online
- ✅ Optimiza imágenes antes de incluirlas

**Imágenes:**
- ✅ Usa TinyPNG/JPEGmini
- ✅ Redimensiona a resoluciones adecuadas
- ✅ Convierte a WebP

### Límites Establecidos

| Tipo | Límite |
|------|--------|
| Por archivo individual | 100 MB |
| Total por curso | 450 MB |
| Imagen de portada | 10 MB (recomendado) |
| Número de archivos | 50 archivos |

## Troubleshooting

### Error persiste después de configuración

1. **Reinicia Apache** completamente
2. **Limpia caché** del navegador (Ctrl + Shift + Delete)
3. **Verifica logs** en `xampp/apache/logs/error.log`
4. **Prueba con archivo pequeño** primero

### Error en producción (servidor web)

Contacta al hosting para aumentar:
- `upload_max_filesize`
- `post_max_size`
- `max_execution_time`

### Alternativa: Nginx

Si usas Nginx, edita nginx.conf:

```nginx
client_max_body_size 500M;
```

## Soporte

Si el problema persiste:
1. Revisa los logs de Apache
2. Verifica que los cambios se aplicaron con `phpinfo()`
3. Prueba con archivos más pequeños
4. Considera usar URLs externas para videos

## Archivos Modificados

- ✅ `public/.htaccess` - Límites PHP
- ✅ `public/js/course-wizard.js` - Validación cliente
- ✅ `CONFIGURACION_UPLOAD.md` - Esta documentación
