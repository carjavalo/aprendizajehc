# Sistema de Mensajería en Tiempo Real - Chat Interno

## Objetivo
Implementar un sistema de actualización automática de mensajes para que los usuarios reciban mensajes instantáneamente sin necesidad de refrescar el navegador, mejorando significativamente la experiencia de usuario.

## Solución Implementada: Polling Inteligente

Se implementó un sistema de **polling** (consulta periódica) que verifica nuevos mensajes cada 5 segundos. Esta es una solución simple, efectiva y no requiere configuración adicional de servidores WebSocket.

## Características Implementadas

### 1. Verificación Automática de Nuevos Mensajes
**Frecuencia**: Cada 5 segundos
**Archivo**: `resources/views/dashboard.blade.php`

```javascript
// Polling cada 5 segundos
chatPollingInterval = setInterval(verificarNuevosMensajes, 5000);
```

### 2. Actualización de Badges en Tiempo Real

#### Badge en Tab "Recibidos"
- Muestra el número de mensajes no leídos
- Se actualiza automáticamente cada 5 segundos
- Animación de pulsación para llamar la atención

```javascript
const badgeHtml = noLeidos > 0 ? 
    `<span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full ml-1">${noLeidos}</span>` 
    : '';
```

#### Badge en Botón Flotante
- Sincronizado con el badge del tab
- Visible desde cualquier parte del dashboard
- Se actualiza en tiempo real

### 3. Notificaciones Visuales Sutiles

Cuando llega un nuevo mensaje, se muestra una notificación temporal en la esquina superior derecha:

```javascript
function mostrarNotificacionNuevoMensaje() {
    const notif = $('<div class="nuevo-mensaje-notif">Nuevo mensaje recibido</div>');
    // Estilo: fondo azul corporativo, texto blanco, animación suave
    // Duración: 3 segundos
}
```

**Características de la notificación**:
- Posición: Esquina superior derecha
- Color: Azul corporativo (#2e3a75)
- Duración: 3 segundos
- Animación: Deslizamiento desde la derecha
- No intrusiva

### 4. Preservación de Posición del Scroll

Cuando se actualizan los mensajes automáticamente, el scroll mantiene su posición:

```javascript
function cargarMensajesRecibidos(preserveScroll = false) {
    const container = $('#mensajesRecibidosContainer');
    const scrollPos = preserveScroll ? container.scrollTop() : 0;
    
    // ... cargar mensajes ...
    
    // Restaurar posición
    if (preserveScroll && scrollPos > 0) {
        container.scrollTop(scrollPos);
    }
}
```

### 5. Detección de Visibilidad de Pestaña

El sistema detecta cuando el usuario vuelve a la pestaña del navegador y actualiza inmediatamente:

```javascript
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        verificarNuevosMensajes();
        if (isTabRecibidosActive) {
            cargarMensajesRecibidos();
        }
    }
});
```

### 6. Actualización Inmediata al Enviar

Cuando un usuario envía un mensaje, el sistema actualiza inmediatamente sin esperar el próximo ciclo de polling:

```javascript
success: function(response) {
    if (response.success) {
        // Limpiar formulario
        // ...
        
        // Actualizar inmediatamente
        verificarNuevosMensajes();
    }
}
```

## Animaciones CSS Implementadas

### 1. Animación de Entrada de Notificación
```css
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.nuevo-mensaje-notif {
    animation: slideInRight 0.3s ease-out;
}
```

### 2. Animación de Pulsación en Badges
```css
@keyframes badgePulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

.chat-tab .bg-red-500 {
    animation: badgePulse 2s infinite;
}

