<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria', function (Blueprint $table) {
            $table->integer('id_categoria', true, false);
            $table->integer('id_usuario', false, false);
            $table->string('nombre', 80);
            $table->enum('tipo_movimiento', ['ingreso', 'gasto']);
            $table->enum('clasificacion_503020', ['necesidad', 'deseo', 'ahorro', 'no_aplica'])->default('no_aplica');
            $table->boolean('es_predeterminada')->default(false);
            $table->boolean('activa')->default(true);

            $table->primary('id_categoria');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria');
    }
};
