<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaAhorro extends Model
{
    protected $table = 'meta_ahorro';

    protected $primaryKey = 'id_meta';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'nombre',
        'tipo_meta',
        'monto_objetivo',
        'fecha_objetivo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'monto_objetivo' => 'decimal:2',
            'fecha_objetivo' => 'date:Y-m-d',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function aportes()
    {
        return $this->hasMany(AporteAhorro::class, 'id_meta', 'id_meta');
    }
}
