<?php

namespace Tests\Feature\Deudas;

use App\Models\AporteAhorro;
use App\Models\Deuda;
use App\Models\MetaAhorro;
use App\Models\Movimiento;
use App\Models\Presupuesto;
use Tests\Feature\ModulosTestCase;

class IntegracionPagoMovimientoTest extends ModulosTestCase
{
    public function test_los_aportes_no_afectan_movimientos_ni_presupuestos(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);
        $meta = MetaAhorro::create([
            'id_usuario' => $usuario->id_usuario, 'nombre' => 'Meta',
            'tipo_meta' => 'meta_ahorro', 'monto_objetivo' => 100,
            'fecha_objetivo' => '2026-12-31', 'estado' => 'activa',
        ]);
        Presupuesto::create([
            'id_usuario' => $usuario->id_usuario, 'id_categoria' => $categoria->id_categoria,
            'monto_limite' => 200, 'fecha_inicio' => '2026-03-01', 'fecha_fin' => '2026-03-31',
        ]);

        $this->actingAs($usuario)->post(route('aportes.store', $meta->id_meta), [
            'monto' => '50', 'fecha' => '2026-03-10',
        ])->assertRedirect(route('metas'));

        $this->assertSame(1, AporteAhorro::count());
        $this->assertSame(0, Movimiento::count());
        $html = $this->actingAs($usuario)->get(route('presupuestos'))->getContent();
        $this->assertStringContainsString('Gastado $0.00', $html);
    }

    public function test_un_pago_aparece_en_movimientos_y_afecta_al_presupuesto_correspondiente(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario, ['nombre' => 'Deudas personales']);
        $deuda = Deuda::create([
            'id_usuario' => $usuario->id_usuario, 'acreedor' => 'Acreedor',
            'monto_inicial' => 100, 'fecha_vencimiento' => '2026-12-31',
            'pago_minimo' => 0, 'estado' => 'pendiente',
        ]);
        Presupuesto::create([
            'id_usuario' => $usuario->id_usuario, 'id_categoria' => $categoria->id_categoria,
            'monto_limite' => 500, 'fecha_inicio' => '2026-03-01', 'fecha_fin' => '2026-03-31',
        ]);

        $this->actingAs($usuario)->post(route('pagos.store', $deuda->id_deuda), [
            'monto' => '75.00', 'fecha' => '2026-03-15',
            'id_categoria' => $categoria->id_categoria, 'descripcion' => 'Pago de prueba',
        ])->assertRedirect(route('deudas'));

        $htmlMovimientos = $this->actingAs($usuario)->get(route('movimientos'))->getContent();
        $this->assertStringContainsString('Pago de prueba', $htmlMovimientos);
        $this->assertSame(1, Movimiento::count());

        $htmlPresupuestos = $this->actingAs($usuario)->get(route('presupuestos'))->getContent();
        $this->assertStringContainsString('Gastado $75.00', $htmlPresupuestos);
        $this->assertStringContainsString('Disponible $425.00', $htmlPresupuestos);
    }

    public function test_los_usuarios_no_pueden_mezclar_informacion_financiera(): void
    {
        $usuarioA = $this->crearUsuario('a@example.com');
        $usuarioB = $this->crearUsuario('b@example.com');
        $categoriaA = $this->crearCategoria($usuarioA, ['nombre' => 'Cat A']);
        $categoriaB = $this->crearCategoria($usuarioB, ['nombre' => 'Cat B']);

        $metaA = MetaAhorro::create([
            'id_usuario' => $usuarioA->id_usuario, 'nombre' => 'Meta A',
            'tipo_meta' => 'meta_ahorro', 'monto_objetivo' => 100,
            'fecha_objetivo' => '2026-12-31', 'estado' => 'activa',
        ]);
        MetaAhorro::create([
            'id_usuario' => $usuarioB->id_usuario, 'nombre' => 'Meta B',
            'tipo_meta' => 'meta_ahorro', 'monto_objetivo' => 100,
            'fecha_objetivo' => '2026-12-31', 'estado' => 'activa',
        ]);
        AporteAhorro::create(['id_meta' => $metaA->id_meta, 'monto' => 20, 'fecha' => '2026-03-01']);

        $deudaA = Deuda::create([
            'id_usuario' => $usuarioA->id_usuario, 'acreedor' => 'Deuda A',
            'monto_inicial' => 100, 'fecha_vencimiento' => '2026-12-31',
            'pago_minimo' => 0, 'estado' => 'pendiente',
        ]);
        Deuda::create([
            'id_usuario' => $usuarioB->id_usuario, 'acreedor' => 'Deuda B',
            'monto_inicial' => 100, 'fecha_vencimiento' => '2026-12-31',
            'pago_minimo' => 0, 'estado' => 'pendiente',
        ]);

        $this->actingAs($usuarioA)->post(route('pagos.store', $deudaA->id_deuda), [
            'monto' => '10', 'fecha' => '2026-03-10',
            'id_categoria' => $categoriaA->id_categoria,
        ])->assertRedirect(route('deudas'));

        $htmlMetas = $this->actingAs($usuarioA)->get(route('metas'))->getContent();
        $this->assertStringContainsString('Meta A', $htmlMetas);
        $this->assertStringNotContainsString('Meta B', $htmlMetas);

        $htmlDeudas = $this->actingAs($usuarioA)->get(route('deudas'))->getContent();
        $this->assertStringContainsString('Deuda A', $htmlDeudas);
        $this->assertStringNotContainsString('Deuda B', $htmlDeudas);
        $this->assertStringNotContainsString('Cat B', $htmlDeudas);
    }
}
