<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deuda', function (Blueprint $table) {
            $table->integer('id_deuda', true, false);
            $table->integer('id_usuario', false, false);
            $table->string('acreedor', 150);
            $table->decimal('monto_inicial', 10, 2);
            $table->date('fecha_vencimiento');
            $table->decimal('pago_minimo', 10, 2)->default(0.00);
            $table->enum('estado', ['pendiente', 'pagada', 'cancelada'])->default('pendiente');

            $table->primary('id_deuda');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deuda');
    }
};
