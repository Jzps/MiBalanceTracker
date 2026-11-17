<?php

namespace Tests\Feature\Presupuestos;

use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\Presupuesto;
use App\Models\Usuario;
use Tests\Feature\ModulosTestCase;

class PresupuestoTest extends ModulosTestCase
{
    private function datosPresupuesto(Categoria $categoria, array $extra = []): array
    {
        return array_merge([
            'id_categoria' => $categoria->id_categoria,
            'monto_limite' => '100.00',
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-03-31',
            'porcentaje_alerta' => '80',
        ], $extra);
    }

    private function crearPresupuesto(Usuario $usuario, Categoria $categoria, array $extra = []): Presupuesto
    {
        return Presupuesto::create([
            'id_usuario' => $usuario->id_usuario,
            'id_categoria' => $categoria->id_categoria,
            'monto_limite' => $extra['monto_limite'] ?? 100,
            'fecha_inicio' => $extra['fecha_inicio'] ?? '2026-03-01',
            'fecha_fin' => $extra['fecha_fin'] ?? '2026-03-31',
            'porcentaje_alerta' => $extra['porcentaje_alerta'] ?? 80,
        ]);
    }

    private function crearGasto(Usuario $usuario, Categoria $categoria, string $monto, string $fecha): Movimiento
    {
        return Movimiento::create([
            'id_usuario' => $usuario->id_usuario,
            'id_categoria' => $categoria->id_categoria,
            'monto' => $monto,
            'fecha' => $fecha,
            'tipo' => 'gasto',
            'descripcion' => 'Gasto de prueba',
        ]);
    }

    public function test_crea_un_presupuesto_valido(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);

        $this->actingAs($usuario)->post(route('presupuestos.store'), $this->datosPresupuesto($categoria))
            ->assertRedirect(route('presupuestos'));

