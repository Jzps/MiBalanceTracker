<?php
/**
 * index.php — Front controller (enrutador) del sistema.
 *
 * Reglas de enrutado:
 *   1. Sin parámetro 'page' -> la raíz del proyecto redirige a login.php,
 *      de modo que la primera pantalla siempre sea el acceso al sistema.
 *   2. Con 'page' válido    -> se incluye pages/<archivo>.html (HTML puro)
 *      dentro de la plantilla compartida (header + menú lateral + footer).
 *   3. Con 'page' inválido  -> se muestra la página de error con el listado
 *      de módulos permitidos.
 *
 * El enrutador solo acepta la lista blanca de includes/funciones.php:
 * nunca se incluye una ruta arbitraria recibida del usuario.
 */

require_once __DIR__ . '/includes/funciones.php';

$modulos = modulos();
$slug = $_GET['page'] ?? '';

// --- 1. Raíz del proyecto: la pantalla inicial es el login ----------------
if ($slug === '') {
    header('Location: login.php');
    exit;
}

// --- 2. Comprobación del módulo solicitado -------------------------------
$pagina_valida = is_string($slug) && isset($modulos[$slug]);
$titulo_pagina = $pagina_valida ? $modulos[$slug]['titulo'] : 'Página no encontrada';
$pagina_activa = $pagina_valida ? $slug : '';

require __DIR__ . '/includes/header.php';

// Mensaje de estado opcional (por ejemplo: index.php?page=movimientos&estado=...)
$estado = $_GET['estado'] ?? '';
if (is_string($estado) && $estado !== '') {
    echo '<div class="alerta alerta-info" role="status">' . e($estado) . '</div>';
}

// --- 3. Contenido del módulo ---------------------------------------------
if ($pagina_valida) {
    $archivo_pagina = __DIR__ . '/pages/' . $modulos[$slug]['archivo'];
    if (is_file($archivo_pagina)) {
        require $archivo_pagina; // las vistas son HTML puro, sin lógica PHP
    } else {
        echo '<section class="tarjeta"><h1>Módulo en construcción</h1>'
            . '<p>No se encontró la vista <code>pages/' . e($modulos[$slug]['archivo']) . '</code>.</p></section>';
    }
} else {
    echo '<section class="tarjeta">';
    echo '<h1>Página no encontrada</h1>';
    echo '<p>El módulo solicitado no existe. Elige una de las secciones del menú lateral:</p>';
    echo '<ul class="enlaces-modulos">';
    foreach ($modulos as $clave => $modulo) {
        echo '<li><a href="index.php?page=' . e($clave) . '">' . e($modulo['titulo']) . '</a></li>';
    }
    echo '</ul>';
    echo '</section>';
}

require __DIR__ . '/includes/footer.php';
