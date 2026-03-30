# 🧪 Instrucciones para Prueba Manual del Chat en Tiempo Real

## Preparación

### Requisitos
- 2 navegadores diferentes (Chrome y Firefox) O 2 ventanas de incógnito
- 2 usuarios con credenciales válidas
- Servidor Laravel corriendo (`php artisan serve`)

---

## 📝 Prueba 1: Envío y Recepción Básica

### Paso 1: Configurar Navegadores
1. **Navegador A**: Abrir `http://127.0.0.1:8000`
2. **Navegador B**: Abrir `http://127.0.0.1:8000` (ventana de incógnito)

### Paso 2: Iniciar Sesión
1. **Navegador A**: Login con Usuario 1 (ej: admin)
2. **Navegador B**: Login con Usuario 2 (ej: estudiante)

### Paso 3: Enviar Mensaje
**En Navegador A**:
1. Ir al dashboard
2. Localizar el widget de chat (lado derecho)
3. En el tab "Enviar":
   - Buscar al Usuario 2 en el campo de búsqueda
   - Seleccionar al usuario de los resultados
   - Escribir mensaje: "Hola, esta es una prueba"
   - Clic en "Enviar Mensaje"

### Paso 4: Verificar Recepción
**En Navegador B** (NO refrescar):
1. Esperar máximo 5 segundos
2. ✅ Verificar que aparezca badge rojo en tab "Recibidos"
3. ✅ Verificar número en el badge (debe ser 1)
4. ✅ Verificar que el badge pulse (animación)
5. ✅ Verificar badge en botón flotante (esquina inferior derecha)

### Paso 5: Ver Mensaje
**En Navegador B**:
1. Clic en tab "Recibidos"
2. ✅ Verificar que aparezca el mensaje
3. ✅ Verificar notificación temporal: "Nuevo mensaje recibido"
4. ✅ Verificar datos del mensaje:
   - Nombre del remitente
   - Contenido del mensaje
   - Fecha y hora
   - Badge "Nuevo" si no está leído

### Resultado Esperado
✅ Mensaje recibido en máximo 5 segundos
✅ Badges actualizados automáticamente
✅ Notificación mostrada
✅ Sin necesidad de refrescar navegador

---

## 📝 Prueba 2: Múltiples Mensajes

### Paso 1: Enviar Varios Mensajes
**En Navegador A**:
1. Enviar mensaje 1: "Primer mensaje"
2. Esperar 2 segundos
3. Enviar mensaje 2: "Segundo mensaje"
4. Esperar 2 segundos
5. Enviar mensaje 3: "Tercer mensaje"

### Paso 2: Verificar Recepción
**En Navegador B** (NO refrescar):
1. ✅ Badge debe mostrar "3"
2. ✅ Badge debe pulsar
3. Abrir tab "Recibidos"
4. ✅ Deben aparecer los 3 mensajes
5. ✅ Orden correcto (más reciente primero)

### Resultado Esperado
✅ Todos los mensajes recibidos
✅ Contador correcto en badge
✅ Orden cronológico correcto

---

## 📝 Prueba 3: Scroll y Nuevos Mensajes

### Paso 1: Preparar Mensajes
**En Navegador A**:
1. Enviar 10 mensajes consecutivos al Usuario 2
2. Esperar que todos lleguen

### Paso 2: Hacer Scroll
**En Navegador B**:
1. Abrir tab "Recibidos"
2. Hacer scroll hacia arriba (ver mensajes antiguos)
3. Mantener posición en medio de la lista

### Paso 3: Enviar Nuevo Mensaje
**En Navegador A**:
1. Enviar mensaje: "Mensaje nuevo mientras haces scroll"

### Paso 4: Verificar Scroll
**En Navegador B** (NO mover scroll):
1. Esperar 5 segundos
2. ✅ Verificar que el scroll NO salte al inicio
3. ✅ Verificar que aparezca notificación
4. ✅ Verificar que badge se actualice
5. Hacer scroll hacia arriba
6. ✅ Verificar que el nuevo mensaje esté ahí

