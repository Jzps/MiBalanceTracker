<?php
/**
 * actions/configuracion.php — Punto de integración futuro (V1: sin procesar).
 *
 * aquí se conectará con la base de datos:
 *   - accion=guardar_ingreso  -> UPDATE usuario.ingreso_fijo_mensual
 *   - accion=guardar_perfil   -> UPDATE usuario.nombre / correo
 * Se redirige de vuelta a la página de Configuración.
 */

$accion = (string) ($_POST['accion'] ?? '');

header('Location: ../index.php?page=configuracion');
exit;
