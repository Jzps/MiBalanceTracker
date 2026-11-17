<?php

namespace Tests\Feature\Auth;

use App\Models\Movimiento;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class IngresoInicialTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(?float $ingreso = null): Usuario
    {
        return Usuario::create([
            'nombre' => 'Usuario ingreso inicial',
            'correo' => 'ingreso@example.com',
            'password_hash' => Hash::make('clave-segura-123'),
            'ingreso_fijo_mensual' => $ingreso,
        ]);
    }

    public function test_usuario_nuevo_tiene_ingreso_fijo_nulo_y_ve_la_configuracion_inicial(): void
    {
        $usuario = $this->crearUsuario();
        $this->actingAs($usuario);

        $this->assertNull($usuario->ingreso_fijo_mensual);
        $this->get(route('ingreso.inicial'))->assertOk()->assertSee('Configuración inicial');
    }

    public function test_no_se_puede_saltar_la_configuracion_inicial(): void
    {
        $this->actingAs($this->crearUsuario());

        $this->get(route('dashboard'))->assertRedirect(route('ingreso.inicial'));
        $this->get(route('movimientos'))->assertRedirect(route('ingreso.inicial'));
    }

    public function test_el_valor_vacio_es_rechazado(): void
    {
        $this->actingAs($this->crearUsuario());

        $this->post(route('ingreso.inicial.guardar'), ['ingreso_fijo_mensual' => ''])
            ->assertSessionHasErrors('ingreso_fijo_mensual');
    }

    public function test_el_valor_cero_es_rechazado(): void
    {
        $this->actingAs($this->crearUsuario());

        $this->post(route('ingreso.inicial.guardar'), ['ingreso_fijo_mensual' => 0])
            ->assertSessionHasErrors('ingreso_fijo_mensual');
    }

    public function test_el_valor_negativo_es_rechazado(): void
    {
        $this->actingAs($this->crearUsuario());

        $this->post(route('ingreso.inicial.guardar'), ['ingreso_fijo_mensual' => -5])
            ->assertSessionHasErrors('ingreso_fijo_mensual');
    }

    public function test_un_valor_positivo_se_guarda_y_permite_acceder_al_dashboard(): void
    {
        $usuario = $this->crearUsuario();
        $this->actingAs($usuario);

        $this->post(route('ingreso.inicial.guardar'), ['ingreso_fijo_mensual' => '1250.50'])
            ->assertRedirect(route('dashboard'));

        $this->assertSame('1250.50', (string) $usuario->fresh()->ingreso_fijo_mensual);
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('ingreso.inicial'))->assertRedirect(route('dashboard'));
    }

    public function test_el_ingreso_fijo_no_crea_movimientos_ni_modifica_los_porcentajes(): void
    {
        $usuario = $this->crearUsuario();
        $this->actingAs($usuario);

        $this->post(route('ingreso.inicial.guardar'), ['ingreso_fijo_mensual' => '1000'])
            ->assertRedirect(route('dashboard'));

        $this->assertSame(0, Movimiento::count());
        $this->assertSame('50.00', (string) $usuario->fresh()->porcentaje_necesidades);
        $this->assertSame('30.00', (string) $usuario->fresh()->porcentaje_deseos);
        $this->assertSame('20.00', (string) $usuario->fresh()->porcentaje_ahorro);
    }
}
