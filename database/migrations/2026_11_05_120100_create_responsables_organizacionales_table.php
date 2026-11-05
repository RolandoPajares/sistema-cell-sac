<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addChecks = static function (): void {
            if (DB::getDriverName() !== 'mysql') {
                return;
            }

            $checks = [
                'ck_responsables_recurso' => "(tipo_recurso = 'sede' AND almacen_id IS NULL) OR (tipo_recurso = 'almacen' AND almacen_id IS NOT NULL)",
                'ck_responsables_vigencia' => 'vigente_hasta IS NULL OR vigente_hasta >= vigente_desde',
            ];

            foreach ($checks as $name => $expression) {
                $exists = DB::table('information_schema.table_constraints')
                    ->where('constraint_schema', DB::raw('DATABASE()'))
                    ->where('table_name', 'responsables_organizacionales')
                    ->where('constraint_name', $name)
                    ->exists();

                if (! $exists) {
                    DB::statement("ALTER TABLE responsables_organizacionales ADD CONSTRAINT {$name} CHECK ({$expression})");
                }
            }
        };

        if (Schema::hasTable('responsables_organizacionales')) {
            $addChecks();
            return;
        }

        Schema::create('responsables_organizacionales', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('sede_id');
            $table->enum('tipo_recurso', ['sede', 'almacen']);
            $table->unsignedBigInteger('almacen_id')->nullable();
            $table->unsignedBigInteger('responsable_usuario_id');
            $table->string('cargo', 100);
            $table->date('vigente_desde');
            $table->date('vigente_hasta')->nullable();
            $table->string('motivo', 500);
            $table->unsignedBigInteger('reemplaza_asignacion_id')->nullable();
            $table->unsignedBigInteger('asignado_por_usuario_id')->nullable();
            $table->boolean('activo')->default(true);
            $table->dateTime('creado_en', 6)->useCurrent();
            $table->dateTime('actualizado_en', 6)->useCurrent()->useCurrentOnUpdate();

            $table->unique(['id', 'empresa_id', 'sede_id'], 'un_responsables_id_empresa_sede');
            $table->index(['empresa_id', 'activo', 'vigente_desde', 'vigente_hasta'], 'ind_responsables_empresa_vigencia');
            $table->index(['empresa_id', 'sede_id', 'tipo_recurso', 'cargo', 'vigente_desde', 'vigente_hasta'], 'ind_responsables_sede_recurso');
            $table->index(['almacen_id', 'activo', 'vigente_desde', 'vigente_hasta'], 'ind_responsables_almacen_recurso');
            $table->index(['responsable_usuario_id', 'activo', 'vigente_hasta'], 'ind_responsables_usuario_vigencia');
            $table->index('reemplaza_asignacion_id', 'ind_responsables_relevo');
            $table->index('asignado_por_usuario_id', 'ind_responsables_asignador');

            $table->foreign('empresa_id', 'cf_responsables_empresa')->references('id')->on('empresas')->restrictOnUpdate()->restrictOnDelete();
            $table->foreign(['sede_id', 'empresa_id'], 'cf_responsables_sede_empresa')->references(['id', 'empresa_id'])->on('sedes')->restrictOnUpdate()->restrictOnDelete();
            $table->foreign(['almacen_id', 'sede_id'], 'cf_responsables_almacen_sede')->references(['id', 'sede_id'])->on('almacenes')->restrictOnUpdate()->restrictOnDelete();
            $table->foreign('responsable_usuario_id', 'cf_responsables_usuario')->references('id')->on('usuarios')->restrictOnUpdate()->restrictOnDelete();
            $table->foreign('asignado_por_usuario_id', 'cf_responsables_asignador')->references('id')->on('usuarios')->restrictOnUpdate()->nullOnDelete();
            $table->foreign(['reemplaza_asignacion_id', 'empresa_id', 'sede_id'], 'cf_responsables_relevo_contexto')
                ->references(['id', 'empresa_id', 'sede_id'])->on('responsables_organizacionales')->restrictOnUpdate()->restrictOnDelete();
        });

        $addChecks();
    }

    public function down(): void
    {
        throw new LogicException('This table is forward-only because it stores organizational history.');
    }
};
