<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categoria';

    protected $primaryKey = 'id_categoria';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'nombre',
        'tipo_movimiento',
        'clasificacion_503020',
        'es_predeterminada',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'es_predeterminada' => 'boolean',
            'activa' => 'boolean',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'id_categoria', 'id_categoria');
    }

    public function presupuestos()
    {
        return $this->hasMany(Presupuesto::class, 'id_categoria', 'id_categoria');
    }
}
