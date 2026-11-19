<?php

namespace App\Http\Controllers;

use App\Models\AporteAhorro;
use App\Models\Movimiento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DistribucionController extends Controller
{
    /**
     * Consulta la distribución financiera del usuario autenticado.
     */
    public function index()
    {
        $usuario = Auth::user();
        $idUsuario = Auth::id();

        $inicioMes = Carbon::now()->startOfMonth()->toDateString();
        $finMes = Carbon::now()->endOfMonth()->toDateString();

        $ingresoFijo = (float) $usuario->ingreso_fijo_mensual;

        $porcentajeNecesidades = (float) $usuario->porcentaje_necesidades;
        $porcentajeDeseos = (float) $usuario->porcentaje_deseos;
        $porcentajeAhorro = (float) $usuario->porcentaje_ahorro;

        // Valores de referencia calculados sobre el ingreso fijo mensual.
        $referenciaNecesidades = $ingresoFijo * ($porcentajeNecesidades / 100);
        $referenciaDeseos = $ingresoFijo * ($porcentajeDeseos / 100);
        $referenciaAhorro = $ingresoFijo * ($porcentajeAhorro / 100);

        // Consultar los gastos del usuario durante el mes actual.
        $gastos = Movimiento::query()
            ->where('id_usuario', $idUsuario)
            ->where('tipo', 'gasto')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->with('categoria')
            ->get();

        // Agrupar los gastos según su clasificación 50/30/20.
        $gastosNecesidades = (float) $gastos
            ->filter(fn ($movimiento) => $movimiento->categoria?->clasificacion_503020 === 'necesidad')
            ->sum('monto');

        $gastosDeseos = (float) $gastos
            ->filter(fn ($movimiento) => $movimiento->categoria?->clasificacion_503020 === 'deseo')
            ->sum('monto');

        // Consultar los aportes de ahorro registrados durante el mes actual.
        $aportesAhorro = (float) AporteAhorro::query()
            ->whereHas('metaAhorro', function ($consulta) use ($idUsuario) {
                $consulta->where('id_usuario', $idUsuario);
            })
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('monto');

        // Comparar los valores de referencia con los valores reales.
        $comparacion = collect([
            [
                'nombre' => 'Necesidades',
                'porcentaje' => $porcentajeNecesidades,
                'referencia' => $referenciaNecesidades,
                'real' => $gastosNecesidades,
            ],
            [
                'nombre' => 'Deseos',
                'porcentaje' => $porcentajeDeseos,
                'referencia' => $referenciaDeseos,
                'real' => $gastosDeseos,
            ],
            [
                'nombre' => 'Ahorro',
                'porcentaje' => $porcentajeAhorro,
                'referencia' => $referenciaAhorro,
                'real' => $aportesAhorro,
            ],
        ])->map(function ($item) {
            $item['diferencia'] = $item['referencia'] - $item['real'];
            $item['porcentajeUtilizado'] = $item['referencia'] > 0
                ? round(($item['real'] / $item['referencia']) * 100, 2)
                : 0;
            $item['anchoBarra'] = min($item['porcentajeUtilizado'], 100);

            return $item;
        });

        return view('pages.distribucion.index', compact(
            'usuario',
            'ingresoFijo',
            'porcentajeNecesidades',
            'porcentajeDeseos',
            'porcentajeAhorro',
            'referenciaNecesidades',
            'referenciaDeseos',
            'referenciaAhorro',
            'gastosNecesidades',
            'gastosDeseos',
            'aportesAhorro',
            'comparacion'
        ) + [
            'tituloPagina' => '50/30/20',
            'paginaActiva' => 'distribucion',
        ]);
    }

    /**
     * Actualiza los porcentajes personalizados del usuario.
     */
    public function actualizar(Request $request)
    {
        $datos = $request->validate([
            'porcentaje_necesidades' => ['required', 'numeric', 'min:0', 'max:100'],
            'porcentaje_deseos' => ['required', 'numeric', 'min:0', 'max:100'],
            'porcentaje_ahorro' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $suma = (float) $datos['porcentaje_necesidades']
            + (float) $datos['porcentaje_deseos']
            + (float) $datos['porcentaje_ahorro'];

        if (round($suma, 2) !== 100.00) {
            throw ValidationException::withMessages([
                'porcentajes' => 'La suma de los porcentajes debe ser exactamente 100 %.'
            ]);
        }

        $usuario = Auth::user();

        $usuario->porcentaje_necesidades = $datos['porcentaje_necesidades'];
        $usuario->porcentaje_deseos = $datos['porcentaje_deseos'];
        $usuario->porcentaje_ahorro = $datos['porcentaje_ahorro'];

        $usuario->save();

        return redirect()
            ->route('distribucion')
            ->with('success', 'La distribución se actualizó correctamente.');
    }
}