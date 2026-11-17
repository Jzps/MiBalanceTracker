<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->integer('id_usuario', true, false);
            $table->string('nombre', 100);
            $table->string('correo', 100);
            $table->string('password_hash', 255);
            $table->decimal('ingreso_fijo_mensual', 10, 2)->nullable();
            $table->decimal('porcentaje_necesidades', 5, 2)->default(50.00);
            $table->decimal('porcentaje_deseos', 5, 2)->default(30.00);
            $table->decimal('porcentaje_ahorro', 5, 2)->default(20.00);

            $table->primary('id_usuario');
            $table->unique('correo', 'uk_usuario_correo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
