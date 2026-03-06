<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('remitente');
            $table->unsignedBigInteger('destinatario');
            $table->unsignedBigInteger('id_depaA');
            $table->unsignedBigInteger('id_depaB');
            $table->string('mensaje');
            $table->timestamp('fecha')->useCurrent();
            $table->timestamps();

            $table->foreign('remitente')->references('id')->on('personas')->onDelete('cascade');
            $table->foreign('destinatario')->references('id')->on('personas')->onDelete('cascade');
            $table->foreign('id_depaA')->references('id')->on('departamentos')->onDelete('cascade');
            $table->foreign('id_depaB')->references('id')->on('departamentos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};
