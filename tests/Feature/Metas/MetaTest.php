<?php

namespace Tests\Feature\Metas;

use App\Models\AporteAhorro;
use App\Models\MetaAhorro;
use Tests\Feature\ModulosTestCase;

class MetaTest extends ModulosTestCase
{
    private function datosMeta(array $extra = []): array
    {
        return array_merge([
            'nombre' => 'Viaje',
            'tipo_meta' => 'meta_ahorro',
            'monto_objetivo' => '1000.00',
            'fecha_objetivo' => '2026-12-31',
        ], $extra);
    }

    private function crearMeta($usuario, array $extra = []): MetaAhorro
    {
        return MetaAhorro::create(array_merge([
            'id_usuario' => $usuario->id_usuario,
            'nombre' => 'Meta propia',
            'tipo_meta' => 'meta_ahorro',
            'monto_objetivo' => 100,
            'fecha_objetivo' => '2026-12-31',
            'estado' => 'activa',
        ], $extra));
    }

    public function test_crea_una_meta_y_un_fondo_de_emergencia(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario)->post(route('metas.store'), $this->datosMeta())
            ->assertRedirect(route('metas'));
        $this->actingAs($usuario)->post(route('metas.store'), $this->datosMeta([
            'nombre' => 'Fondo', 'tipo_meta' => 'fondo_emergencia',
        ]))->assertRedirect(route('metas'));

        $this->assertSame(2, MetaAhorro::count());
        $this->assertTrue(MetaAhorro::where('tipo_meta', 'fondo_emergencia')->exists());
        $this->assertSame('activa', MetaAhorro::first()->estado);
        $this->assertSame($usuario->id_usuario, MetaAhorro::first()->id_usuario);
    }

    public function test_solo_muestra_metas_propias(): void
    {
        $usuario = $this->crearUsuario();
        $otro = $this->crearUsuario('otro@example.com');
        $this->crearMeta($usuario, ['nombre' => 'Meta propia']);
        $this->crearMeta($otro, ['nombre' => 'Meta ajena']);

        $respuesta = $this->actingAs($usuario)->get(route('metas'));
        $respuesta->assertOk()->assertSee('Meta propia')->assertDontSee('Meta ajena');
    }

    public function test_edita_meta_propia_y_bloquea_metas_ajenas(): void
    {
        $usuario = $this->crearUsuario();
        $otro = $this->crearUsuario('otro@example.com');
        $meta = $this->crearMeta($usuario);
        $metaAjena = $this->crearMeta($otro);

        $this->actingAs($usuario)->put(route('metas.update', $meta->id_meta), $this->datosMeta(['nombre' => 'Editada']))
            ->assertRedirect(route('metas'));
        $this->assertSame('Editada', $meta->fresh()->nombre);

        $this->actingAs($usuario)->put(route('metas.update', $metaAjena->id_meta), $this->datosMeta())
            ->assertNotFound();
        $this->actingAs($usuario)->delete(route('metas.destroy', $metaAjena->id_meta))
            ->assertNotFound();
    }

    public function test_valida_enum_y_montos(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario)->post(route('metas.store'), $this->datosMeta(['tipo_meta' => 'invalido']))
            ->assertSessionHasErrors('tipo_meta');
        $this->actingAs($usuario)->post(route('metas.store'), $this->datosMeta(['monto_objetivo' => 0]))
            ->assertSessionHasErrors('monto_objetivo');
        $this->actingAs($usuario)->post(route('metas.store'), $this->datosMeta(['nombre' => '', 'fecha_objetivo' => 'no-fecha']))
            ->assertSessionHasErrors(['nombre', 'fecha_objetivo']);
        $this->assertSame(0, MetaAhorro::count());
    }

    public function test_no_permite_marcar_completada_manualmente(): void
    {
        $usuario = $this->crearUsuario();
        $meta = $this->crearMeta($usuario);

        $this->actingAs($usuario)->put(route('metas.update', $meta->id_meta),
            $this->datosMeta(['estado' => 'completada']))
            ->assertSessionHasErrors('estado');
        $this->assertSame('activa', $meta->fresh()->estado);
    }

    public function test_elimina_meta_sin_aportes_y_bloquea_con_aportes(): void
    {
        $usuario = $this->crearUsuario();
        $sinAportes = $this->crearMeta($usuario, ['nombre' => 'Sin aportes']);
        $conAportes = $this->crearMeta($usuario, ['nombre' => 'Con aportes']);
        AporteAhorro::create([
            'id_meta' => $conAportes->id_meta, 'monto' => 10, 'fecha' => '2026-03-10',
        ]);

        $this->actingAs($usuario)->delete(route('metas.destroy', $sinAportes->id_meta))
            ->assertRedirect(route('metas'));
        $this->assertFalse(MetaAhorro::where('id_meta', $sinAportes->id_meta)->exists());

        $this->actingAs($usuario)->delete(route('metas.destroy', $conAportes->id_meta))
            ->assertRedirect(route('metas'));
        $this->assertTrue(MetaAhorro::where('id_meta', $conAportes->id_meta)->exists());
        $this->assertSame(1, AporteAhorro::count());
    }

    public function test_cancelar_una_meta_con_aportes_conserva_el_historial(): void
    {
        $usuario = $this->crearUsuario();
        $meta = $this->crearMeta($usuario);
        AporteAhorro::create(['id_meta' => $meta->id_meta, 'monto' => 30, 'fecha' => '2026-03-10']);

        $this->actingAs($usuario)->put(route('metas.update', $meta->id_meta),
            $this->datosMeta(['estado' => 'cancelada']))
            ->assertRedirect(route('metas'));

        $this->assertSame('cancelada', $meta->fresh()->estado);
        $this->assertSame(1, AporteAhorro::count());
    }
}
