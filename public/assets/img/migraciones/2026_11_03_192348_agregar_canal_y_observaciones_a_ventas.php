<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (! Schema::hasTable('ventas')) {
            throw new LogicException('La tabla ventas debe existir antes de ampliar sus columnas.');
        }

        if (! Schema::hasColumn('ventas', 'canal')) {
            Schema::table('ventas', function (Blueprint $table): void {
                $table->string('canal', 30)->nullable();
            });
        }

        if (! Schema::hasColumn('ventas', 'observaciones')) {
            Schema::table('ventas', function (Blueprint $table): void {
                $table->string('observaciones', 500)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new LogicException('La reversión podría eliminar canales y observaciones registrados.');
    }
};
