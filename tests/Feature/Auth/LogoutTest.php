<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Usuario logout',
            'correo' => 'logout@example.com',
            'password_hash' => Hash::make('clave-segura-123'),
            'ingreso_fijo_mensual' => 1000,
        ]);

        $this->actingAs($usuario);
    }

    public function test_el_logout_por_post_cierra_la_sesion_y_redirige_al_login(): void
    {
        $this->autenticar();
        $tokenAntes = $this->app['session']->token();

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNotSame($tokenAntes, $this->app['session']->token());
    }

    public function test_despues_del_logout_las_rutas_privadas_bloquean_el_acceso(): void
    {
        $this->autenticar();
        $this->post(route('logout'));

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('movimientos'))->assertRedirect(route('login'));
    }

    public function test_no_se_puede_cerrar_sesion_con_get(): void
    {
        $this->autenticar();

        $this->get('/logout')->assertStatus(405);
        $this->assertAuthenticated();
    }
}
