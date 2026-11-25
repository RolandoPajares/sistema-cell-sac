<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categorias') || Schema::hasColumn('categorias', 'categoria_padre_id')) {
            return;
        }

        Schema::table('categorias', function (Blueprint $table): void {
            $table->unsignedInteger('categoria_padre_id')->nullable()->after('id');
            $table->unsignedInteger('orden')->default(0)->after('activo');
            $table->foreign('categoria_padre_id')->references('id')->on('categorias')->nullOnDelete();
            $table->index(['categoria_padre_id', 'activo', 'orden']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('categorias') || ! Schema::hasColumn('categorias', 'categoria_padre_id')) {
            return;
        }

        Schema::table('categorias', function (Blueprint $table): void {
            $table->dropForeign(['categoria_padre_id']);
            $table->dropIndex(['categoria_padre_id', 'activo', 'orden']);
            $table->dropColumn(['categoria_padre_id', 'orden']);
        });
    }
};