### Resultado Esperado
✅ Scroll mantiene posición
✅ Nuevo mensaje se agrega sin interrumpir lectura
✅ Notificación aparece

---

## 📝 Prueba 4: Cambio de Pestaña del Navegador

### Paso 1: Cambiar de Pestaña
**En Navegador B**:
1. Abrir nueva pestaña (ej: Google)
2. Dejar el dashboard en pestaña de fondo

### Paso 2: Enviar Mensaje
**En Navegador A**:
1. Enviar mensaje: "Mensaje mientras estás en otra pestaña"

### Paso 3: Volver a Pestaña
**En Navegador B**:
1. Esperar 10 segundos
2. Volver a la pestaña del dashboard
3. ✅ Verificar actualización INMEDIATA
4. ✅ Verificar badge actualizado
5. ✅ Verificar mensaje visible

### Resultado Esperado
✅ Actualización inmediata al volver
✅ No necesita esperar los 5 segundos del polling
✅ Badge correcto

---

## 📝 Prueba 5: Conversación Bidireccional

### Paso 1: Usuario A envía
**En Navegador A**:
1. Enviar: "Hola, ¿cómo estás?"

### Paso 2: Usuario B responde
**En Navegador B**:
1. Esperar recepción (máx. 5 seg)
2. Abrir tab "Recibidos"
3. Ver mensaje
4. Cambiar a tab "Enviar"
5. Buscar Usuario 1
6. Responder: "Bien, gracias. ¿Y tú?"

### Paso 3: Usuario A recibe
**En Navegador A** (NO refrescar):
1. Esperar máximo 5 segundos
2. ✅ Verificar badge en tab "Recibidos"
3. Abrir tab "Recibidos"
4. ✅ Ver respuesta

### Paso 4: Continuar Conversación
Repetir el proceso 3-4 veces más

### Resultado Esperado
✅ Conversación fluida
✅ Mensajes llegan en ambas direcciones
✅ Sin necesidad de refrescar
✅ Experiencia similar a WhatsApp/Telegram

---

## 📝 Prueba 6: Notificaciones Visuales

### Paso 1: Observar Notificación
**En Navegador B**:
1. Tener tab "Recibidos" abierto
2. Observar esquina superior derecha

**En Navegador A**:
1. Enviar mensaje

