<?php

namespace Tests\Feature\Distribucion;

use App\Models\AporteAhorro;
use App\Models\Categoria;
use App\Models\MetaAhorro;
use App\Models\Movimiento;
use App\Models\Usuario;
use Carbon\Carbon;
use Tests\Feature\ModulosTestCase;

class DistribucionTest extends ModulosTestCase
{
    private function establecerPorcentajes(Usuario $usuario, string $necesidades, string $deseos, string $ahorro): void
    {
        $usuario->porcentaje_necesidades = $necesidades;
        $usuario->porcentaje_deseos = $deseos;
        $usuario->porcentaje_ahorro = $ahorro;
        $usuario->save();
    }

    private function crearMovimiento(Usuario $usuario, Categoria $categoria, string $tipo, string $monto): Movimiento
    {
        return Movimiento::create([
            'id_usuario' => $usuario->id_usuario,
            'id_categoria' => $categoria->id_categoria,
            'monto' => $monto,
            'fecha' => Carbon::now()->toDateString(),
            'tipo' => $tipo,
            'descripcion' => 'Movimiento de prueba',
        ]);
    }

    private function crearAporte(Usuario $usuario, string $monto): AporteAhorro
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
            'fecha' => Carbon::now()->toDateString(),
        ]);
    }

    public function test_los_valores_de_referencia_usan_los_porcentajes_del_usuario(): void
    {
        $usuario = $this->crearUsuario('distribucion@example.com', 1000);
        $this->establecerPorcentajes($usuario, '40', '35', '25');

        $necesidad = $this->crearCategoria($usuario, ['nombre' => 'Mercado']);
        $deseo = $this->crearCategoria($usuario, [
            'nombre' => 'Entretenimiento',
            'clasificacion_503020' => 'deseo',
        ]);
        $ingreso = $this->crearCategoria($usuario, [
            'nombre' => 'Ingreso extra',
            'tipo_movimiento' => 'ingreso',
            'clasificacion_503020' => 'no_aplica',
        ]);

        $this->crearMovimiento($usuario, $necesidad, 'gasto', '100.00');
        $this->crearMovimiento($usuario, $deseo, 'gasto', '50.00');
        $this->crearMovimiento($usuario, $ingreso, 'ingreso', '200.00');
        $this->crearAporte($usuario, '30.00');

        $this->actingAs($usuario)->get(route('distribucion'))
            ->assertOk()
            ->assertViewIs('pages.distribucion.index')
            ->assertViewHas('ingresoFijo', 1000.0)
            ->assertViewHas('porcentajeNecesidades', 40.0)
            ->assertViewHas('porcentajeDeseos', 35.0)
            ->assertViewHas('porcentajeAhorro', 25.0)
            ->assertViewHas('referenciaNecesidades', 400.0)
            ->assertViewHas('referenciaDeseos', 350.0)
            ->assertViewHas('referenciaAhorro', 250.0)
            ->assertViewHas('gastosNecesidades', 100.0)
            ->assertViewHas('gastosDeseos', 50.0)
            ->assertViewHas('aportesAhorro', 30.0)
            ->assertViewHas('comparacion', function ($comparacion) {
                return $comparacion->count() === 3
                    && (float) $comparacion[0]['real'] === 100.0
                    && (float) $comparacion[0]['referencia'] === 400.0
                    && (float) $comparacion[0]['diferencia'] === 300.0
                    && (float) $comparacion[0]['porcentajeUtilizado'] === 25.0
                    && (float) $comparacion[2]['real'] === 30.0
                    && (float) $comparacion[2]['referencia'] === 250.0;
            })
            ->assertSee('Referencia vs. gasto real')
            ->assertSee('$ 400');
    }

    public function test_cambiar_porcentajes_actualiza_referencias_sin_modificar_movimientos(): void
    {
        $usuario = $this->crearUsuario('actualizar@example.com', 1000);
        $categoria = $this->crearCategoria($usuario);
        $movimiento = $this->crearMovimiento($usuario, $categoria, 'gasto', '100.00');

        $this->actingAs($usuario)->post(route('distribucion.actualizar'), [
            'porcentaje_necesidades' => '40',
            'porcentaje_deseos' => '35',
            'porcentaje_ahorro' => '25',
        ])
            ->assertRedirect(route('distribucion'))
            ->assertSessionHas('success');

        $usuario->refresh();
        $this->assertSame('40.00', (string) $usuario->porcentaje_necesidades);
        $this->assertSame('35.00', (string) $usuario->porcentaje_deseos);
        $this->assertSame('25.00', (string) $usuario->porcentaje_ahorro);

        $this->actingAs($usuario)->get(route('distribucion'))
            ->assertOk()
            ->assertViewHas('referenciaNecesidades', 400.0)
            ->assertViewHas('referenciaDeseos', 350.0)
            ->assertViewHas('referenciaAhorro', 250.0);

        $this->assertSame(1, Movimiento::count());
        $this->assertSame($movimiento->id_movimiento, Movimiento::first()->id_movimiento);
        $this->assertSame('100.00', (string) Movimiento::first()->monto);
    }

    public function test_rechaza_porcentajes_que_no_suman_cien_sin_modificar_la_configuracion(): void
    {
        $usuario = $this->crearUsuario('suma@example.com', 1000);
        $this->establecerPorcentajes($usuario, '50', '30', '20');

        $this->actingAs($usuario)->post(route('distribucion.actualizar'), [
            'porcentaje_necesidades' => '50',
            'porcentaje_deseos' => '30',
            'porcentaje_ahorro' => '21',
        ])
            ->assertSessionHasErrors('porcentajes');

        $usuario->refresh();
        $this->assertSame('50.00', (string) $usuario->porcentaje_necesidades);
        $this->assertSame('30.00', (string) $usuario->porcentaje_deseos);
        $this->assertSame('20.00', (string) $usuario->porcentaje_ahorro);
    }

    public function test_rechaza_porcentajes_fuera_de_rango(): void
    {
        $usuario = $this->crearUsuario('rango@example.com', 1000);

        $this->actingAs($usuario)->post(route('distribucion.actualizar'), [
            'porcentaje_necesidades' => '101',
            'porcentaje_deseos' => '0',
            'porcentaje_ahorro' => '0',
        ])->assertSessionHasErrors('porcentaje_necesidades');

        $usuario->refresh();
        $this->assertSame('50.00', (string) $usuario->porcentaje_necesidades);
        $this->assertSame('30.00', (string) $usuario->porcentaje_deseos);
        $this->assertSame('20.00', (string) $usuario->porcentaje_ahorro);
    }

    public function test_los_aportes_no_se_contabilizan_como_gastos_ni_generan_movimientos(): void
    {
        $usuario = $this->crearUsuario('aportes@example.com', 1000);
        $this->crearAporte($usuario, '100.00');

        $this->actingAs($usuario)->get(route('distribucion'))
            ->assertOk()
            ->assertViewHas('gastosNecesidades', 0.0)
            ->assertViewHas('gastosDeseos', 0.0)
            ->assertViewHas('aportesAhorro', 100.0)
            ->assertSee('Ahorro');

        $this->assertSame(0, Movimiento::count());
    }

    public function test_la_distribucion_solo_consultas_datos_del_usuario_autenticado(): void
    {
        $usuario = $this->crearUsuario('dist-propio@example.com', 1000);
        $otro = $this->crearUsuario('dist-ajeno@example.com', 5000);
        $categoriaAjena = $this->crearCategoria($otro, ['nombre' => 'Ajena']);

        $this->crearMovimiento($otro, $categoriaAjena, 'gasto', '500.00');
        $this->crearAporte($otro, '300.00');

        $this->actingAs($usuario)->get(route('distribucion'))
            ->assertOk()
            ->assertViewHas('gastosNecesidades', 0.0)
            ->assertViewHas('gastosDeseos', 0.0)
            ->assertViewHas('aportesAhorro', 0.0)
            ->assertDontSee('Ajena');
    }
}
