<?php

namespace Tests\Feature\Movimientos;

use App\Models\Categoria;
use App\Models\Deuda;
use App\Models\Movimiento;
use App\Models\PagoDeuda;
use Tests\Feature\ModulosTestCase;

class MovimientoTest extends ModulosTestCase
{
    private function datosMovimiento(Categoria $categoria, array $extra = []): array
    {
        return array_merge([
            'tipo' => 'gasto',
            'id_categoria' => $categoria->id_categoria,
            'monto' => '25.50',
            'fecha' => '2026-03-15',
            'descripcion' => 'Compra de prueba',
            'metodo_pago' => 'efectivo',
            'notas' => 'Nota opcional',
        ], $extra);
    }

    public function test_el_listado_muestra_solo_movimientos_propios_y_el_nombre_de_la_categoria(): void
    {
        $usuario = $this->crearUsuario('propietario@example.com');
        $otro = $this->crearUsuario('otro@example.com');
        $categoria = $this->crearCategoria($usuario, ['nombre' => 'Transporte']);
        $categoriaAjena = $this->crearCategoria($otro, ['nombre' => 'Ajena']);

        Movimiento::create([
            'id_usuario' => $usuario->id_usuario,
            'id_categoria' => $categoria->id_categoria,
            'monto' => 10,
            'fecha' => '2026-03-10',
            'tipo' => 'gasto',
            'descripcion' => 'Movimiento propio',
        ]);
        Movimiento::create([
            'id_usuario' => $otro->id_usuario,
            'id_categoria' => $categoriaAjena->id_categoria,
            'monto' => 99,
            'fecha' => '2026-03-11',
            'tipo' => 'gasto',
            'descripcion' => 'Movimiento ajeno',
        ]);

        $respuesta = $this->actingAs($usuario)->get(route('movimientos'));

        $respuesta->assertOk();
        $respuesta->assertSee('Movimiento propio');
        $respuesta->assertSee('Transporte');
        $respuesta->assertDontSee('Movimiento ajeno');
        $respuesta->assertDontSee('Ajena');
    }

    public function test_registra_ingresos_y_gastos_validos_con_id_usuario_del_servidor(): void
    {
        $usuario = $this->crearUsuario();
        $gasto = $this->crearCategoria($usuario);
        $ingreso = $this->crearCategoria($usuario, ['nombre' => 'Salario', 'tipo_movimiento' => 'ingreso']);

        $this->actingAs($usuario)->post(route('movimientos.store'), $this->datosMovimiento($gasto))
            ->assertRedirect(route('movimientos'));

        $this->actingAs($usuario)->post(route('movimientos.store'), $this->datosMovimiento($ingreso, [
            'tipo' => 'ingreso', 'descripcion' => 'Ingreso extra',
        ]))->assertRedirect(route('movimientos'));

        $this->assertSame(2, Movimiento::count());
        $this->assertSame('25.50', (string) Movimiento::first()->monto);
        $this->assertTrue(Movimiento::where('id_usuario', $usuario->id_usuario)->where('tipo', 'ingreso')->exists());
    }

    public function test_rechaza_monto_cero_y_negativo(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);

