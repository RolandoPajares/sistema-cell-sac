<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add nullable scope keys so legacy ecommerce and operational rows remain readable while being backfilled.
     */
    public function up(): void
    {
        foreach (['empresas', 'sedes', 'almacenes'] as $requiredTable) {
            if (! Schema::hasTable($requiredTable)) {
                throw new RuntimeException('Apply the organization/RBAC migration before adding operation scopes. Missing table: '.$requiredTable);
            }
        }

        if (! Schema::hasIndex('sedes', ['id', 'empresa_id'], 'unique')) {
            Schema::table('sedes', function (Blueprint $table): void {
                $table->unique(['id', 'empresa_id'], 'sedes_id_empresa_id_unique');
            });
        }

        if (! Schema::hasIndex('almacenes', ['id', 'sede_id'], 'unique')) {
            Schema::table('almacenes', function (Blueprint $table): void {
                $table->unique(['id', 'sede_id'], 'almacenes_id_sede_id_unique');
            });
        }

        foreach (['pedidos', 'compras', 'cotizaciones', 'movimientos_inventario', 'devoluciones_garantias'] as $businessTable) {
            if (! Schema::hasTable($businessTable)) {
                throw new RuntimeException('The legacy business table required for site scoping is missing: '.$businessTable);
            }

            $missingEmpresaId = ! Schema::hasColumn($businessTable, 'empresa_id');
            $missingSedeId = ! Schema::hasColumn($businessTable, 'sede_id');
            $hasAlmacen = in_array($businessTable, ['pedidos', 'compras', 'movimientos_inventario'], true);
            $missingAlmacenId = $hasAlmacen && ! Schema::hasColumn($businessTable, 'almacen_id');

            Schema::table($businessTable, function (Blueprint $table) use ($missingEmpresaId, $missingSedeId, $missingAlmacenId): void {
                if ($missingEmpresaId) {
                    $table->foreignId('empresa_id')->nullable();
                }

                if ($missingSedeId) {
                    $table->foreignId('sede_id')->nullable();
                }

                if ($missingAlmacenId) {
                    $table->foreignId('almacen_id')->nullable();
                }
            });

            $this->assertUnsignedBigintColumns($businessTable, ['empresa_id', 'sede_id', ...($hasAlmacen ? ['almacen_id'] : [])]);
            $this->ensureForeignKey($businessTable, ['empresa_id'], 'empresas', ['id'], $businessTable.'_empresa_foreign');
            $this->ensureForeignKey($businessTable, ['sede_id'], 'sedes', ['id'], $businessTable.'_sede_foreign');
            $this->ensureForeignKey($businessTable, ['sede_id', 'empresa_id'], 'sedes', ['id', 'empresa_id'], $businessTable.'_sede_empresa_foreign');

            if ($hasAlmacen) {
                $this->ensureForeignKey($businessTable, ['almacen_id'], 'almacenes', ['id'], $businessTable.'_almacen_foreign');
                $this->ensureForeignKey($businessTable, ['almacen_id', 'sede_id'], 'almacenes', ['id', 'sede_id'], $businessTable.'_almacen_sede_foreign');
            }
        }
    }

    public function down(): void
    {
        throw new LogicException('Business scope columns are forward-only after data backfill; use a reviewed recovery plan instead of dropping them.');
    }

    /** @param array<int, string> $columns
     * @param  array<int, string>  $foreignColumns
     */
    private function ensureForeignKey(string $table, array $columns, string $foreignTable, array $foreignColumns, string $name): void
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if ($foreignKey['columns'] === $columns
                && $foreignKey['foreign_table'] === $foreignTable
                && $foreignKey['foreign_columns'] === $foreignColumns) {
                return;
            }
        }

        if ($this->hasOrphanRows($table, $columns, $foreignTable, $foreignColumns)) {
            throw new RuntimeException('Cannot add '.$name.' because existing values in '.$table.' do not match '.$foreignTable.'. Reconcile the data on an isolated copy first.');
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $foreignTable, $foreignColumns, $name): void {
            $blueprint->foreign($columns, $name)
                ->references($foreignColumns)
                ->on($foreignTable)
                ->restrictOnDelete();
        });
    }

    /** @param array<int, string> $columns
     * @param  array<int, string>  $foreignColumns
     */
    private function hasOrphanRows(string $table, array $columns, string $foreignTable, array $foreignColumns): bool
    {
        $orphans = DB::table($table)->whereNotNull($columns[0]);

        foreach (array_slice($columns, 1) as $column) {
            $orphans->whereNotNull($column);
        }

        return $orphans->whereNotExists(function ($query) use ($table, $columns, $foreignTable, $foreignColumns): void {
            $query->selectRaw('1')->from($foreignTable);
            foreach ($columns as $index => $column) {
                $query->whereColumn($foreignTable.'.'.$foreignColumns[$index], $table.'.'.$column);
            }
        })->exists();
    }

    /** @param array<int, string> $columns */
    private function assertUnsignedBigintColumns(string $table, array $columns): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($columns as $column) {
            $columnType = DB::table('information_schema.COLUMNS')
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', $table)
                ->where('COLUMN_NAME', $column)
                ->value('COLUMN_TYPE');

            if (preg_match('/^bigint(?:\(\d+\))? unsigned$/', strtolower((string) $columnType)) !== 1) {
                throw new RuntimeException('Expected '.$table.'.'.$column.' to be BIGINT UNSIGNED to match its organization foreign key. No automatic type conversion was attempted.');
            }
        }
    }
};
