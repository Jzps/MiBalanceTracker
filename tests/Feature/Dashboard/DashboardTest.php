<?php

namespace Tests\Feature\Dashboard;

use App\Models\AporteAhorro;
use App\Models\Categoria;
use App\Models\MetaAhorro;
use App\Models\Movimiento;
use App\Models\Presupuesto;
use App\Models\Usuario;
use Carbon\Carbon;
use Tests\Feature\ModulosTestCase;

class DashboardTest extends ModulosTestCase
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

    private function crearAporte(Usuario $usuario, string $monto, string $fecha): AporteAhorro
    {
        $meta = MetaAhorro::create([
            'id_usuario' => $usuario->id_usuario,
            'nombre' => 'Meta de prueba',
            'tipo_meta' => 'meta_ahorro',
            'monto_objetivo' => '1000.00',
            'fecha_objetivo' => Carbon::now()->addYear()->toDateString(),
            'estado' => 'activa',
        ]);

        return AporteAhorro::create([
            'id_meta' => $meta->id_meta,
            'monto' => $monto,
            'fecha' => $fecha,
            'nota' => 'Aporte de prueba',
        ]);
    }

    public function test_el_dashboard_calcula_totales_saldos_y_aportes_del_mes(): void
    {
        $usuario = $this->crearUsuario('dashboard@example.com', 1000);
        $gastoCategoria = $this->crearCategoria($usuario, ['nombre' => 'Alimentación']);
        $ingresoCategoria = $this->crearCategoria($usuario, [
            'nombre' => 'Ingreso extra',
            'tipo_movimiento' => 'ingreso',
            'clasificacion_503020' => 'no_aplica',
        ]);

        $hoy = Carbon::now()->toDateString();
        $mesAnterior = Carbon::now()->subMonth()->startOfMonth()->toDateString();

        $this->crearMovimiento($usuario, $ingresoCategoria, 'ingreso', '200.00', $hoy);
        $this->crearMovimiento($usuario, $gastoCategoria, 'gasto', '150.00', $hoy);
        $this->crearMovimiento($usuario, $gastoCategoria, 'gasto', '900.00', $mesAnterior);
        $this->crearAporte($usuario, '100.00', $hoy);

        $this->actingAs($usuario)->get(route('dashboard'))
            ->assertOk()
            ->assertViewIs('pages.dashboard.index')
            ->assertViewHas('ingresoFijo', 1000.0)
            ->assertViewHas('ingresosAdicionales', 200.0)
            ->assertViewHas('ingresos', 1200.0)
            ->assertViewHas('gastos', 150.0)
            ->assertViewHas('disponible', 1050.0)
            ->assertViewHas('ahorro', 100.0)
            ->assertSee('Ingresos del mes')
            ->assertSee('$ 1.200');
    }

    public function test_los_ingresos_adicionales_no_modifican_el_ingreso_fijo(): void
    {
        $usuario = $this->crearUsuario('ingresos@example.com', 1000);
        $categoria = $this->crearCategoria($usuario, [
            'nombre' => 'Ingreso extra',
            'tipo_movimiento' => 'ingreso',
            'clasificacion_503020' => 'no_aplica',
        ]);

        $this->crearMovimiento($usuario, $categoria, 'ingreso', '500.00', Carbon::now()->toDateString());

        $this->actingAs($usuario)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('ingresoFijo', 1000.0)
            ->assertViewHas('ingresosAdicionales', 500.0)
            ->assertViewHas('ingresos', 1500.0);

        $this->assertSame('1000.00', (string) $usuario->fresh()->ingreso_fijo_mensual);
    }

    public function test_el_dashboard_sin_movimientos_muestra_ceros_y_estados_vacios(): void
    {
        $usuario = $this->crearUsuario('vacio@example.com', 800);

        $this->actingAs($usuario)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('ingresos', 800.0)
            ->assertViewHas('gastos', 0.0)
            ->assertViewHas('disponible', 800.0)
            ->assertViewHas('ahorro', 0.0)
            ->assertSee('No hay datos registrados');
    }

    public function test_el_dashboard_calcula_presupuestos_y_metas_existentes(): void
    {
        $usuario = $this->crearUsuario('indicadores@example.com', 2000);
        $categoria = $this->crearCategoria($usuario, ['nombre' => 'Alimentación']);

        Presupuesto::create([
            'id_usuario' => $usuario->id_usuario,
            'id_categoria' => $categoria->id_categoria,
            'monto_limite' => '400.00',
            'fecha_inicio' => Carbon::now()->startOfMonth()->toDateString(),
            'fecha_fin' => Carbon::now()->endOfMonth()->toDateString(),
            'porcentaje_alerta' => '80',
        ]);

        $meta = MetaAhorro::create([
            'id_usuario' => $usuario->id_usuario,
            'nombre' => 'Viaje',
            'tipo_meta' => 'meta_ahorro',
            'monto_objetivo' => '500.00',
            'fecha_objetivo' => Carbon::now()->addYear()->toDateString(),
            'estado' => 'activa',
        ]);

        $this->crearMovimiento($usuario, $categoria, 'gasto', '100.00', Carbon::now()->toDateString());
        AporteAhorro::create([
            'id_meta' => $meta->id_meta,
            'monto' => '125.00',
            'fecha' => Carbon::now()->toDateString(),
        ]);

        $this->actingAs($usuario)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('presupuestos', function ($presupuestos) {
                $item = $presupuestos->first();

                return (float) $item['gastado'] === 100.0
                    && (float) $item['disponible'] === 300.0
                    && (float) $item['porcentaje'] === 25.0;
            })
            ->assertViewHas('metas', function ($metas) {
                $item = $metas->first();

                return (float) $item['acumulado'] === 125.0
                    && (float) $item['pendiente'] === 375.0
                    && (float) $item['progreso'] === 25.0;
            });
    }

    public function test_el_dashboard_solo_consultas_datos_del_usuario_autenticado(): void
    {
        $usuario = $this->crearUsuario('propio@example.com', 1000);
        $otro = $this->crearUsuario('ajeno@example.com', 9000);
        $categoriaPropia = $this->crearCategoria($usuario, ['nombre' => 'Propia']);
        $categoriaAjena = $this->crearCategoria($otro, ['nombre' => 'Ajena']);

        $this->crearMovimiento($usuario, $categoriaPropia, 'gasto', '50.00', Carbon::now()->toDateString(), 'GASTO_PROPIO_MARCA');
        $this->crearMovimiento($otro, $categoriaAjena, 'gasto', '700.00', Carbon::now()->toDateString(), 'GASTO_AJENO_MARCA');
        $this->crearAporte($otro, '600.00', Carbon::now()->toDateString());

        $this->actingAs($usuario)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('gastos', 50.0)
            ->assertViewHas('ahorro', 0.0)
            ->assertSee('GASTO_PROPIO_MARCA')
            ->assertDontSee('GASTO_AJENO_MARCA')
            ->assertDontSee('Ajena');
    }
}
