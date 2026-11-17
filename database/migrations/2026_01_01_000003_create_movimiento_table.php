<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimiento', function (Blueprint $table) {
            $table->integer('id_movimiento', true, false);
            $table->integer('id_usuario', false, false);
            $table->integer('id_categoria', false, false);
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->enum('tipo', ['ingreso', 'gasto']);
            $table->string('descripcion', 150);
            $table->string('metodo_pago', 50)->nullable();
            $table->text('notas')->nullable();

            $table->primary('id_movimiento');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuario');
            $table->foreign('id_categoria')->references('id_categoria')->on('categoria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento');
    }
};
