<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atributos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 120);
            $table->string('tipo_dato', 20)->default('texto');
            $table->string('unidad_medida', 30)->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('visible')->default(true);
            $table->boolean('filtrable')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->string('tipo_control', 20)->default('checkbox');
            $table->foreignId('dependencia_atributo_id')->nullable()->constrained('atributos')->nullOnDelete();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->timestamp('creado_en', 6)->nullable();
            $table->timestamp('actualizado_en', 6)->nullable();
            $table->index(['activo', 'visible', 'filtrable', 'orden']);
            $table->unique(['empresa_id', 'nombre']);
        });

        Schema::create('atributo_valores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('atributo_id')->constrained('atributos')->cascadeOnDelete();
            $table->string('valor', 160);
            $table->string('valor_normalizado', 160);
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamp('creado_en', 6)->nullable();
            $table->timestamp('actualizado_en', 6)->nullable();
            $table->unique(['atributo_id', 'valor_normalizado']);
            $table->index(['atributo_id', 'activo', 'orden']);
        });

        Schema::create('categoria_atributo', function (Blueprint $table): void {
            $table->unsignedInteger('categoria_id');
            $table->foreignId('atributo_id')->constrained('atributos')->cascadeOnDelete();
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->primary(['categoria_id', 'atributo_id']);
            $table->foreign('categoria_id')->references('id')->on('categorias')->cascadeOnDelete();
            $table->index(['categoria_id', 'activo', 'orden']);
        });

        Schema::create('producto_atributo_valor', function (Blueprint $table): void {
            $table->unsignedInteger('producto_id');
            $table->foreignId('atributo_valor_id')->constrained('atributo_valores')->cascadeOnDelete();
            $table->primary(['producto_id', 'atributo_valor_id']);
            $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
            $table->index(['atributo_valor_id', 'producto_id']);
        });

        if (Schema::hasTable('caracteristicas_productos')) {
            DB::table('caracteristicas_productos')->select('nombre')->distinct()->orderBy('nombre')->get()
                ->each(function (object $caracteristica): void {
                    $nombre = trim((string) $caracteristica->nombre);
                    if ($nombre === '') {
                        return;
                    }

                    $atributoId = DB::table('atributos')->whereNull('empresa_id')->where('nombre', $nombre)->value('id');
                    $atributoId ??= DB::table('atributos')->insertGetId([
                        'nombre' => $nombre,
                        'tipo_dato' => 'texto',
                        'activo' => true,
                        'visible' => true,
                        'filtrable' => true,
                        'orden' => 0,
                        'tipo_control' => 'checkbox',
                        'creado_en' => now(),
                        'actualizado_en' => now(),
                    ]);

                    DB::table('caracteristicas_productos')->where('nombre', $nombre)->select('producto_id', 'valor')
                        ->orderBy('producto_id')->get()->each(function (object $item) use ($atributoId): void {
                            $valor = trim((string) $item->valor);
                            if ($valor === '') {
                                return;
                            }

                            $valorNormalizado = mb_strtolower($valor);
                            $valorId = DB::table('atributo_valores')->where('atributo_id', $atributoId)
                                ->where('valor_normalizado', $valorNormalizado)->value('id');
                            $valorId ??= DB::table('atributo_valores')->insertGetId([
                                'atributo_id' => $atributoId,
                                'valor' => $valor,
                                'valor_normalizado' => $valorNormalizado,
                                'activo' => true,
                                'orden' => 0,
                                'creado_en' => now(),
                                'actualizado_en' => now(),
                            ]);

                            DB::table('producto_atributo_valor')->insertOrIgnore([
                                'producto_id' => $item->producto_id,
                                'atributo_valor_id' => $valorId,
                            ]);
                        });
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_atributo_valor');
        Schema::dropIfExists('categoria_atributo');
        Schema::dropIfExists('atributo_valores');
        Schema::dropIfExists('atributos');
    }
};
