<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AnalisisController extends Controller
{
    /**
     * Consulta y presenta los análisis financieros del usuario autenticado.
     */
    public function index()
    {
        $idUsuario = Auth::id();

        $fechaActual = Carbon::now();
        $inicioPeriodo = $fechaActual->copy()->startOfMonth()->subMonths(2);
        $finPeriodo = $fechaActual->copy()->endOfMonth();

        // Movimientos registrados durante los últimos tres meses.
        $movimientos = Movimiento::query()
            ->where('id_usuario', $idUsuario)
            ->whereBetween('fecha', [
                $inicioPeriodo->toDateString(),
                $finPeriodo->toDateString(),
            ])
            ->with('categoria')
            ->get();

        // Totales generales del periodo.
        $ingresos = (float) $movimientos
            ->where('tipo', 'ingreso')
            ->sum('monto');

        $gastos = (float) $movimientos
            ->where('tipo', 'gasto')
            ->sum('monto');

        $ahorro = $ingresos - $gastos;

        // Agrupación de gastos por categoría.
        $gastosPorCategoria = $movimientos
            ->where('tipo', 'gasto')
            ->groupBy(function ($movimiento) {
                return $movimiento->categoria->nombre ?? 'Sin categoría';
            })
            ->map(function ($grupo, $categoria) {
                return [
                    'categoria' => $categoria,
                    'total' => (float) $grupo->sum('monto'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $mayorGastoCategoria = (float) $gastosPorCategoria->max('total');

        $gastosPorCategoria = $gastosPorCategoria->map(function ($item) use ($mayorGastoCategoria) {
            $item['porcentaje'] = $mayorGastoCategoria > 0
                ? round(($item['total'] / $mayorGastoCategoria) * 100, 2)
                : 0;

            return $item;
        });

        // Evolución mensual de los gastos.
        $evolucionGastos = collect();

        // Comparación de ingresos, gastos, ahorro y disponible por mes.
        $comparacionMensual = collect();

        for ($i = 2; $i >= 0; $i--) {
            $fechaMes = $fechaActual->copy()->startOfMonth()->subMonths($i);

            $movimientosMes = $movimientos->filter(function ($movimiento) use ($fechaMes) {
                return $movimiento->fecha->format('Y-m') === $fechaMes->format('Y-m');
            });

            $ingresosMes = (float) $movimientosMes
                ->where('tipo', 'ingreso')
                ->sum('monto');

            $gastosMes = (float) $movimientosMes
                ->where('tipo', 'gasto')
                ->sum('monto');

            $ahorroMes = $ingresosMes - $gastosMes;

            $nombreMes = $fechaMes->locale('es')->translatedFormat('F Y');

            $evolucionGastos->push([
                'periodo' => ucfirst($nombreMes),
                'total' => $gastosMes,
            ]);

            $comparacionMensual->push([
                'periodo' => ucfirst($nombreMes),
                'ingresos' => $ingresosMes,
                'gastos' => $gastosMes,
                'ahorro' => $ahorroMes,
                'disponible' => $ahorroMes,
            ]);
        }

        $mayorGastoMensual = (float) $evolucionGastos->max('total');

        $evolucionGastos = $evolucionGastos->map(function ($item) use ($mayorGastoMensual) {
            $item['porcentaje'] = $mayorGastoMensual > 0
                ? round(($item['total'] / $mayorGastoMensual) * 100, 2)
                : 0;

            return $item;
        });

        return view('pages.analisis.index', compact(
            'ingresos',
            'gastos',
            'ahorro',
            'gastosPorCategoria',
            'evolucionGastos',
            'comparacionMensual'
        ) + [
            'tituloPagina' => 'Análisis',
            'paginaActiva' => 'analisis',
        ]);
    }
}