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
        Schema::create('devoluciones_garantias', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('codigo', 40)->unique();
            $table->integer('pedido_id');
            $table->integer('cliente_id');
            $table->integer('producto_id')->nullable();
            $table->string('tipo', 30)->index();
            $table->text('motivo');
            $table->string('estado', 30)->default('Recibida')->index();
            $table->date('fecha_solicitud')->index();
            $table->date('fecha_limite')->nullable()->index();
            $table->text('resolucion')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('activo')->default(true)->index();
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('pedido_id')->references('id')->on('pedidos')->restrictOnDelete();
            $table->foreign('cliente_id')->references('id')->on('clientes')->restrictOnDelete();
            $table->foreign('producto_id')->references('id')->on('productos')->nullOnDelete();
            $table->index(['estado', 'fecha_solicitud']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devoluciones_garantias');
    }
};
