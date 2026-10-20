<?php
/**
 * includes/navbar.php — Menú de navegación de la aplicación.
 *
 * Renderiza dos zonas de la plantilla compartida:
 *   1. La barra superior (marca, cierre de sesión y botón del menú móvil).
 *   2. El menú lateral con los nueve módulos del sistema.
 *
 * El enlace del módulo activo se marca con la clase "activo" y con
 * aria-current="page" para indicar la sección actual.
 */

$modulos_nav = modulos();
?>
<header class="barra-superior">
    <a class="marca" href="index.php?page=dashboard" aria-label="Ir al Dashboard">
        <img src="assets/img/logo.svg" alt="" width="34" height="34">
        <span class="marca-texto">Mi <strong>BalanceTracker</strong></span>
    </a>

    <div class="barra-acciones">
        <a class="btn btn-fantasma btn-pequeno" href="login.php">Cerrar sesión</a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu-principal">
            <span class="nav-toggle-barra"></span>
            <span class="nav-toggle-barra"></span>
            <span class="nav-toggle-barra"></span>
            <span class="visually-hidden">Abrir o cerrar el menú</span>
        </button>
    </div>
</header>

<aside class="lateral">
    <nav id="menu-principal" class="menu-principal" aria-label="Módulos del sistema">
        <ul>
            <?php foreach ($modulos_nav as $clave => $modulo): ?>
                <?php $activo = ($clave === $pagina_activa); ?>
                <li>
                    <a href="index.php?page=<?= e($clave) ?>"
                       class="<?= $activo ? 'activo' : '' ?>"
                       <?= $activo ? 'aria-current="page"' : '' ?>>
                        <svg class="nav-ico" viewBox="0 0 24 24" aria-hidden="true"><?= $modulo['icono'] ?></svg>
                        <span><?= e($modulo['titulo']) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>
