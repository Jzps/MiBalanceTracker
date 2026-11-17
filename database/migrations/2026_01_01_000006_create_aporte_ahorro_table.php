<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aporte_ahorro', function (Blueprint $table) {
            $table->integer('id_aporte', true, false);
            $table->integer('id_meta', false, false);
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->string('nota', 255)->nullable();

            $table->primary('id_aporte');
            $table->foreign('id_meta')->references('id_meta')->on('meta_ahorro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aporte_ahorro');
    }
};
