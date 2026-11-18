<?php

namespace Tests\Feature\Deudas;

use App\Models\Categoria;
use App\Models\Deuda;
use App\Models\Movimiento;
use App\Models\PagoDeuda;
use Tests\Feature\ModulosTestCase;

class PagoDeudaTest extends ModulosTestCase
{
    private function crearDeuda($usuario, array $extra = []): Deuda
    {
        return Deuda::create(array_merge([
            'id_usuario' => $usuario->id_usuario,
            'acreedor' => 'Acreedor',
            'monto_inicial' => 100,
            'fecha_vencimiento' => '2026-12-31',
            'pago_minimo' => 0,
            'estado' => 'pendiente',
        ], $extra));
    }

    private function datosPago(Categoria $categoria, array $extra = []): array
    {
        return array_merge([
            'monto' => '40.00',
            'fecha' => '2026-03-10',
            'id_categoria' => $categoria->id_categoria,
            'descripcion' => 'Abono inicial',
        ], $extra);
    }

    public function test_pago_parcial_crea_un_movimiento_y_un_pago_relacionado(): void
    {
        $usuario = $this->crearUsuario();
        $deuda = $this->crearDeuda($usuario);
        $categoria = $this->crearCategoria($usuario);

        $this->actingAs($usuario)->post(route('pagos.store', $deuda->id_deuda),
            $this->datosPago($categoria))->assertRedirect(route('deudas'));

        $this->assertSame(1, Movimiento::count());
        $this->assertSame(1, PagoDeuda::count());

        $movimiento = Movimiento::firstOrFail();
        $pago = PagoDeuda::firstOrFail();

        $this->assertSame($movimiento->id_movimiento, $pago->id_movimiento);
        $this->assertSame($deuda->id_deuda, $pago->id_deuda);
        $this->assertSame('40.00', (string) $movimiento->monto);
        $this->assertSame('40.00', (string) $pago->monto);
        $this->assertSame($movimiento->fecha->toDateString(), $pago->fecha->toDateString());
        $this->assertSame('gasto', $movimiento->tipo);
        $this->assertSame($usuario->id_usuario, $movimiento->id_usuario);
        $this->assertSame('Abono inicial', $movimiento->descripcion);
        $this->assertSame('pendiente', $deuda->fresh()->estado);
    }

    public function test_pago_que_completa_la_deuda_actualiza_el_estado(): void
    {
        $usuario = $this->crearUsuario();
        $deuda = $this->crearDeuda($usuario, ['monto_inicial' => 100]);
        $categoria = $this->crearCategoria($usuario);

        $this->actingAs($usuario)->post(route('pagos.store', $deuda->id_deuda),
            $this->datosPago($categoria, ['monto' => '100.00']))
            ->assertRedirect(route('deudas'));

        $this->assertSame('pagada', $deuda->fresh()->estado);
        $html = $this->actingAs($usuario)->get(route('deudas'))->getContent();
        $this->assertStringContainsString('Abono inicial', $html);
        $this->assertStringContainsString('Movimiento #'.Movimiento::first()->id_movimiento, $html);
    }

    public function test_no_puede_pagar_deuda_ajena(): void
    {
        $usuario = $this->crearUsuario();
        $otro = $this->crearUsuario('otro@example.com');
        $deudaAjena = $this->crearDeuda($otro);
        $categoria = $this->crearCategoria($usuario);

        $this->actingAs($usuario)->post(route('pagos.store', $deudaAjena->id_deuda),
            $this->datosPago($categoria))->assertNotFound();
        $this->assertSame(0, PagoDeuda::count());
        $this->assertSame(0, Movimiento::count());
    }

    public function test_no_acepta_pagos_en_deudas_canceladas_o_pagadas(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);
        $cancelada = $this->crearDeuda($usuario, ['estado' => 'cancelada']);
        $pagada = $this->crearDeuda($usuario, ['estado' => 'pagada']);

