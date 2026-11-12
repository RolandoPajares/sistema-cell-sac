<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos_favoritos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('producto_id');
            $table->unsignedBigInteger('usuario_id');
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->unique(['producto_id', 'usuario_id']);
        });

        Schema::create('productos_resenas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('producto_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedTinyInteger('valoracion');
            $table->text('comentario')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->unique(['producto_id', 'usuario_id']);
            $table->index(['producto_id', 'valoracion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos_resenas');
        Schema::dropIfExists('productos_favoritos');
    }
};
