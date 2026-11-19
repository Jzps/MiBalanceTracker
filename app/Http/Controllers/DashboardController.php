<?php

namespace App\Http\Controllers;

use App\Models\AporteAhorro;
use App\Models\MetaAhorro;
use App\Models\Movimiento;
use App\Models\Presupuesto;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Resumen de la situación financiera del usuario autenticado.
     */
    public function index()
    {
        $idUsuario = Auth::id();

        $usuario = Auth::user();

        $inicioMes = Carbon::now()->startOfMonth()->toDateString();
        $finMes = Carbon::now()->endOfMonth()->toDateString();

        // Ingresos y gastos registrados durante el mes.
        $ingresosAdicionales = (float) Movimiento::query()
            ->where('id_usuario', $idUsuario)
            ->where('tipo', 'ingreso')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('monto');

        $ingresoFijo = (float) ($usuario->ingreso_fijo_mensual ?? 0);

        $ingresos = $ingresoFijo + $ingresosAdicionales;

        $gastos = (float) Movimiento::query()
            ->where('id_usuario', $idUsuario)
            ->where('tipo', 'gasto')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('monto');

        $disponible = $ingresos - $gastos;

        // Aportes de ahorro realizados durante el mes.
        $ahorro = (float) AporteAhorro::query()
            ->whereHas('metaAhorro', function ($consulta) use ($idUsuario) {
                $consulta->where('id_usuario', $idUsuario);
            })
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('monto');

        // Presupuestos y gastos acumulados.
        $presupuestos = Presupuesto::query()
            ->where('id_usuario', $idUsuario)
            ->with('categoria')
            ->orderByDesc('fecha_inicio')
            ->get()
            ->map(function (Presupuesto $presupuesto) use ($idUsuario) {
                $gastado = (float) Movimiento::query()
                    ->where('id_usuario', $idUsuario)
                    ->where('id_categoria', $presupuesto->id_categoria)
                    ->where('tipo', 'gasto')
                    ->whereBetween('fecha', [
                        $presupuesto->fecha_inicio->toDateString(),
                        $presupuesto->fecha_fin->toDateString(),
                    ])
                    ->sum('monto');

                $limite = (float) $presupuesto->monto_limite;

                $porcentaje = $limite > 0
                    ? round(($gastado / $limite) * 100, 2)
                    : 0;

                return [
                    'presupuesto' => $presupuesto,
                    'categoria' => $presupuesto->categoria,
                    'gastado' => $gastado,
                    'disponible' => $limite - $gastado,
                    'porcentaje' => $porcentaje,
                    'anchoBarra' => min($porcentaje, 100),
                ];
            });

        // Metas de ahorro y progreso acumulado.
        $metas = MetaAhorro::query()
            ->where('id_usuario', $idUsuario)
            ->with('aportes')
            ->orderByDesc('fecha_objetivo')
            ->get()
            ->map(function (MetaAhorro $meta) {
                $acumulado = (float) $meta->aportes->sum('monto');
                $objetivo = (float) $meta->monto_objetivo;

                $progreso = $objetivo > 0
                    ? round(($acumulado / $objetivo) * 100, 2)
                    : 0;

                return [
                    'meta' => $meta,
                    'acumulado' => $acumulado,
                    'pendiente' => max($objetivo - $acumulado, 0),
                    'progreso' => $progreso,
                    'anchoBarra' => min($progreso, 100),
                ];
            });

        // Últimos movimientos registrados.
        $movimientos = Movimiento::query()
            ->where('id_usuario', $idUsuario)
            ->with('categoria')
            ->orderByDesc('fecha')
            ->orderByDesc('id_movimiento')
            ->limit(5)
            ->get();

        // Valores de referencia de la distribución 50/30/20.
        $porcentajeNecesidades = (float) $usuario->porcentaje_necesidades;
        $porcentajeDeseos = (float) $usuario->porcentaje_deseos;
        $porcentajeAhorro = (float) $usuario->porcentaje_ahorro;

        $referenciaNecesidades = $ingresoFijo * ($porcentajeNecesidades / 100);
        $referenciaDeseos = $ingresoFijo * ($porcentajeDeseos / 100);
        $referenciaAhorro = $ingresoFijo * ($porcentajeAhorro / 100);

        return view('pages.dashboard.index', compact(
            'usuario',
            'ingresoFijo',
            'ingresosAdicionales',
            'ingresos',
            'gastos',
            'disponible',
            'ahorro',
            'presupuestos',
            'metas',
            'movimientos',
            'porcentajeNecesidades',
            'porcentajeDeseos',
            'porcentajeAhorro',
            'referenciaNecesidades',
            'referenciaDeseos',
            'referenciaAhorro'
        ) + [
            'tituloPagina' => 'Dashboard',
            'paginaActiva' => 'dashboard',
        ]);
    }
}