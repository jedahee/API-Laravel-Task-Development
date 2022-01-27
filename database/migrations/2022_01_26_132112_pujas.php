<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Pujas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pujas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('prod_id');
            $table->unsignedInteger('usuario_id');
            $table->decimal('dineroPujado');
            $table->timestamps();

            // Relaciones
            $table->foreign('prod_id')->references('id')->on('productos');
            $table->foreign('usuario_id')->references('id')->on('usuarios');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pujas');
    }
}
