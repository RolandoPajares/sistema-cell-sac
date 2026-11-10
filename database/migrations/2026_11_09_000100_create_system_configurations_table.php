<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('configuraciones_sistema')) {
            return;
        }

        Schema::create('configuraciones_sistema', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->restrictOnDelete();
            $table->foreignId('sede_id')->nullable()->constrained('sedes')->restrictOnDelete();
            $table->string('grupo', 60);
            $table->string('clave', 100);
            $table->json('valor_json');
            $table->unsignedBigInteger('actualizado_por_usuario_id')->nullable();
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->useCurrent();
            $table->unique(['empresa_id', 'sede_id', 'grupo', 'clave'], 'uq_config_sistema_scope');
            $table->index(['empresa_id', 'grupo']);
            $table->index(['sede_id', 'grupo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones_sistema');
    }
};