**En Navegador B**:
1. ✅ Verificar notificación aparece en esquina superior derecha
2. ✅ Verificar texto: "Nuevo mensaje recibido"
3. ✅ Verificar color azul corporativo (#2e3a75)
4. ✅ Verificar animación de entrada (desliza desde derecha)
5. ✅ Verificar que desaparece después de 3 segundos

### Resultado Esperado
✅ Notificación visible y clara
✅ Animación suave
✅ Desaparece automáticamente

---

## 📝 Prueba 7: Badge Pulsante

### Paso 1: Observar Badge
**En Navegador B**:
1. Tener mensajes no leídos
2. Observar badge rojo en tab "Recibidos"
3. ✅ Verificar que el badge pulsa (crece y decrece)
4. ✅ Verificar animación continua cada 2 segundos

### Paso 2: Observar Botón Flotante
**En Navegador B**:
1. Localizar botón flotante (esquina inferior derecha)
2. ✅ Verificar badge rojo con número
3. ✅ Verificar que también pulsa

### Resultado Esperado
✅ Badges llaman la atención
✅ Animación suave y no molesta
✅ Sincronización entre badges

---

## 📝 Prueba 8: Consola del Navegador

### Paso 1: Abrir Consola
**En Navegador B**:
1. Presionar F12
2. Ir a pestaña "Console"

### Paso 2: Verificar Logs
✅ Buscar mensajes:
```
"Dashboard Marketplace cargado correctamente"
"Usuario: [Nombre]"
"Chat interno inicializado con actualización en tiempo real"
"Polling cada 5 segundos para nuevos mensajes"
```

### Paso 3: Ejecutar Comandos
En la consola, ejecutar:

```javascript
// Ver estado del polling
console.log('Polling activo:', chatPollingInterval !== null);

// Ver contador de mensajes
console.log('Mensajes actuales:', lastMessageCount);

// Ver si tab está activo
console.log('Tab recibidos activo:', isTabRecibidosActive);

// Forzar verificación manual
verificarNuevosMensajes();
```

### Resultado Esperado
✅ Logs correctos en consola
✅ Variables accesibles
✅ Función manual funciona

---

## 📝 Prueba 9: Rendimiento

### Paso 1: Monitorear Red
**En Navegador B**:
1. Presionar F12
2. Ir a pestaña "Network"
3. Observar peticiones cada 5 segundos

### Paso 2: Verificar Peticiones
✅ Buscar peticiones a `/chat/mensajes`
✅ Verificar frecuencia: cada 5 segundos
✅ Verificar tamaño: ~2-5 KB
✅ Verificar status: 200 OK

### Resultado Esperado
✅ Peticiones regulares cada 5 segundos
✅ Tamaño pequeño
✅ Sin errores

---

## 📝 Prueba 10: Envío Sin Confirmación

### Paso 1: Enviar Mensaje
**En Navegador A**:
1. Escribir mensaje
2. Clic en "Enviar Mensaje"

### Paso 2: Verificar Comportamiento
✅ NO debe aparecer SweetAlert de confirmación
✅ Formulario se limpia inmediatamente
✅ Campo de búsqueda se limpia
✅ Contador de caracteres vuelve a "0 / 4000"
✅ Botón vuelve a estado deshabilitado

### Resultado Esperado
✅ Envío silencioso
✅ Sin interrupciones
✅ Experiencia fluida

---

## 🐛 Problemas Comunes y Soluciones

### Problema 1: Mensajes no llegan
**Solución**:
1. Verificar que el servidor esté corriendo
2. Abrir consola (F12) y buscar errores
3. Verificar ruta `/chat/mensajes` en Network
4. Ejecutar: `php artisan route:list --path=chat`

### Problema 2: Badges no actualizan
**Solución**:
1. Verificar en consola: `chatPollingInterval !== null`
2. Buscar errores de JavaScript
3. Refrescar página y volver a intentar

### Problema 3: Notificaciones no aparecen
**Solución**:
1. Solo aparecen con mensajes NUEVOS
2. Verificar que tab "Recibidos" esté activo
3. No aparecen en la primera carga

### Problema 4: Scroll salta
**Solución**:
1. Verificar que `preserveScroll` esté en `true`
2. Revisar función `cargarMensajesRecibidos()`

---

## ✅ Checklist de Pruebas

- [ ] Prueba 1: Envío y recepción básica
- [ ] Prueba 2: Múltiples mensajes
- [ ] Prueba 3: Scroll y nuevos mensajes
- [ ] Prueba 4: Cambio de pestaña
- [ ] Prueba 5: Conversación bidireccional
- [ ] Prueba 6: Notificaciones visuales
- [ ] Prueba 7: Badge pulsante
- [ ] Prueba 8: Consola del navegador
- [ ] Prueba 9: Rendimiento
- [ ] Prueba 10: Envío sin confirmación

---

## 📊 Criterios de Éxito

### Funcionalidad
✅ Mensajes llegan en máximo 5 segundos
✅ Badges se actualizan automáticamente
✅ Notificaciones aparecen correctamente
✅ Scroll mantiene posición
✅ Sin necesidad de refrescar navegador

### Experiencia de Usuario
✅ Interfaz fluida y responsive
✅ Animaciones suaves
✅ Sin interrupciones molestas
✅ Feedback visual claro

### Rendimiento
✅ Peticiones cada 5 segundos
✅ Tamaño de peticiones pequeño (~2-5 KB)
✅ Sin errores en consola
✅ Sin lag o congelamiento

---

## 📞 Reporte de Resultados

Después de completar todas las pruebas, documentar:

1. **Pruebas exitosas**: [  /10]
2. **Problemas encontrados**: [Listar]
3. **Sugerencias de mejora**: [Listar]
4. **Experiencia general**: [1-10]

---

**Fecha de prueba**: _______________
**Probado por**: _______________
**Navegadores usados**: _______________
**Resultado general**: ✅ APROBADO / ❌ REQUIERE AJUSTES
