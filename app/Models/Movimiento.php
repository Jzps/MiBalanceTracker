<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $table = 'movimiento';

    protected $primaryKey = 'id_movimiento';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_categoria',
        'monto',
        'fecha',
        'tipo',
        'descripcion',
        'metodo_pago',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha' => 'date:Y-m-d',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function pagoDeuda()
    {
        return $this->hasOne(PagoDeuda::class, 'id_movimiento', 'id_movimiento');
    }
}
