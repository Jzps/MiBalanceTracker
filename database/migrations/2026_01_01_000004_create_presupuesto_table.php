<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuesto', function (Blueprint $table) {
            $table->integer('id_presupuesto', true, false);
            $table->integer('id_usuario', false, false);
            $table->integer('id_categoria', false, false);
            $table->decimal('monto_limite', 10, 2);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('porcentaje_alerta', 5, 2)->default(80.00);

            $table->primary('id_presupuesto');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuario');
            $table->foreign('id_categoria')->references('id_categoria')->on('categoria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuesto');
    }
};
