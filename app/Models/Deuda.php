<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deuda extends Model
{
    protected $table = 'deuda';

    protected $primaryKey = 'id_deuda';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'acreedor',
        'monto_inicial',
        'fecha_vencimiento',
        'pago_minimo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'monto_inicial' => 'decimal:2',
            'fecha_vencimiento' => 'date:Y-m-d',
            'pago_minimo' => 'decimal:2',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function pagos()
    {
        return $this->hasMany(PagoDeuda::class, 'id_deuda', 'id_deuda');
    }
}
