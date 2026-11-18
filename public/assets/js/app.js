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

    /* ---------- 3. Formularios visuales sin backend ---------- */
    /* Solo se cancela el envío de los formularios que aún no tienen una acción
       conectada (los antiguos actions/*.php de módulos no implementados).
       Los formularios de autenticación y de movimientos/categorías/presupuestos
       sí se envían al backend Laravel. */
    $$('form').forEach(function (form) {
        var accion = form.getAttribute('action') || '';
        if (accion === '' || accion.indexOf('actions/') !== -1) {
            form.addEventListener('submit', function (evento) {
                evento.preventDefault();
            });
        }
    });

    /* ---------- 4. Categorías compatibles con el tipo de movimiento ---------- */
    var tipoMovimiento = $('#tipo');
    var selectorCategoria = $('#id_categoria');

    if (tipoMovimiento && selectorCategoria) {
        var filtrarCategorias = function () {
            var tipo = tipoMovimiento.value;
            $$('option', selectorCategoria).forEach(function (opcion) {
                if (!opcion.value) {
                    return;
                }
                var oculta = Boolean(tipo) && opcion.getAttribute('data-tipo') !== tipo;
                opcion.hidden = oculta;
                opcion.disabled = oculta;
            });
            var seleccionada = selectorCategoria.querySelector('option:checked');
            if (seleccionada && seleccionada.hidden) {
                selectorCategoria.value = '';
            }
        };
        tipoMovimiento.addEventListener('change', filtrarCategorias);
        filtrarCategorias();
    }

    /* ---------- 5. Acción dinámica de aportes y pagos ---------- */
    var formulariosDinamicos = [
        { form: '#frm-aporte', selector: '#id_meta' },
        { form: '#frm-pago', selector: '#id_deuda' }
    ];

    formulariosDinamicos.forEach(function (config) {
        var formulario = $(config.form);
        var selector = $(config.selector);
        if (!formulario || !selector) {
            return;
        }
        var base = formulario.getAttribute('data-base') || '';
        var actualizarAccion = function () {
            if (selector.value && base.indexOf('__') !== -1) {
                formulario.action = base.replace('__META__', selector.value).replace('__DEUDA__', selector.value);
            }
        };
        selector.addEventListener('change', actualizarAccion);
        actualizarAccion();
    });
})();
