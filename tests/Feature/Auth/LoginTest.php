<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(?float $ingreso = 1000, string $correo = 'login@example.com'): Usuario
    {
        return Usuario::create([
            'nombre' => 'Usuario de prueba',
            'correo' => $correo,
            'password_hash' => Hash::make('clave-segura-123'),
            'ingreso_fijo_mensual' => $ingreso,
        ]);
    }

    public function test_el_login_correcto_autentica_y_regenera_la_sesion(): void
    {
        $usuario = $this->crearUsuario();
        $idSesionAntes = $this->app['session']->getId();

        $respuesta = $this->post(route('login.post'), [
            'correo' => 'login@example.com',
            'password' => 'clave-segura-123',
        ]);

        $respuesta->assertRedirect(route('dashboard'));
        $this->assertTrue(Auth::check());
        $this->assertSame($usuario->id_usuario, Auth::id());
        $this->assertInstanceOf(Usuario::class, Auth::user());
        $this->assertNotSame($idSesionAntes, $this->app['session']->getId());
    }

    public function test_usuario_con_ingreso_pendiente_es_redirigido_a_configuracion_inicial(): void
    {
        $this->crearUsuario(null);

        $this->post(route('login.post'), [
            'correo' => 'login@example.com',
            'password' => 'clave-segura-123',
        ])->assertRedirect(route('ingreso.inicial'));
    }

    public function test_login_incorrecto_no_autentica_y_muestra_error_generico(): void
    {
        $this->crearUsuario();

        $respuesta = $this->post(route('login.post'), [
            'correo' => 'login@example.com',
            'password' => 'clave-equivocada',
        ]);

        $respuesta->assertSessionHasErrors('correo');
        $this->assertSame('Correo o contraseña incorrectos.', session('errors')->first('correo'));
        $this->assertGuest();
    }

    public function test_login_con_correo_inexistente_usa_el_mismo_mensaje_de_error(): void
    {
        $respuesta = $this->post(route('login.post'), [
            'correo' => 'no-existe@example.com',
            'password' => 'clave-segura-123',
        ]);

        $respuesta->assertSessionHasErrors('correo');
        $this->assertSame('Correo o contraseña incorrectos.', session('errors')->first('correo'));
        $this->assertGuest();
    }

    public function test_el_correo_ingresado_se_conserva_después_de_un_error(): void
    {
        $respuesta = $this->post(route('login.post'), [
            'correo' => 'retained@example.com',
            'password' => 'incorrecta',
        ]);

        $respuesta->assertSessionHasInput('correo', 'retained@example.com');
    }

    public function test_usuario_autenticado_no_vuelve_a_ver_login_ni_registro(): void
    {
        $this->crearUsuario();

        $this->post(route('login.post'), [
            'correo' => 'login@example.com',
            'password' => 'clave-segura-123',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('login'))->assertRedirect(route('dashboard'));
        $this->get(route('registro'))->assertRedirect(route('dashboard'));
    }
}
