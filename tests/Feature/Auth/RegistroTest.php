<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistroTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_registro_valido_crea_usuario_con_hash_y_redirige_a_configuracion_inicial(): void
    {
        $respuesta = $this->post(route('registro.post'), [
            'nombre' => 'Ana Pérez',
            'correo' => 'ana@example.com',
            'password' => 'clave-segura-123',
            'password_confirmation' => 'clave-segura-123',
        ]);

        $respuesta->assertRedirect(route('ingreso.inicial'));

        $usuario = Usuario::where('correo', 'ana@example.com')->firstOrFail();
        $this->assertSame('Ana Pérez', $usuario->nombre);
        $this->assertNotSame('clave-segura-123', $usuario->password_hash);
        $this->assertTrue(Hash::check('clave-segura-123', $usuario->password_hash));
        $this->assertNull($usuario->ingreso_fijo_mensual);
        $this->assertSame('50.00', (string) $usuario->porcentaje_necesidades);
        $this->assertSame('30.00', (string) $usuario->porcentaje_deseos);
        $this->assertSame('20.00', (string) $usuario->porcentaje_ahorro);

        $this->assertTrue(Auth::check());
        $this->assertSame($usuario->id_usuario, Auth::id());
    }

    public function test_no_se_permite_registrar_un_correo_duplicado(): void
    {
        Usuario::create([
            'nombre' => 'Existente',
            'correo' => 'duplicado@example.com',
            'password_hash' => Hash::make('clave-segura-123'),
        ]);

        $respuesta = $this->post(route('registro.post'), [
            'nombre' => 'Otro usuario',
            'correo' => 'duplicado@example.com',
            'password' => 'clave-segura-123',
            'password_confirmation' => 'clave-segura-123',
        ]);

        $respuesta->assertSessionHasErrors('correo');
        $this->assertSame(1, Usuario::where('correo', 'duplicado@example.com')->count());
    }

    public function test_los_campos_obligatorios_no_pueden_estar_vacios(): void
    {
        $respuesta = $this->post(route('registro.post'), []);

        $respuesta->assertSessionHasErrors(['nombre', 'correo', 'password']);
        $this->assertSame(0, Usuario::count());
    }

    public function test_la_confirmacion_debe_coincidir_con_la_contrasena(): void
    {
        $respuesta = $this->post(route('registro.post'), [
            'nombre' => 'Usuario',
            'correo' => 'confirmacion@example.com',
            'password' => 'clave-segura-123',
            'password_confirmation' => 'otra-clave-123',
        ]);

        $respuesta->assertSessionHasErrors('password');
        $this->assertSame(0, Usuario::count());
    }

    public function test_la_contrasena_debe_tener_al_menos_ocho_caracteres(): void
    {
        $respuesta = $this->post(route('registro.post'), [
            'nombre' => 'Usuario',
            'correo' => 'corta@example.com',
            'password' => 'corta1',
            'password_confirmation' => 'corta1',
        ]);

        $respuesta->assertSessionHasErrors('password');
    }

    public function test_las_vistas_publicas_de_login_y_registro_se_pueden_abrir(): void
    {
        $this->get('/')->assertOk()->assertSee('Iniciar sesión');
        $this->get(route('login'))->assertOk()->assertSee('Iniciar sesión');
        $this->get(route('registro'))->assertOk()->assertSee('Crear cuenta');
    }
}
