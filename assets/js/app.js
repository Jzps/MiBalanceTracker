/* ==========================================================================
   Finanzas Personales — JavaScript del frontend

     1. Menú lateral en pantallas pequeñas (navegación).
     2. Mostrar u ocultar la contraseña (login y registro).
     3. Los formularios de los módulos son visuales: se evita que envíen
        información a un archivo de acción que todavía no tiene lógica.
     4. Inicio de sesión de demostración (admin / admin123).

   Todos los selectores se comprueban antes de usarse, de modo que una página
   sin un formulario concreto no produce errores en la consola.
   ========================================================================== */
(function () {
    'use strict';

    /* Credenciales autorizadas para la demostración. */
    var USUARIO_DEMO = 'admin';
    var CLAVE_DEMO = 'admin123';

    function $(selector, contexto) {
        return (contexto || document).querySelector(selector);
    }

    function $$(selector, contexto) {
        return Array.prototype.slice.call((contexto || document).querySelectorAll(selector));
    }

    /* ---------- 1. Menú lateral en pantallas pequeñas ---------- */
    var botonMenu = $('.nav-toggle');
    var menu = $('#menu-principal');

    if (botonMenu && menu) {
        botonMenu.addEventListener('click', function () {
            var abierto = menu.classList.toggle('abierto');
            botonMenu.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        });
    }

    /* ---------- 2. Mostrar u ocultar la contraseña ---------- */
    $$('[data-mostrar-password]').forEach(function (boton) {
        var campo = document.getElementById(boton.getAttribute('data-mostrar-password'));
        if (!campo) {
            return;
        }
        boton.addEventListener('click', function () {
            var visible = campo.type === 'text';
            campo.type = visible ? 'password' : 'text';
            boton.textContent = visible ? 'Ver' : 'Ocultar';
            boton.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
            boton.setAttribute('aria-pressed', visible ? 'false' : 'true');
        });
    });

    /* ---------- 3. Formularios visuales de los módulos ---------- */
    /* Sus botones todavía no ejecutan ninguna operación. Se cancela el envío
       para no recargar la página, mientras que la validación nativa del
       navegador (required, type, min, max) sigue funcionando. */
    $$('form:not(#frm-login)').forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            evento.preventDefault();
        });
    });

    /* ---------- 4. Inicio de sesión de demostración ---------- */
    var formLogin = $('#frm-login');

    if (formLogin) {
        formLogin.addEventListener('submit', function (evento) {
            evento.preventDefault();

            var usuario = $('#usuario');
            var clave = $('#password');
            var mensaje = $('#mensaje');

            if (usuario.value.trim() === USUARIO_DEMO && clave.value === CLAVE_DEMO) {
                window.location.href = 'index.php?page=dashboard';
            } else {
                mensaje.textContent = 'Usuario o contraseña incorrectos. Usa admin / admin123.';
                mensaje.hidden = false;
                clave.focus();
            }
        });
    }
})();
