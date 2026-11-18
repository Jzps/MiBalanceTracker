<?php

namespace Tests\Feature\Deudas;

use App\Models\Deuda;
use App\Models\Movimiento;
use App\Models\PagoDeuda;
use Tests\Feature\ModulosTestCase;

class DeudaTest extends ModulosTestCase
{
    private function datosDeuda(array $extra = []): array
    {
        return array_merge([
            'acreedor' => 'Banco Ejemplo',
            'monto_inicial' => '1000.00',
            'fecha_vencimiento' => '2026-12-31',
            'pago_minimo' => '50.00',
        ], $extra);
    }

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

    private function crearPago(Deuda $deuda, $monto): void
    {
        $movimiento = Movimiento::create([
            'id_usuario' => $deuda->id_usuario,
            'id_categoria' => $this->crearCategoria(\App\Models\Usuario::find($deuda->id_usuario))->id_categoria,
            'monto' => $monto,
            'fecha' => '2026-03-10',
            'tipo' => 'gasto',
            'descripcion' => 'Pago',
        ]);

        PagoDeuda::create([
            'id_deuda' => $deuda->id_deuda,
            'id_movimiento' => $movimiento->id_movimiento,
            'monto' => $monto,
            'fecha' => '2026-03-10',
            'descripcion' => 'Pago',
        ]);
    }

    public function test_crea_una_deuda_para_el_usuario_autenticado(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario)->post(route('deudas.store'), $this->datosDeuda())
            ->assertRedirect(route('deudas'));

        $deuda = Deuda::firstOrFail();
        $this->assertSame($usuario->id_usuario, $deuda->id_usuario);
        $this->assertSame('pendiente', $deuda->estado);
        $this->assertSame('1000.00', (string) $deuda->monto_inicial);
    }

    public function test_solo_muestra_deudas_propias(): void
    {
        $usuario = $this->crearUsuario();
        $otro = $this->crearUsuario('otro@example.com');
        $this->crearDeuda($usuario, ['acreedor' => 'Propia']);
        $this->crearDeuda($otro, ['acreedor' => 'Ajena']);

        $respuesta = $this->actingAs($usuario)->get(route('deudas'));
        $respuesta->assertOk()->assertSee('Propia')->assertDontSee('Ajena');
    }

    public function test_edita_deuda_propia_y_bloquea_ajenas(): void
    {
        $usuario = $this->crearUsuario();
        $otro = $this->crearUsuario('otro@example.com');
        $deuda = $this->crearDeuda($usuario);
        $ajena = $this->crearDeuda($otro);

        $this->actingAs($usuario)->put(route('deudas.update', $deuda->id_deuda),
            $this->datosDeuda(['acreedor' => 'Editada']))
            ->assertRedirect(route('deudas'));
        $this->assertSame('Editada', $deuda->fresh()->acreedor);

        $this->actingAs($usuario)->put(route('deudas.update', $ajena->id_deuda), $this->datosDeuda())
            ->assertNotFound();
        $this->actingAs($usuario)->delete(route('deudas.destroy', $ajena->id_deuda))
            ->assertNotFound();
    }

    public function test_valida_montos_y_fechas(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario)->post(route('deudas.store'), $this->datosDeuda(['monto_inicial' => 0]))
            ->assertSessionHasErrors('monto_inicial');
        $this->actingAs($usuario)->post(route('deudas.store'), $this->datosDeuda(['monto_inicial' => -5]))
            ->assertSessionHasErrors('monto_inicial');
        $this->actingAs($usuario)->post(route('deudas.store'), $this->datosDeuda(['pago_minimo' => -1]))
            ->assertSessionHasErrors('pago_minimo');
        $this->actingAs($usuario)->post(route('deudas.store'), $this->datosDeuda(['fecha_vencimiento' => 'no-fecha']))
            ->assertSessionHasErrors('fecha_vencimiento');
        $this->assertSame(0, Deuda::count());
    }

    public function test_calcula_pagado_saldo_y_porcentaje(): void
    {
        $usuario = $this->crearUsuario();
        $deuda = $this->crearDeuda($usuario, ['monto_inicial' => 200]);
        $this->crearPago($deuda, 50);

        $html = $this->actingAs($usuario)->get(route('deudas'))->getContent();
        $this->assertStringContainsString('$50.00', $html);
        $this->assertStringContainsString('$150.00', $html);
        $this->assertStringContainsString('25%', $html);
    }

    public function test_no_permite_monto_inicial_menor_que_lo_ya_pagado(): void
    {
        $usuario = $this->crearUsuario();
        $deuda = $this->crearDeuda($usuario, ['monto_inicial' => 100]);
        $this->crearPago($deuda, 60);

        $this->actingAs($usuario)->put(route('deudas.update', $deuda->id_deuda),
            $this->datosDeuda(['monto_inicial' => '50.00']))
            ->assertSessionHasErrors('monto_inicial');
        $this->assertSame('100.00', (string) $deuda->fresh()->monto_inicial);
    }

    public function test_cancela_y_reactiva_una_deuda_con_saldo_positivo(): void
    {
        $usuario = $this->crearUsuario();
        $deuda = $this->crearDeuda($usuario);

        $this->actingAs($usuario)->put(route('deudas.update', $deuda->id_deuda),
            $this->datosDeuda(['estado' => 'cancelada']))
            ->assertRedirect(route('deudas'));
        $this->assertSame('cancelada', $deuda->fresh()->estado);

        $this->actingAs($usuario)->put(route('deudas.update', $deuda->id_deuda),
            $this->datosDeuda(['estado' => 'pendiente']))
            ->assertRedirect(route('deudas'));
        $this->assertSame('pendiente', $deuda->fresh()->estado);
    }

    public function test_no_permite_marcar_pagada_con_saldo_pendiente(): void
    {
        $usuario = $this->crearUsuario();
        $deuda = $this->crearDeuda($usuario);

        $this->actingAs($usuario)->put(route('deudas.update', $deuda->id_deuda),
            $this->datosDeuda(['estado' => 'pagada']))
            ->assertSessionHasErrors('estado');
    }

    public function test_elimina_deuda_sin_pagos_y_bloquea_con_pagos(): void
    {
        $usuario = $this->crearUsuario();
        $sinPagos = $this->crearDeuda($usuario, ['acreedor' => 'Sin pagos']);
        $conPagos = $this->crearDeuda($usuario, ['acreedor' => 'Con pagos']);
        $this->crearPago($conPagos, 10);

        $this->actingAs($usuario)->delete(route('deudas.destroy', $sinPagos->id_deuda))
            ->assertRedirect(route('deudas'));
        $this->assertFalse(Deuda::where('id_deuda', $sinPagos->id_deuda)->exists());

        $this->actingAs($usuario)->delete(route('deudas.destroy', $conPagos->id_deuda))
            ->assertRedirect(route('deudas'));
        $this->assertTrue(Deuda::where('id_deuda', $conPagos->id_deuda)->exists());
        $this->assertSame(1, PagoDeuda::count());
    }
}
