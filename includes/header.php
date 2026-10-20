<?php
/**
 * includes/header.php — Cabecera común de todas las páginas.
 *
 * Variables opcionales que puede definir quien incluye este archivo:
 *   $titulo_pagina  -> texto del <title> y del encabezado.
 *   $pagina_activa  -> slug del módulo (resalta el enlace del menú lateral).
 *   $mostrar_navbar -> false en las pantallas públicas (login y registro).
 *   $clase_body     -> clases adicionales del <body>.
 */

if (!isset($titulo_pagina)) {
    $titulo_pagina = 'MiBalanceTracker';
}
if (!isset($pagina_activa)) {
    $pagina_activa = '';
}
if (!isset($mostrar_navbar)) {
    $mostrar_navbar = true;
}
if (!isset($clase_body)) {
    $clase_body = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistema de gestión financiera personal: ingresos, gastos, presupuestos, metas y deudas.">
    <title><?= e($titulo_pagina) ?> · MiBalanceTracker</title>
    <link rel="icon" href="assets/favicon/favicon.ico" sizes="any">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="<?= e($clase_body) ?>">
<div class="app">
<?php if ($mostrar_navbar): ?>
    <?php require __DIR__ . '/navbar.php'; ?>
<?php endif; ?>
    <main class="principal">
