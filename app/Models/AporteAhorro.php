<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AporteAhorro extends Model
{
    protected $table = 'aporte_ahorro';

    protected $primaryKey = 'id_aporte';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_meta',
        'monto',
        'fecha',
        'nota',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha' => 'date:Y-m-d',
        ];
    }

    public function metaAhorro()
    {
        return $this->belongsTo(MetaAhorro::class, 'id_meta', 'id_meta');
    }
}
