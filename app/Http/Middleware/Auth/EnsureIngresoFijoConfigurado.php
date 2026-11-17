<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIngresoFijoConfigurado
{
    /**
     * Impide acceder a los módulos mientras el ingreso fijo mensual esté pendiente.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario && (is_null($usuario->ingreso_fijo_mensual) || (float) $usuario->ingreso_fijo_mensual <= 0)) {
            return redirect()->route('ingreso.inicial');
        }

        return $next($request);
    }
}
