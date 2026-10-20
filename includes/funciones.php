<?php
/**
 * includes/funciones.php — Funciones compartidas por el front controller.
 */

/**
 * Lista blanca de módulos del sistema.
 *
 * Es la única fuente de verdad: 'archivo' debe corresponder a un archivo real
 * dentro de pages/ y el enrutador nunca acepta rutas arbitrarias.
 */
function modulos(): array
{
    return [
        'dashboard' => [
            'titulo' => 'Dashboard',
            'archivo' => 'dashboard.html',
            'icono' => '<path d="M4 13h6V4H4v9Zm0 7h6v-5H4v5Zm10 0h6v-9h-6v9Zm0-16v5h6V4h-6Z"/>',
        ],
        'movimientos' => [
            'titulo' => 'Movimientos',
            'archivo' => 'movimientos.html',
            'icono' => '<path d="M7 8V3H4v5H2l3 4 3-4H7Zm10 0 3-4h-2V3h-3v5h-3l3 4 3-4Zm-4 3H5a3 3 0 0 0-3 3v2h2v-2a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v2h2v-2a3 3 0 0 0-3-3Zm-8 6v2h10v-2H5Z"/>',
        ],
        'presupuestos' => [
            'titulo' => 'Presupuestos',
            'archivo' => 'presupuestos.html',
            'icono' => '<path d="M12 2a10 10 0 1 0 10 10h-10V2Zm2 3.2A6 6 0 0 1 18.8 14H14V5.2Z"/>',
        ],
        'metas' => [
            'titulo' => 'Metas',
            'archivo' => 'metas.html',
            'icono' => '<path d="m12 2 2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17l-5.9 3 1.2-6.5L2.5 8.9 9.1 8 12 2Z"/>',
        ],
        'deudas' => [
            'titulo' => 'Deudas',
            'archivo' => 'deudas.html',
            'icono' => '<path d="M4 4h16a1 1 0 0 1 1 1v3a3 3 0 0 0 0 6v3a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-3a3 3 0 0 0 0-6V5a1 1 0 0 1 1-1Zm3 3v2a3 3 0 0 0 0 2v2h2V9a3 3 0 0 1-2-2H7Zm10 0a3 3 0 0 1-2 2v4a3 3 0 0 1 2 0v2h2v-6h-2Z"/>',
        ],
        'analisis' => [
            'titulo' => 'Análisis',
            'archivo' => 'analisis.html',
            'icono' => '<path d="M4 20V10h3v10H4Zm6.5 0V4h3v16h-3ZM17 20v-7h3v7h-3Z"/>',
        ],
        'educacion' => [
            'titulo' => 'Educación financiera',
            'archivo' => 'educacion.html',
            'icono' => '<path d="M12 3 1 8l11 5 9-4.1V15h2V8L12 3ZM5 12.4V16c0 1.7 3.1 3 7 3s7-1.3 7-3v-3.6l-7 3.2-7-3.2Z"/>',
        ],
        'distribucion' => [
            'titulo' => '50/30/20',
            'archivo' => 'distribucion.html',
            'icono' => '<path d="M12 2a10 10 0 1 0 10 10H12V2Zm2 0v8h8A10 10 0 0 0 14 2Zm-4 10a4 4 0 1 1-4 4 4 4 0 0 1 4-4Z"/>',
        ],
        'configuracion' => [
            'titulo' => 'Configuración',
            'archivo' => 'configuracion.html',
            'icono' => '<path d="m12 8a4 4 0 1 0 4 4 4 4 0 0 0-4-4Zm8.9 4a7 7 0 0 0-.1-1.2l2-1.6-2-3.4-2.4 1a7.4 7.4 0 0 0-2-1.2L16 3h-4l-.4 2.6a7.4 7.4 0 0 0-2 1.2l-2.4-1-2 3.4 2 1.6a7.6 7.6 0 0 0 0 2.4l-2 1.6 2 3.4 2.4-1a7.4 7.4 0 0 0 2 1.2L12 21h4l.4-2.6a7.4 7.4 0 0 0 2-1.2l2.4 1 2-3.4-2-1.6a7 7 0 0 0 .1-1.2Z"/>',
        ],
    ];
}

/** Escapa un texto para poder imprimirlo dentro de HTML. */
function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}
