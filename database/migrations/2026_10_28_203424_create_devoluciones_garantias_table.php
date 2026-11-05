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
        if (Schema::hasTable('devoluciones_garantias')) {
            $requiredColumns = [
                'id', 'codigo', 'pedido_id', 'cliente_id', 'producto_id', 'tipo', 'motivo',
                'estado', 'fecha_solicitud', 'fecha_limite', 'resolucion', 'creado_por', 'activo', 'creado_en',
            ];
            $missingColumns = array_values(array_filter(
                $requiredColumns,
                static fn (string $column): bool => ! Schema::hasColumn('devoluciones_garantias', $column),
            ));

            if ($missingColumns !== []) {
                throw new RuntimeException('The existing devoluciones_garantias table needs a reviewed, additive reconciliation before migrations continue. Missing columns: '.implode(', ', $missingColumns));
            }

            return;
        }

        Schema::create('devoluciones_garantias', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('codigo', 40)->unique();
            // Legacy commerce IDs are INT UNSIGNED; the FK columns must match exactly on MariaDB.
            $table->unsignedInteger('pedido_id');
            $table->unsignedInteger('cliente_id');
            $table->unsignedInteger('producto_id')->nullable();
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
        throw new LogicException('This business table may predate the migration and contains retained records. Use a reviewed recovery plan instead of dropping it.');
    }
};
