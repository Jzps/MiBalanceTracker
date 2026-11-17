<?php

namespace Tests\Feature\Presupuestos;

use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\Presupuesto;
use Tests\Feature\ModulosTestCase;

class IntegracionUsuariosTest extends ModulosTestCase
{
    public function test_los_datos_y_calculos_de_un_usuario_nunca_incluyen_a_otro(): void
    {
        $usuarioA = $this->crearUsuario('a@example.com');
        $usuarioB = $this->crearUsuario('b@example.com');

        $categoriaA = $this->crearCategoria($usuarioA, ['nombre' => 'Categoria A']);
        $categoriaB = $this->crearCategoria($usuarioB, ['nombre' => 'Categoria B']);

        Movimiento::create([
            'id_usuario' => $usuarioA->id_usuario, 'id_categoria' => $categoriaA->id_categoria,
            'monto' => 40, 'fecha' => '2026-03-10', 'tipo' => 'gasto', 'descripcion' => 'Gasto A',
        ]);
        Movimiento::create([
            'id_usuario' => $usuarioB->id_usuario, 'id_categoria' => $categoriaB->id_categoria,
            'monto' => 300, 'fecha' => '2026-03-10', 'tipo' => 'gasto', 'descripcion' => 'Gasto B',
        ]);

        Presupuesto::create([
            'id_usuario' => $usuarioA->id_usuario, 'id_categoria' => $categoriaA->id_categoria,
            'monto_limite' => 100, 'fecha_inicio' => '2026-03-01', 'fecha_fin' => '2026-03-31',
        ]);
        Presupuesto::create([
            'id_usuario' => $usuarioB->id_usuario, 'id_categoria' => $categoriaB->id_categoria,
            'monto_limite' => 100, 'fecha_inicio' => '2026-03-01', 'fecha_fin' => '2026-03-31',
        ]);

        $htmlA = $this->actingAs($usuarioA)->get(route('presupuestos'))->getContent();
        $this->assertStringContainsString('Categoria A', $htmlA);
        $this->assertStringNotContainsString('Categoria B', $htmlA);
        $this->assertStringContainsString('Gastado $40.00', $htmlA);
        $this->assertStringNotContainsString('Gastado $300.00', $htmlA);

        $htmlMovimientosA = $this->actingAs($usuarioA)->get(route('movimientos'))->getContent();
        $this->assertStringContainsString('Gasto A', $htmlMovimientosA);
        $this->assertStringNotContainsString('Gasto B', $htmlMovimientosA);
    }
}
