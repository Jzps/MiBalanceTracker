<?php

namespace Tests\Feature\Categorias;

use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\Presupuesto;
use Tests\Feature\ModulosTestCase;

class CategoriaTest extends ModulosTestCase
{
    private function datosCategoria(array $extra = []): array
    {
        return array_merge([
            'nombre' => 'Ocio',
            'tipo_movimiento' => 'gasto',
            'clasificacion_503020' => 'deseo',
        ], $extra);
    }

    public function test_crea_una_categoria_para_el_usuario_autenticado(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario)->post(route('categorias.store'), $this->datosCategoria())
            ->assertRedirect(route('movimientos'));

        $categoria = Categoria::firstOrFail();
        $this->assertSame($usuario->id_usuario, $categoria->id_usuario);
        $this->assertTrue((bool) $categoria->activa);
        $this->assertFalse((bool) $categoria->es_predeterminada);
    }

    public function test_el_listado_muestra_categorias_propias_y_no_las_ajenas(): void
    {
        $usuario = $this->crearUsuario();
        $otro = $this->crearUsuario('otro@example.com');
        $this->crearCategoria($usuario, ['nombre' => 'Propia']);
        $this->crearCategoria($otro, ['nombre' => 'Ajena']);

        $respuesta = $this->actingAs($usuario)->get(route('movimientos'));

        $respuesta->assertOk()->assertSee('Propia')->assertDontSee('Ajena');
    }

    public function test_valida_los_campos_y_los_enum(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario)->post(route('categorias.store'), ['nombre' => ''])
            ->assertSessionHasErrors(['nombre', 'tipo_movimiento', 'clasificacion_503020']);
        $this->actingAs($usuario)->post(route('categorias.store'), $this->datosCategoria(['tipo_movimiento' => 'invalido']))
            ->assertSessionHasErrors('tipo_movimiento');
        $this->actingAs($usuario)->post(route('categorias.store'), $this->datosCategoria(['clasificacion_503020' => 'invalida']))
            ->assertSessionHasErrors('clasificacion_503020');
        $this->assertSame(0, Categoria::count());
    }

    public function test_edita_una_categoria_propia(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario, ['nombre' => 'Viejo nombre']);

        $this->actingAs($usuario)->put(route('categorias.update', $categoria->id_categoria),
            $this->datosCategoria(['nombre' => 'Nuevo nombre']))
            ->assertRedirect(route('movimientos'));

        $this->assertSame('Nuevo nombre', $categoria->fresh()->nombre);
    }

    public function test_no_puede_editar_categorias_ajenas(): void
    {
        $usuario = $this->crearUsuario();
        $otro = $this->crearUsuario('otro@example.com');
        $categoriaAjena = $this->crearCategoria($otro);

        $this->actingAs($usuario)->put(route('categorias.update', $categoriaAjena->id_categoria),
            $this->datosCategoria())->assertNotFound();
        $this->actingAs($usuario)->delete(route('categorias.destroy', $categoriaAjena->id_categoria))
            ->assertNotFound();
    }

    public function test_desactivar_es_logico_y_la_categoria_sigue_visible_en_historial(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario, ['nombre' => 'Categoria historica']);
        Movimiento::create([
            'id_usuario' => $usuario->id_usuario,
            'id_categoria' => $categoria->id_categoria,
            'monto' => 10,
            'fecha' => '2026-03-10',
            'tipo' => 'gasto',
            'descripcion' => 'Gasto historico',
        ]);

        $this->actingAs($usuario)->delete(route('categorias.destroy', $categoria->id_categoria))
            ->assertRedirect(route('movimientos'));

        $this->assertSame(1, Categoria::count());
        $this->assertFalse((bool) $categoria->fresh()->activa);
        $this->assertSame(1, Movimiento::count());

        $html = $this->actingAs($usuario)->get(route('movimientos'))->getContent();
        $this->assertStringContainsString('Categoria historica', $html);
        $this->assertStringContainsString('Inactiva', $html);
        $this->assertDoesNotMatchRegularExpression(
            '/<option value="'.$categoria->id_categoria.'"\s+data-tipo=/',
            $html
        );
    }

    public function test_una_categoria_inactiva_puede_volver_a_activarse(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario, ['activa' => false]);

        $this->actingAs($usuario)->put(route('categorias.update', $categoria->id_categoria),
            $this->datosCategoria(['activa' => '1']))
            ->assertRedirect(route('movimientos'));

        $this->assertTrue((bool) $categoria->fresh()->activa);
    }

    public function test_no_cambia_el_tipo_cuando_hay_movimientos_incompatibles(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);
        Movimiento::create([
            'id_usuario' => $usuario->id_usuario,
            'id_categoria' => $categoria->id_categoria,
            'monto' => 10,
            'fecha' => '2026-03-10',
            'tipo' => 'gasto',
            'descripcion' => 'Gasto existente',
        ]);

        $this->actingAs($usuario)->put(route('categorias.update', $categoria->id_categoria),
            $this->datosCategoria(['tipo_movimiento' => 'ingreso']))
            ->assertSessionHasErrors('tipo_movimiento');

        $this->assertSame('gasto', $categoria->fresh()->tipo_movimiento);
    }

    public function test_no_cambia_a_ingreso_cuando_la_categoria_tiene_presupuesto(): void
    {
        $usuario = $this->crearUsuario();
        $categoria = $this->crearCategoria($usuario);
        Presupuesto::create([
            'id_usuario' => $usuario->id_usuario,
            'id_categoria' => $categoria->id_categoria,
            'monto_limite' => 100,
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-03-31',
        ]);

        $this->actingAs($usuario)->put(route('categorias.update', $categoria->id_categoria),
            $this->datosCategoria(['tipo_movimiento' => 'ingreso']))
            ->assertSessionHasErrors('tipo_movimiento');
    }
}
