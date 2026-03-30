# Corrección Error JavaScript: verDocumento

## Problema Identificado

Error en consola:
```
VM360:841 Uncaught SyntaxError: Failed to execute 'appendChild' on 'Node': Unexpected token ':'
at b (jquery.min.js:2:866)
at He (jquery.min.js:2:48373)
at S.fn.init.append (jquery.min.js:2:49724)
at S.fn.init.html (jquery.min.js:2:50494)
at Object.<anonymous> (classroom:1463:35)
```

## Causa Raíz

El error tenía dos causas:

### 1. Parámetros sin escapar en llamadas onclick
Los parámetros pasados a la función `verDocumento()` no estaban siendo escapados correctamente para JavaScript.

**Código Problemático:**
```php
onclick="verDocumento('{{ $archivoUrl }}', '{{ addslashes($material->titulo) }}', '{{ $material->tipo }}', '{{ $extension }}')"
```

### 2. Variables sin escapar en template strings de JavaScript
Dentro de la función `verDocumento`, las variables se usaban directamente en template strings sin escapar, causando errores cuando contenían caracteres especiales.

**Código Problemático:**
```javascript
contenido = `
    <img src="${url}" alt="${titulo}" />
    <video><source src="${url}" type="video/${extension}"></video>
`;
```

**Problema:** Si `url` contiene caracteres como `:`, `'`, `"`, o `titulo` contiene comillas, el HTML generado se rompe.

## Solución Implementada

### 1. Escapar parámetros en llamadas onclick con json_encode()

```php
onclick="verDocumento({{ json_encode($archivoUrl) }}, {{ json_encode($material->titulo) }}, {{ json_encode($material->tipo) }}, {{ json_encode($extension) }})"
```

### 2. Función auxiliar para escapar HTML en JavaScript

Agregada función `escapeHtml()` dentro de `verDocumento()`:

```javascript
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Escapar variables antes de usarlas
const urlEscaped = escapeHtml(url);
const tituloEscaped = escapeHtml(titulo);

// Usar variables escapadas en template strings
contenido = `
    <img src="${urlEscaped}" alt="${tituloEscaped}" />
    <video><source src="${urlEscaped}" type="video/${escapeHtml(extension)}"></video>
`;
```

## Archivos Corregidos

1. **resources/views/admin/capacitaciones/cursos/edit.blade.php**
   - Línea 337: Llamada onclick con json_encode()
   - Línea 1325: Función verDocumento con escapeHtml()

2. **resources/views/admin/capacitaciones/cursos/classroom/materiales.blade.php**
   - Línea 47: Llamada onclick con json_encode()
   - Línea 827: Función verDocumento con escapeHtml()

## Verificación

Para verificar que la corrección funciona:

1. Limpiar caché del navegador (Ctrl+Shift+Delete)
2. Recargar la página (Ctrl+F5)
3. Acceder a un curso con materiales
4. Hacer clic en el botón "Ver" de cualquier material
5. El modal debe abrirse correctamente sin errores en consola
6. Probar con materiales que tengan:
   - Títulos con comillas o caracteres especiales
   - URLs con parámetros (que contengan `:`, `?`, `&`)
   - Diferentes tipos de archivos

## Prevención Futura

### Regla 1: Pasar datos de PHP a JavaScript
Siempre usar `json_encode()` al pasar datos de PHP a JavaScript en atributos HTML:

```php
<!-- ✅ CORRECTO -->
onclick="myFunction({{ json_encode($variable) }})"
data-config="{{ json_encode($array) }}"

<!-- ❌ INCORRECTO -->
onclick="myFunction('{{ $variable }}')"
onclick="myFunction('{{ addslashes($variable) }}')"
```

### Regla 2: Usar variables en template strings de JavaScript
Siempre escapar variables antes de usarlas en template strings que generan HTML:

```javascript
// ✅ CORRECTO
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
const safe = escapeHtml(userInput);
html = `<div>${safe}</div>`;

// ❌ INCORRECTO
html = `<div>${userInput}</div>`;
```

### Regla 3: Usar métodos jQuery seguros
Cuando sea posible, usar métodos jQuery que escapan automáticamente:

```javascript
// ✅ CORRECTO - jQuery escapa automáticamente
$('#elemento').text(userInput);
$('#elemento').attr('href', url);

// ⚠️ CUIDADO - No escapa automáticamente
$('#elemento').html(userInput);
```