        $this->actingAs($usuario)->post(route('movimientos.store'), $this->datosMovimiento($categoria, ['monto' => 0]))
            ->assertSessionHasErrors('monto');
        $this->actingAs($usuario)->post(route('movimientos.store'), $this->datosMovimiento($categoria, ['monto' => -5]))
            ->assertSessionHasErrors('monto');
        $this->assertSame(0, Movimiento::count());
    }

    public function test_rechaza_categoria_ajena_inactiva_y_de_tipo_incompatible(): void
    {
        $usuario = $this->crearUsuario();
        $otro = $this->crearUsuario('otro@example.com');
        $ajena = $this->crearCategoria($otro);
        $inactiva = $this->crearCategoria($usuario, ['nombre' => 'Inactiva', 'activa' => false]);
        $ingreso = $this->crearCategoria($usuario, ['nombre' => 'Ingresos', 'tipo_movimiento' => 'ingreso']);

        $this->actingAs($usuario)->post(route('movimientos.store'), $this->datosMovimiento($ajena))
            ->assertSessionHasErrors('id_categoria');
        $this->actingAs($usuario)->post(route('movimientos.store'), $this->datosMovimiento($inactiva))
            ->assertSessionHasErrors('id_categoria');
        $this->actingAs($usuario)->post(route('movimientos.store'), $this->datosMovimiento($ingreso, ['tipo' => 'gasto']))
            ->assertSessionHasErrors('id_categoria');
        $this->assertSame(0, Movimiento::count());
    }

    public function test_rechaza_fechas_y_campos_invalidos(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);

        $this->actingAs($usuario)->post(route('movimientos.store'), $this->datosMovimiento($categoria, ['fecha' => 'no-fecha']))
            ->assertSessionHasErrors('fecha');
        $this->actingAs($usuario)->post(route('movimientos.store'), $this->datosMovimiento($categoria, ['descripcion' => '']))
            ->assertSessionHasErrors('descripcion');
        $this->actingAs($usuario)->post(route('movimientos.store'), $this->datosMovimiento($categoria, ['tipo' => 'otro']))
            ->assertSessionHasErrors('tipo');
    }

    public function test_actualiza_un_movimiento_propio(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);
        $movimiento = Movimiento::create([
            'id_usuario' => $usuario->id_usuario,
            'id_categoria' => $categoria->id_categoria,
            'monto' => 10,
            'fecha' => '2026-03-10',
            'tipo' => 'gasto',
            'descripcion' => 'Original',
        ]);

        $this->actingAs($usuario)->put(route('movimientos.update', $movimiento->id_movimiento),
            $this->datosMovimiento($categoria, ['monto' => '80.00', 'descripcion' => 'Actualizado']))
            ->assertRedirect(route('movimientos'));

        $movimiento->refresh();
        $this->assertSame('80.00', (string) $movimiento->monto);
        $this->assertSame('Actualizado', $movimiento->descripcion);
    }

    public function test_no_puede_modificar_ni_eliminar_movimientos_ajenos(): void
    {
        $usuario = $this->crearUsuario();
        $otro = $this->crearUsuario('otro@example.com');
        $categoriaAjena = $this->crearCategoria($otro);
        $movimientoAjeno = Movimiento::create([
            'id_usuario' => $otro->id_usuario,
            'id_categoria' => $categoriaAjena->id_categoria,
            'monto' => 10,
            'fecha' => '2026-03-10',
            'tipo' => 'gasto',
            'descripcion' => 'Ajeno',
        ]);

        $this->actingAs($usuario)->put(route('movimientos.update', $movimientoAjeno->id_movimiento),
            $this->datosMovimiento($categoriaAjena))->assertNotFound();
        $this->actingAs($usuario)->delete(route('movimientos.destroy', $movimientoAjeno->id_movimiento))
            ->assertNotFound();
        $this->assertSame(1, Movimiento::count());
    }

    public function test_elimina_un_movimiento_propio(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);
        $movimiento = Movimiento::create([
            'id_usuario' => $usuario->id_usuario,
            'id_categoria' => $categoria->id_categoria,
            'monto' => 10,
            'fecha' => '2026-03-10',
            'tipo' => 'gasto',
            'descripcion' => 'A eliminar',
        ]);

        $this->actingAs($usuario)->delete(route('movimientos.destroy', $movimiento->id_movimiento))
            ->assertRedirect(route('movimientos'));

        $this->assertSame(0, Movimiento::count());
    }

    public function test_no_elimina_un_movimiento_asociado_a_un_pago_de_deuda(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);
        $movimiento = Movimiento::create([
            'id_usuario' => $usuario->id_usuario,
            'id_categoria' => $categoria->id_categoria,
            'monto' => 10,
            'fecha' => '2026-03-10',
            'tipo' => 'gasto',
            'descripcion' => 'Pago de deuda',
        ]);
        $deuda = Deuda::create([
            'id_usuario' => $usuario->id_usuario,
            'acreedor' => 'Acreedor',
            'monto_inicial' => 100,
            'fecha_vencimiento' => '2026-04-10',
        ]);
        PagoDeuda::create([
            'id_deuda' => $deuda->id_deuda,
            'id_movimiento' => $movimiento->id_movimiento,
            'monto' => 10,
            'fecha' => '2026-03-10',
        ]);

        $this->actingAs($usuario)->delete(route('movimientos.destroy', $movimiento->id_movimiento))
            ->assertRedirect(route('movimientos'));

        $this->assertSame(1, Movimiento::count());
        $this->assertTrue(Movimiento::where('id_movimiento', $movimiento->id_movimiento)->exists());
    }
}
