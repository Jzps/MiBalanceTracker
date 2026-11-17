<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

abstract class ModulosTestCase extends TestCase
{
    use RefreshDatabase;

    protected function crearUsuario(string $correo = 'usuario@example.com', float $ingreso = 1000): Usuario
    {
        return Usuario::create([
            'nombre' => 'Usuario de prueba',
            'correo' => $correo,
            'password_hash' => Hash::make('clave-segura-123'),
            'ingreso_fijo_mensual' => $ingreso,
        ]);
    }

    protected function crearCategoria(Usuario $usuario, array $datos = []): Categoria
    {
        return Categoria::create(array_merge([
            'id_usuario' => $usuario->id_usuario,
            'nombre' => 'Alimentación',
            'tipo_movimiento' => 'gasto',
            'clasificacion_503020' => 'necesidad',
            'es_predeterminada' => false,
            'activa' => true,
        ], $datos));
    }
}
