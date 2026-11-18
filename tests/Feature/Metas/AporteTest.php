<?php

namespace Tests\Feature\Metas;

use App\Models\AporteAhorro;
use App\Models\MetaAhorro;
use App\Models\Movimiento;
use App\Models\Presupuesto;
use Tests\Feature\ModulosTestCase;

class AporteTest extends ModulosTestCase
{
    private function crearMeta($usuario, array $extra = []): MetaAhorro
    {
        return MetaAhorro::create(array_merge([
            'id_usuario' => $usuario->id_usuario,
            'nombre' => 'Viaje',
            'tipo_meta' => 'meta_ahorro',
            'monto_objetivo' => 100,
            'fecha_objetivo' => '2026-12-31',
            'estado' => 'activa',
        ], $extra));
    }

    public function test_registra_aporte_y_no_crea_movimientos(): void
    {
        $usuario = $this->crearUsuario();
        $meta = $this->crearMeta($usuario);

        $this->actingAs($usuario)->post(route('aportes.store', $meta->id_meta), [
            'monto' => '25.50', 'fecha' => '2026-03-10', 'nota' => 'Primer aporte',
        ])->assertRedirect(route('metas'));

        $aporte = AporteAhorro::firstOrFail();
        $this->assertSame($meta->id_meta, $aporte->id_meta);
        $this->assertSame('25.50', (string) $aporte->monto);
        $this->assertSame('Primer aporte', $aporte->nota);
        $this->assertSame(0, Movimiento::count());
        $this->assertSame(0, Presupuesto::count());
    }

    public function test_calcula_acumulado_pendiente_y_porcentaje(): void
    {
        $usuario = $this->crearUsuario();
        $meta = $this->crearMeta($usuario, ['monto_objetivo' => 200]);
        AporteAhorro::create(['id_meta' => $meta->id_meta, 'monto' => 50, 'fecha' => '2026-03-01']);
        AporteAhorro::create(['id_meta' => $meta->id_meta, 'monto' => 25, 'fecha' => '2026-03-05']);

        $html = $this->actingAs($usuario)->get(route('metas'))->getContent();
        $this->assertStringContainsString('Acumulado $75.00', $html);
        $this->assertStringContainsString('Pendiente $125.00', $html);
        $this->assertStringContainsString('37.5%', $html);
    }

    public function test_completa_la_meta_cuando_los_aportes_alcanzan_el_objetivo(): void
    {
        $usuario = $this->crearUsuario();
        $meta = $this->crearMeta($usuario, ['monto_objetivo' => 100]);

        $this->actingAs($usuario)->post(route('aportes.store', $meta->id_meta), [
            'monto' => '60', 'fecha' => '2026-03-01',
        ]);
        $this->assertSame('activa', $meta->fresh()->estado);

        $this->actingAs($usuario)->post(route('aportes.store', $meta->id_meta), [
            'monto' => '40', 'fecha' => '2026-03-02',
        ]);
        $this->assertSame('completada', $meta->fresh()->estado);

        $html = $this->actingAs($usuario)->get(route('metas'))->getContent();
        $this->assertStringContainsString('Pendiente $0.00', $html);
        $this->assertStringContainsString('100%', $html);
    }

    public function test_la_barra_no_supera_el_cien_por_ciento_pero_el_porcentaje_real_se_conserva(): void
    {
        $usuario = $this->crearUsuario();
        $meta = $this->crearMeta($usuario, ['monto_objetivo' => 100]);
        AporteAhorro::create(['id_meta' => $meta->id_meta, 'monto' => 120, 'fecha' => '2026-03-01']);

        $html = $this->actingAs($usuario)->get(route('metas'))->getContent();
        $this->assertStringContainsString('--valor: 100%', $html);
        $this->assertStringContainsString('120%', $html);
        $this->assertStringContainsString('Acumulado $120.00', $html);
    }

    public function test_rechaza_aporte_de_meta_ajena(): void
    {
        $usuario = $this->crearUsuario();
        $otro = $this->crearUsuario('otro@example.com');
        $metaAjena = $this->crearMeta($otro);

        $this->actingAs($usuario)->post(route('aportes.store', $metaAjena->id_meta), [
            'monto' => 10, 'fecha' => '2026-03-10',
        ])->assertNotFound();
        $this->assertSame(0, AporteAhorro::count());
    }

    public function test_rechaza_aportes_en_metas_que_no_estan_activas(): void
    {
        $usuario = $this->crearUsuario();
        foreach (['pausada', 'cancelada', 'completada'] as $estado) {
            $meta = $this->crearMeta($usuario, ['estado' => $estado]);
            $this->actingAs($usuario)->post(route('aportes.store', $meta->id_meta), [
                'monto' => 10, 'fecha' => '2026-03-10',
            ])->assertRedirect(route('metas'));
        }
        $this->assertSame(0, AporteAhorro::count());
    }

    public function test_valida_monto_fecha_y_nota(): void
    {
        $usuario = $this->crearUsuario();
        $meta = $this->crearMeta($usuario);

        $this->actingAs($usuario)->post(route('aportes.store', $meta->id_meta), [
            'monto' => 0, 'fecha' => '2026-03-10',
        ])->assertSessionHasErrors('monto');
        $this->actingAs($usuario)->post(route('aportes.store', $meta->id_meta), [
            'monto' => -5, 'fecha' => '2026-03-10',
        ])->assertSessionHasErrors('monto');
        $this->actingAs($usuario)->post(route('aportes.store', $meta->id_meta), [
            'monto' => 10, 'fecha' => 'no-fecha',
        ])->assertSessionHasErrors('fecha');
        $this->actingAs($usuario)->post(route('aportes.store', $meta->id_meta), [
            'monto' => 10, 'fecha' => '2026-03-10', 'nota' => str_repeat('a', 256),
        ])->assertSessionHasErrors('nota');
    }

    public function test_si_falla_la_insercion_del_aporte_la_transaccion_se_revierte(): void
    {
        $usuario = $this->crearUsuario();
        $meta = $this->crearMeta($usuario);
        $this->actingAs($usuario);
        $this->withoutExceptionHandling();

        AporteAhorro::created(function () {
            throw new \RuntimeException('Fallo simulado en el aporte');
        });

        try {
            $this->post(route('aportes.store', $meta->id_meta), [
                'monto' => 10, 'fecha' => '2026-03-10',
            ]);
        } catch (\RuntimeException $exception) {
            $this->assertSame('Fallo simulado en el aporte', $exception->getMessage());
        } finally {
            AporteAhorro::flushEventListeners();
        }

        $this->assertSame(0, AporteAhorro::count());
        $this->assertSame('activa', $meta->fresh()->estado);
    }
}
