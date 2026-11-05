<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('detalles_transferencias_existencias')) {
            return;
        }

        if (! Schema::hasColumn('detalles_transferencias_existencias', 'cantidad_devuelta')) {
            Schema::table('detalles_transferencias_existencias', function (Blueprint $table): void {
                $table->decimal('cantidad_devuelta', 18, 6)->default(0)->after('cantidad_recibida');
            });
        }

        if (! Schema::hasColumn('detalles_transferencias_existencias', 'cantidad_conciliada')) {
            Schema::table('detalles_transferencias_existencias', function (Blueprint $table): void {
                $table->decimal('cantidad_conciliada', 18, 6)->default(0)->after('cantidad_devuelta');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('detalles_transferencias_existencias')) {
            return;
        }

        if ((Schema::hasColumn('detalles_transferencias_existencias', 'cantidad_devuelta')
                && DB::table('detalles_transferencias_existencias')->where('cantidad_devuelta', '>', 0)->exists())
            || (Schema::hasColumn('detalles_transferencias_existencias', 'cantidad_conciliada')
                && DB::table('detalles_transferencias_existencias')->where('cantidad_conciliada', '>', 0)->exists())) {
            throw new LogicException('La migración no se puede revertir porque ya hay cantidades devueltas registradas.');
        }

        $columns = array_values(array_filter(
            ['cantidad_conciliada', 'cantidad_devuelta'],
            static fn (string $column): bool => Schema::hasColumn('detalles_transferencias_existencias', $column),
        ));
        if ($columns !== []) {
            Schema::table('detalles_transferencias_existencias', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
