<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfil_campos', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 80)->unique();
            $table->string('etiqueta', 120);
            $table->string('tipo', 30)->default('text');
            $table->json('opciones')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->useCurrent();
            $table->index(['activo', 'orden']);
        });

        Schema::create('roles_perfil_campos', function (Blueprint $table): void {
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('perfil_campo_id')->constrained('perfil_campos')->cascadeOnDelete();
            $table->boolean('visible')->default(true);
            $table->boolean('editable')->default(false);
            $table->boolean('requerido')->default(false);
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->useCurrent();
            $table->primary(['rol_id', 'perfil_campo_id']);
            $table->index(['perfil_campo_id', 'visible']);
        });

        Schema::create('usuarios_perfil_campos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('perfil_campo_id')->constrained('perfil_campos')->cascadeOnDelete();
            $table->text('valor')->nullable();
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->useCurrent();
            $table->unique(['usuario_id', 'perfil_campo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios_perfil_campos');
        Schema::dropIfExists('roles_perfil_campos');
        Schema::dropIfExists('perfil_campos');
    }
};
