<?php

/**
 * Laravel - Proxy para despliegues en cPanel.
 *
 * Este archivo redirige las peticiones al front-controller
 * ubicado en public/index.php sin exponer la carpeta public/
 * en la URL del navegador.
 */

require __DIR__.'/public/index.php';
