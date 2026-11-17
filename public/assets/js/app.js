/* ==========================================================================
   Finanzas Personales — JavaScript del frontend

     1. Menú lateral en pantallas pequeñas (navegación).
     2. Mostrar u ocultar la contraseña (login, registro y configuración).
     3. Los formularios de los módulos son visuales: se evita que envíen
        información a un archivo de acción que todavía no tiene lógica.

   Todos los selectores se comprueban antes de usarse, de modo que una página
   sin un formulario concreto no produce errores en la consola.
   ========================================================================== */
(function () {
    'use strict';

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
       navegador (required, type, min, max) sigue funcionando.
       Los formularios de autenticación sí se envían al backend Laravel. */
    $$('form:not(#frm-login):not(#frm-registro):not(#frm-ingreso-inicial):not(#frm-logout)').forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            evento.preventDefault();
        });
    });
})();
