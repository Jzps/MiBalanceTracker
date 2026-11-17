<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_deuda', function (Blueprint $table) {
            $table->integer('id_pago', true, false);
            $table->integer('id_deuda', false, false);
            $table->integer('id_movimiento', false, false);
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->string('descripcion', 150)->nullable();

            $table->primary('id_pago');
            $table->unique('id_movimiento', 'uk_pago_deuda_movimiento');
            $table->foreign('id_deuda')->references('id_deuda')->on('deuda');
            $table->foreign('id_movimiento')->references('id_movimiento')->on('movimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_deuda');
    }
};
