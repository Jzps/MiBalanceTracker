<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_ahorro', function (Blueprint $table) {
            $table->integer('id_meta', true, false);
            $table->integer('id_usuario', false, false);
            $table->string('nombre', 100);
            $table->enum('tipo_meta', ['meta_ahorro', 'fondo_emergencia'])->default('meta_ahorro');
            $table->decimal('monto_objetivo', 10, 2);
            $table->date('fecha_objetivo');
            $table->enum('estado', ['activa', 'completada', 'pausada', 'cancelada'])->default('activa');

            $table->primary('id_meta');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ahorro');
    }
};