        $presupuesto = Presupuesto::firstOrFail();
        $this->assertSame($usuario->id_usuario, $presupuesto->id_usuario);
        $this->assertSame('100.00', (string) $presupuesto->monto_limite);
        $this->assertSame('80.00', (string) $presupuesto->porcentaje_alerta);
    }

    public function test_el_listado_muestra_solo_presupuestos_propios(): void
    {
        $usuario = $this->crearUsuario();
        $otro = $this->crearUsuario('otro@example.com');
        $propia = $this->crearCategoria($usuario, ['nombre' => 'Propia']);
        $ajena = $this->crearCategoria($otro, ['nombre' => 'Ajena']);
        $this->crearPresupuesto($usuario, $propia);
        $this->crearPresupuesto($otro, $ajena);

        $respuesta = $this->actingAs($usuario)->get(route('presupuestos'));

        $respuesta->assertOk()->assertSee('Propia')->assertDontSee('Ajena');
    }

    public function test_edita_un_presupuesto_propio_y_bloquea_los_ajenos(): void
    {
        $usuario = $this->crearUsuario();
        $otro = $this->crearUsuario('otro@example.com');
        $categoria = $this->crearCategoria($usuario);
        $categoriaAjena = $this->crearCategoria($otro);
        $presupuesto = $this->crearPresupuesto($usuario, $categoria);
        $presupuestoAjeno = $this->crearPresupuesto($otro, $categoriaAjena);

        $this->actingAs($usuario)->put(route('presupuestos.update', $presupuesto->id_presupuesto),
            $this->datosPresupuesto($categoria, ['monto_limite' => '250.00']))
            ->assertRedirect(route('presupuestos'));
        $this->assertSame('250.00', (string) $presupuesto->fresh()->monto_limite);

        $this->actingAs($usuario)->put(route('presupuestos.update', $presupuestoAjeno->id_presupuesto),
            $this->datosPresupuesto($categoriaAjena))->assertNotFound();
        $this->actingAs($usuario)->delete(route('presupuestos.destroy', $presupuestoAjeno->id_presupuesto))
            ->assertNotFound();
    }

    public function test_elimina_presupuesto_sin_tocar_los_movimientos(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);
        $presupuesto = $this->crearPresupuesto($usuario, $categoria);
        $this->crearGasto($usuario, $categoria, '10', '2026-03-10');

        $this->actingAs($usuario)->delete(route('presupuestos.destroy', $presupuesto->id_presupuesto))
            ->assertRedirect(route('presupuestos'));

        $this->assertSame(0, Presupuesto::count());
        $this->assertSame(1, Movimiento::count());
    }

    public function test_rechaza_categorias_de_ingreso_inactivas_y_ajenas(): void
    {
        $usuario = $this->crearUsuario();
        $otro = $this->crearUsuario('otro@example.com');
        $ingreso = $this->crearCategoria($usuario, ['nombre' => 'Salario', 'tipo_movimiento' => 'ingreso']);
        $inactiva = $this->crearCategoria($usuario, ['nombre' => 'Inactiva', 'activa' => false]);
        $ajena = $this->crearCategoria($otro);

        $this->actingAs($usuario)->post(route('presupuestos.store'), $this->datosPresupuesto($ingreso))
            ->assertSessionHasErrors('id_categoria');
        $this->actingAs($usuario)->post(route('presupuestos.store'), $this->datosPresupuesto($inactiva))
            ->assertSessionHasErrors('id_categoria');
        $this->actingAs($usuario)->post(route('presupuestos.store'), $this->datosPresupuesto($ajena))
            ->assertSessionHasErrors('id_categoria');
        $this->assertSame(0, Presupuesto::count());
    }

    public function test_rechaza_montos_y_fechas_invalidos(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);

        $this->actingAs($usuario)->post(route('presupuestos.store'), $this->datosPresupuesto($categoria, ['monto_limite' => 0]))
            ->assertSessionHasErrors('monto_limite');
        $this->actingAs($usuario)->post(route('presupuestos.store'), $this->datosPresupuesto($categoria, ['monto_limite' => -10]))
            ->assertSessionHasErrors('monto_limite');
        $this->actingAs($usuario)->post(route('presupuestos.store'), $this->datosPresupuesto($categoria, [
            'fecha_inicio' => '2026-03-31', 'fecha_fin' => '2026-03-01',
        ]))->assertSessionHasErrors('fecha_fin');
        $this->actingAs($usuario)->post(route('presupuestos.store'), $this->datosPresupuesto($categoria, ['porcentaje_alerta' => 120]))
            ->assertSessionHasErrors('porcentaje_alerta');
    }

    public function test_calcula_gasto_disponible_y_porcentaje_con_movimientos_reales(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);
        $presupuesto = $this->crearPresupuesto($usuario, $categoria);

        $this->crearGasto($usuario, $categoria, '30', '2026-03-05');
        $this->crearGasto($usuario, $categoria, '20', '2026-03-31');
        $this->crearGasto($usuario, $categoria, '100', '2026-02-28');
        $this->crearGasto($usuario, $categoria, '100', '2026-04-01');
        Movimiento::create([
            'id_usuario' => $usuario->id_usuario,
            'id_categoria' => $categoria->id_categoria,
            'monto' => 500,
            'fecha' => '2026-03-10',
            'tipo' => 'ingreso',
            'descripcion' => 'Ingreso no incluido',
        ]);

        $html = $this->actingAs($usuario)->get(route('presupuestos'))->getContent();

        $this->assertStringContainsString('Gastado $50.00 de $100.00', $html);
        $this->assertStringContainsString('Disponible $50.00', $html);
        $this->assertStringContainsString('50% utilizado', $html);
        $this->assertStringNotContainsString('Gastado $150.00', $html);
        $this->assertTrue((bool) $presupuesto->exists);
    }

    public function test_alerta_preventiva_usa_el_porcentaje_configurado(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);
        $this->crearPresupuesto($usuario, $categoria, ['porcentaje_alerta' => 50]);
        $this->crearGasto($usuario, $categoria, '50', '2026-03-10');

        $html = $this->actingAs($usuario)->get(route('presupuestos'))->getContent();
        $this->assertStringContainsString('Has utilizado el 50% de tu presupuesto.', $html);
    }

    public function test_no_hay_alerta_antes_del_umbral(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);
        $this->crearPresupuesto($usuario, $categoria, ['porcentaje_alerta' => 80]);
        $this->crearGasto($usuario, $categoria, '79', '2026-03-10');

        $html = $this->actingAs($usuario)->get(route('presupuestos'))->getContent();
        $this->assertStringNotContainsString('Has utilizado el', $html);
        $this->assertStringNotContainsString('Has superado el presupuesto', $html);
    }

    public function test_excedente_tiene_prioridad_sobre_la_alerta_preventiva(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario, ['nombre' => 'Alimentación']);
        $this->crearPresupuesto($usuario, $categoria, ['porcentaje_alerta' => 50]);
        $this->crearGasto($usuario, $categoria, '120', '2026-03-10');

        $html = $this->actingAs($usuario)->get(route('presupuestos'))->getContent();
        $this->assertStringContainsString('Has superado el presupuesto de Alimentación.', $html);
        $this->assertStringNotContainsString('Has utilizado el 120% de tu presupuesto.', $html);
        $this->assertStringContainsString('Disponible $-20.00', $html);
    }

    public function test_el_progreso_se_recalcula_al_modificar_y_eliminar_un_movimiento(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);
        $this->crearPresupuesto($usuario, $categoria);
        $movimiento = $this->crearGasto($usuario, $categoria, '50', '2026-03-10');

        $html = $this->actingAs($usuario)->get(route('presupuestos'))->getContent();
        $this->assertStringContainsString('Gastado $50.00', $html);

        $this->actingAs($usuario)->put(route('movimientos.update', $movimiento->id_movimiento), [
            'tipo' => 'gasto',
            'id_categoria' => $categoria->id_categoria,
            'monto' => '80',
            'fecha' => '2026-03-10',
            'descripcion' => 'Gasto actualizado',
        ])->assertRedirect(route('movimientos'));

        $html = $this->actingAs($usuario)->get(route('presupuestos'))->getContent();
        $this->assertStringContainsString('Gastado $80.00', $html);

        $this->actingAs($usuario)->delete(route('movimientos.destroy', $movimiento->id_movimiento));
        $html = $this->actingAs($usuario)->get(route('presupuestos'))->getContent();
        $this->assertStringContainsString('Gastado $0.00', $html);
    }
}
