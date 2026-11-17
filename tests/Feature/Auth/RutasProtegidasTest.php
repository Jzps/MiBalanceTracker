<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RutasProtegidasTest extends TestCase
{
    use RefreshDatabase;

    private array $rutasPrivadas = [
        'dashboard',
        'movimientos',
        'presupuestos',
        'metas',
        'deudas',
        'analisis',
        'educacion',
        'distribucion',
        'configuracion',
    ];

    public function test_un_visitante_es_redirigido_al_login_desde_todas_las_rutas_privadas(): void
    {
        foreach ($this->rutasPrivadas as $ruta) {
            $this->get(route($ruta))->assertRedirect(route('login'));
        }
    }

    public function test_usuario_autenticado_con_ingreso_configurado_puede_abrir_todas_las_rutas(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Usuario con ingreso',
            'correo' => 'con-ingreso@example.com',
            'password_hash' => Hash::make('clave-segura-123'),
            'ingreso_fijo_mensual' => 1500,
        ]);

        $this->actingAs($usuario);

        foreach ($this->rutasPrivadas as $ruta) {
            $this->get(route($ruta))->assertOk();
        }
    }

    public function test_usuario_autenticado_sin_ingreso_no_puede_abrir_las_rutas_privadas(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Usuario sin ingreso',
            'correo' => 'sin-ingreso@example.com',
            'password_hash' => Hash::make('clave-segura-123'),
        ]);

        $this->actingAs($usuario);

        foreach ($this->rutasPrivadas as $ruta) {
            $this->get(route($ruta))->assertRedirect(route('ingreso.inicial'));
        }
    }
}