.floating-badge {
    animation: badgePulse 2s infinite;
}
```

## Flujo de Funcionamiento

### Escenario 1: Usuario A envía mensaje a Usuario B

1. **Usuario A** escribe y envía el mensaje
2. El mensaje se guarda en la base de datos
3. El formulario de Usuario A se limpia automáticamente
4. Usuario A ve actualización inmediata (sin esperar polling)

5. **Usuario B** (en el dashboard):
   - Máximo 5 segundos después, el polling detecta el nuevo mensaje
   - El badge de "Recibidos" se actualiza con el número de no leídos
   - El badge del botón flotante también se actualiza
   - Si Usuario B tiene el tab "Recibidos" abierto:
     - Los mensajes se recargan automáticamente
     - Se muestra notificación: "Nuevo mensaje recibido"
     - El scroll mantiene su posición actual

### Escenario 2: Usuario recibe mensaje mientras está en otra pestaña

1. Usuario está en otra pestaña del navegador
2. Llega un nuevo mensaje
3. El polling continúa ejecutándose en segundo plano
4. Cuando el usuario vuelve a la pestaña:
   - Se ejecuta verificación inmediata
   - Badges se actualizan
   - Si está en tab "Recibidos", mensajes se recargan

### Escenario 3: Usuario cierra/sale de la página

1. El evento `beforeunload` se dispara
2. El polling se detiene automáticamente
3. Se liberan recursos

```javascript
$(window).on('beforeunload', function() {
    if (chatPollingInterval) {
        clearInterval(chatPollingInterval);
    }
});
```

## Variables de Control

```javascript
let chatPollingInterval = null;      // Intervalo del polling
let lastMessageCount = 0;            // Contador de mensajes anterior
let isTabRecibidosActive = false;    // Estado del tab activo
```

## Optimizaciones Implementadas

### 1. Polling Condicional
- Solo recarga mensajes si el tab "Recibidos" está activo
- Evita recargas innecesarias cuando el usuario está en el tab "Enviar"

### 2. Detección de Cambios
- Solo muestra notificación si hay mensajes nuevos (currentCount > lastMessageCount)
- No muestra notificación en la primera carga

### 3. Gestión de Recursos
- Detiene el polling cuando el usuario sale de la página
- Limpia intervalos correctamente

### 4. Manejo de Errores
- Si falla la consulta, no interrumpe el polling
- Continúa intentando en el próximo ciclo

## Ventajas de Esta Implementación

✅ **Sin configuración adicional**: No requiere WebSockets, Pusher, o servicios externos
✅ **Compatible con cualquier servidor**: Funciona en cualquier hosting PHP/Laravel
✅ **Bajo consumo de recursos**: Solo consulta cada 5 segundos
✅ **Experiencia fluida**: Los usuarios ven mensajes casi instantáneamente
✅ **No intrusivo**: Las notificaciones son sutiles y no interrumpen el trabajo
✅ **Preserva contexto**: Mantiene posición del scroll al actualizar
✅ **Responsive**: Se adapta a cambios de visibilidad de pestaña

## Consideraciones de Rendimiento

### Frecuencia de Polling
- **Actual**: 5 segundos (12 consultas por minuto)
- **Recomendado**: Entre 3-10 segundos según carga del servidor
- **Ajustable**: Cambiar el valor en `setInterval(verificarNuevosMensajes, 5000)`

### Carga del Servidor
- Consulta ligera: Solo obtiene datos de mensajes
- Filtrado en cliente: Reduce procesamiento en servidor
- Caché de Laravel: Puede implementarse para optimizar

### Escalabilidad
Para más de 100 usuarios concurrentes, considerar:
- Aumentar intervalo de polling a 10 segundos
- Implementar WebSockets (Laravel Echo + Pusher/Socket.io)
- Usar Redis para caché de mensajes

## Archivos Modificados

### resources/views/dashboard.blade.php

**Líneas agregadas/modificadas**:
- ~1145-1260: Función `cargarMensajesRecibidos()` con preservación de scroll
- ~1490-1600: Sistema completo de polling y verificación
- ~1120-1140: Estilos CSS para animaciones

**Funciones agregadas**:
1. `verificarNuevosMensajes()` - Polling principal
2. `mostrarNotificacionNuevoMensaje()` - Notificaciones visuales
3. `cargarMensajesRecibidos(preserveScroll)` - Carga con preservación de scroll

**Event Listeners agregados**:
1. `visibilitychange` - Detecta cambio de pestaña
2. `beforeunload` - Limpia recursos al salir
3. Click en tabs - Actualiza estado activo

## Testing Recomendado

### Prueba 1: Mensajería Básica
1. Abrir dashboard en dos navegadores (Usuario A y Usuario B)
2. Usuario A envía mensaje a Usuario B
3. Verificar que Usuario B recibe el mensaje en máximo 5 segundos
4. Verificar que badge se actualiza

### Prueba 2: Múltiples Mensajes
1. Enviar varios mensajes consecutivos
2. Verificar que todos llegan
3. Verificar que el contador es correcto

### Prueba 3: Cambio de Pestaña
1. Usuario B cambia a otra pestaña del navegador
2. Usuario A envía mensaje
3. Usuario B vuelve a la pestaña
4. Verificar actualización inmediata

### Prueba 4: Scroll Preservation
1. Usuario B tiene varios mensajes
2. Hacer scroll hacia arriba
3. Esperar que llegue nuevo mensaje
4. Verificar que el scroll no salta al inicio

## Mejoras Futuras Posibles

1. **WebSockets**: Para mensajería verdaderamente instantánea
2. **Sonido de notificación**: Audio sutil al recibir mensaje
3. **Notificaciones del navegador**: Usar Notification API
4. **Indicador de "escribiendo"**: Mostrar cuando el otro usuario está escribiendo
5. **Confirmación de lectura**: Marcar automáticamente como leído al visualizar
6. **Historial infinito**: Cargar más mensajes al hacer scroll hacia arriba

## Fecha de Implementación
21 de enero de 2026

## Conclusión

El sistema de mensajería en tiempo real está completamente funcional y proporciona una excelente experiencia de usuario sin requerir infraestructura compleja. Los mensajes llegan de forma casi instantánea (máximo 5 segundos de retraso) y el sistema es robusto, eficiente y fácil de mantener.
