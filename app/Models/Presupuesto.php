<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    protected $table = 'presupuesto';

    protected $primaryKey = 'id_presupuesto';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_categoria',
        'monto_limite',
        'fecha_inicio',
        'fecha_fin',
        'porcentaje_alerta',
    ];

    protected function casts(): array
    {
        return [
            'monto_limite' => 'decimal:2',
            'fecha_inicio' => 'date:Y-m-d',
            'fecha_fin' => 'date:Y-m-d',
            'porcentaje_alerta' => 'decimal:2',
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
}