        $this->actingAs($usuario)->post(route('pagos.store', $cancelada->id_deuda), $this->datosPago($categoria))
            ->assertRedirect(route('deudas'));
        $this->actingAs($usuario)->post(route('pagos.store', $pagada->id_deuda), $this->datosPago($categoria))
            ->assertRedirect(route('deudas'));
        $this->assertSame(0, PagoDeuda::count());
    }

    public function test_valida_monto_y_rechaza_pago_superior_al_saldo(): void
    {
        $usuario = $this->crearUsuario();
        $deuda = $this->crearDeuda($usuario, ['monto_inicial' => 100]);
        $categoria = $this->crearCategoria($usuario);

        $this->actingAs($usuario)->post(route('pagos.store', $deuda->id_deuda),
            $this->datosPago($categoria, ['monto' => 0]))->assertSessionHasErrors('monto');
        $this->actingAs($usuario)->post(route('pagos.store', $deuda->id_deuda),
            $this->datosPago($categoria, ['monto' => -10]))->assertSessionHasErrors('monto');
        $this->actingAs($usuario)->post(route('pagos.store', $deuda->id_deuda),
            $this->datosPago($categoria, ['monto' => '101.00']))->assertSessionHasErrors('monto');
        $this->assertSame(0, PagoDeuda::count());
    }

    public function test_rechaza_categorias_ajenas_inactivas_y_de_ingreso(): void
    {
        $usuario = $this->crearUsuario();
        $otro = $this->crearUsuario('otro@example.com');
        $deuda = $this->crearDeuda($usuario);
        $ajena = $this->crearCategoria($otro);
        $inactiva = $this->crearCategoria($usuario, ['nombre' => 'Inactiva', 'activa' => false]);
        $ingreso = $this->crearCategoria($usuario, ['nombre' => 'Ingresos', 'tipo_movimiento' => 'ingreso']);

        $this->actingAs($usuario)->post(route('pagos.store', $deuda->id_deuda), $this->datosPago($ajena))
            ->assertSessionHasErrors('id_categoria');
        $this->actingAs($usuario)->post(route('pagos.store', $deuda->id_deuda), $this->datosPago($inactiva))
            ->assertSessionHasErrors('id_categoria');
        $this->actingAs($usuario)->post(route('pagos.store', $deuda->id_deuda), $this->datosPago($ingreso))
            ->assertSessionHasErrors('id_categoria');
        $this->assertSame(0, PagoDeuda::count());
    }

    public function test_si_falla_la_creacion_del_movimiento_no_queda_pago_registrado(): void
    {
        $usuario = $this->crearUsuario();
        $deuda = $this->crearDeuda($usuario);
        $categoria = $this->crearCategoria($usuario);
        $this->actingAs($usuario);
        $this->withoutExceptionHandling();

        Movimiento::created(function () {
            throw new \RuntimeException('Fallo simulado en el movimiento');
        });

        try {
            $this->post(route('pagos.store', $deuda->id_deuda), $this->datosPago($categoria));
        } catch (\RuntimeException $exception) {
            $this->assertSame('Fallo simulado en el movimiento', $exception->getMessage());
        } finally {
            Movimiento::flushEventListeners();
        }

        $this->assertSame(0, Movimiento::count());
        $this->assertSame(0, PagoDeuda::count());
        $this->assertSame('pendiente', $deuda->fresh()->estado);
    }

    public function test_no_se_puede_editar_ni_eliminar_desde_movimientos_un_pago_relacionado(): void
    {
        $usuario = $this->crearUsuario();
        $deuda = $this->crearDeuda($usuario);
        $categoria = $this->crearCategoria($usuario);

        $this->actingAs($usuario)->post(route('pagos.store', $deuda->id_deuda), $this->datosPago($categoria));
        $movimiento = Movimiento::firstOrFail();

        $this->actingAs($usuario)->put(route('movimientos.update', $movimiento->id_movimiento), [
            'tipo' => 'gasto', 'id_categoria' => $categoria->id_categoria, 'monto' => '99',
            'fecha' => '2026-03-10', 'descripcion' => 'Intento de edición',
        ])->assertRedirect(route('movimientos'));

        $this->actingAs($usuario)->delete(route('movimientos.destroy', $movimiento->id_movimiento))
            ->assertRedirect(route('movimientos'));

        $this->assertSame('40.00', (string) $movimiento->fresh()->monto);
        $this->assertSame('Abono inicial', $movimiento->fresh()->descripcion);
        $this->assertSame(1, Movimiento::count());
    }
}
