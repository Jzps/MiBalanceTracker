<?php
/**
 * login.php — Pantalla de acceso.
 *
 * La comparación de credenciales se realiza en JavaScript
 * (assets/js/app.js) contra los valores fijados por el equipo.
 */

require_once __DIR__ . '/includes/funciones.php';

$titulo_pagina = 'Iniciar sesión';
$pagina_activa = '';
$mostrar_navbar = false;
$clase_body = 'pagina-auth';

require __DIR__ . '/includes/header.php';
?>
<section class="auth-caja">
    <div class="auth-cabecera">
        <img src="assets/img/logo.svg" alt="Logotipo de Finanzas Personales" width="56" height="56">
        <h1>Iniciar sesión</h1>
        <p>Acceso al sistema de finanzas personales.</p>
    </div>

    <p class="auth-credenciales">
        Usuario <code>admin</code> · Contraseña <code>admin123</code>
    </p>

    <div id="mensaje" class="alerta alerta-error" role="alert" hidden></div>

    <form id="frm-login" class="formulario" action="login.php" method="post">
        <div class="campo">
            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" required autocomplete="username" placeholder=" ">
        </div>

        <div class="campo">
            <label for="password">Contraseña</label>
            <div class="campo-con-boton">
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder=" ">
                <button type="button" class="btn btn-fantasma" data-mostrar-password="password"
                        aria-label="Mostrar contraseña" aria-pressed="false">Ver</button>
            </div>
        </div>

        <button type="submit" class="btn btn-primario btn-block">Ingresar</button>
    </form>

    <p class="auth-enlace">
        ¿No tienes cuenta? <a href="registro.php">Regístrate</a>
    </p>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
