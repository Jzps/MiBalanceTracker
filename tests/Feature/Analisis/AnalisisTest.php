<?php

namespace Tests\Feature\Analisis;

use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\Usuario;
use Carbon\Carbon;
use Tests\Feature\ModulosTestCase;

class AnalisisTest extends ModulosTestCase
{
    private function crearMovimiento(
        Usuario $usuario,
        Categoria $categoria,
        string $tipo,
        string $monto,
        string $fecha,
        string $descripcion = 'Movimiento de prueba'
    ): Movimiento {
        return Movimiento::create([
            'id_usuario' => $usuario->id_usuario,
            'id_categoria' => $categoria->id_categoria,
            'monto' => $monto,
            'fecha' => $fecha,
            'tipo' => $tipo,
            'descripcion' => $descripcion,
        ]);
    }

    public function test_el_analisis_calcula_totales_y_agrupa_gastos_por_categoria(): void
    {
        $usuario = $this->crearUsuario('analisis@example.com', 1000);
        $alimentacion = $this->crearCategoria($usuario, ['nombre' => 'Alimentación']);
        $entretenimiento = $this->crearCategoria($usuario, [
            'nombre' => 'Entretenimiento',
            'clasificacion_503020' => 'deseo',
        ]);
        $ingresos = $this->crearCategoria($usuario, [
            'nombre' => 'Ingreso extra',
            'tipo_movimiento' => 'ingreso',
            'clasificacion_503020' => 'no_aplica',
        ]);

        $this->crearMovimiento($usuario, $ingresos, 'ingreso', '300.00', Carbon::now()->toDateString());
        $this->crearMovimiento($usuario, $alimentacion, 'gasto', '120.00', Carbon::now()->toDateString());
        $this->crearMovimiento($usuario, $entretenimiento, 'gasto', '80.00', Carbon::now()->subMonth()->startOfMonth()->toDateString());
        $this->crearMovimiento($usuario, $alimentacion, 'gasto', '50.00', Carbon::now()->subMonths(2)->startOfMonth()->toDateString());

        $this->actingAs($usuario)->get(route('analisis'))
            ->assertOk()
            ->assertViewIs('pages.analisis.index')
            ->assertViewHas('ingresos', 300.0)
            ->assertViewHas('gastos', 250.0)
            ->assertViewHas('ahorro', 50.0)
            ->assertViewHas('gastosPorCategoria', function ($grupos) {
                return $grupos->count() === 2
                    && $grupos[0]['categoria'] === 'Alimentación'
                    && (float) $grupos[0]['total'] === 170.0
                    && (float) $grupos[0]['porcentaje'] === 100.0
                    && $grupos[1]['categoria'] === 'Entretenimiento'
                    && (float) $grupos[1]['total'] === 80.0
                    && (float) $grupos[1]['porcentaje'] === 47.06;
            })
            ->assertSee('Gastos por categoría')
            ->assertSee('Alimentación')
            ->assertSee('Entretenimiento');
    }

    public function test_la_comparacion_mensual_corresponde_a_los_tres_periodos(): void
    {
        $usuario = $this->crearUsuario('periodos@example.com', 1000);
        $categoria = $this->crearCategoria($usuario);

        $fechaActual = Carbon::now();
        $this->crearMovimiento($usuario, $categoria, 'gasto', '10.00', $fechaActual->copy()->subMonths(2)->startOfMonth()->toDateString());
        $this->crearMovimiento($usuario, $categoria, 'gasto', '20.00', $fechaActual->copy()->subMonth()->startOfMonth()->toDateString(), 'GASTO_MES_ANTERIOR');
        $this->crearMovimiento($usuario, $categoria, 'gasto', '30.00', $fechaActual->copy()->toDateString(), 'GASTO_MES_ACTUAL');

        $this->actingAs($usuario)->get(route('analisis'))
            ->assertOk()
            ->assertViewHas('comparacionMensual', function ($periodos) use ($fechaActual) {
                if ($periodos->count() !== 3) {
                    return false;
                }

                $esperados = [];
                for ($i = 2; $i >= 0; $i--) {
                    $esperados[] = $fechaActual->copy()->startOfMonth()->subMonths($i)->format('Y-m');
                }

                foreach ($periodos as $indice => $periodo) {
                    $fechaPeriodo = Carbon::createFromFormat('Y-m', $esperados[$indice])->startOfMonth();
                    $nombreEsperado = ucfirst($fechaPeriodo->locale('es')->translatedFormat('F Y'));
                    $totalEsperado = [10.0, 20.0, 30.0][$indice];

                    if (($periodo['gastos'] ?? null) != $totalEsperado
                        || ($periodo['ingresos'] ?? null) != 0.0
                        || ($periodo['ahorro'] ?? null) != -$totalEsperado
                        || ($periodo['periodo'] ?? null) !== $nombreEsperado) {
                        return false;
                    }
                }

                return true;
            })
            ->assertViewHas('evolucionGastos', function ($evolucion) {
                return $evolucion->count() === 3
                    && (float) $evolucion[2]['total'] === 30.0
                    && (float) $evolucion[2]['porcentaje'] === 100.0;
            })
            ->assertSee('Comparación mensual');
    }

    public function test_los_graficos_sin_datos_no_generan_errores_de_presentacion(): void
    {
        $usuario = $this->crearUsuario('sindatos@example.com', 1000);

        $this->actingAs($usuario)->get(route('analisis'))
            ->assertOk()
            ->assertViewHas('ingresos', 0.0)
            ->assertViewHas('gastos', 0.0)
            ->assertViewHas('ahorro', 0.0)
            ->assertViewHas('gastosPorCategoria', fn ($grupos) => $grupos->isEmpty())
            ->assertViewHas('evolucionGastos', function ($evolucion) {
                return $evolucion->count() === 3
                    && $evolucion->every(fn ($item) => (float) $item['total'] === 0.0 && (float) $item['porcentaje'] === 0.0);
            })
            ->assertSee('Sin datos');
    }

    public function test_el_analisis_solo_consultas_datos_del_usuario_autenticado(): void
    {
        $usuario = $this->crearUsuario('analisis-propio@example.com', 1000);
        $otro = $this->crearUsuario('analisis-ajeno@example.com', 4000);
        $categoriaPropia = $this->crearCategoria($usuario, ['nombre' => 'Propia']);
        $categoriaAjena = $this->crearCategoria($otro, ['nombre' => 'Ajena']);

        $this->crearMovimiento($usuario, $categoriaPropia, 'gasto', '40.00', Carbon::now()->toDateString(), 'GASTO_ANALISIS_PROPIO');
        $this->crearMovimiento($otro, $categoriaAjena, 'gasto', '900.00', Carbon::now()->toDateString(), 'GASTO_ANALISIS_AJENO');

        $this->actingAs($usuario)->get(route('analisis'))
            ->assertOk()
            ->assertViewHas('gastos', 40.0)
            ->assertSee('Propia')
            ->assertDontSee('Ajena');
    }
}
