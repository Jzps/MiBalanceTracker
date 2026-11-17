<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuario';

    protected $primaryKey = 'id_usuario';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'correo',
        'password_hash',
        'ingreso_fijo_mensual',
        'porcentaje_necesidades',
        'porcentaje_deseos',
        'porcentaje_ahorro',
    ];

    protected function casts(): array
    {
        return [
            'ingreso_fijo_mensual' => 'decimal:2',
            'porcentaje_necesidades' => 'decimal:2',
            'porcentaje_deseos' => 'decimal:2',
            'porcentaje_ahorro' => 'decimal:2',
        ];
    }

    public function categorias()
    {
        return $this->hasMany(Categoria::class, 'id_usuario', 'id_usuario');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'id_usuario', 'id_usuario');
    }

    public function presupuestos()
    {
        return $this->hasMany(Presupuesto::class, 'id_usuario', 'id_usuario');
    }

    public function metasAhorro()
    {
        return $this->hasMany(MetaAhorro::class, 'id_usuario', 'id_usuario');
    }

    public function deudas()
    {
        return $this->hasMany(Deuda::class, 'id_usuario', 'id_usuario');
    }
}
