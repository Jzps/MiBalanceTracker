<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoDeuda extends Model
{
    protected $table = 'pago_deuda';

    protected $primaryKey = 'id_pago';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_deuda',
        'id_movimiento',
        'monto',
        'fecha',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha' => 'date:Y-m-d',
        ];
    }

    public function deuda()
    {
        return $this->belongsTo(Deuda::class, 'id_deuda', 'id_deuda');
    }

    public function movimiento()
    {
        return $this->belongsTo(Movimiento::class, 'id_movimiento', 'id_movimiento');
    }
}
