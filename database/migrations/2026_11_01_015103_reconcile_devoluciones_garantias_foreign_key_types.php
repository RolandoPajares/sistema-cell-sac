<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Align inherited return foreign keys with the legacy INT UNSIGNED primary keys without deleting rows.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (['devoluciones_garantias', 'pedidos', 'clientes', 'productos'] as $requiredTable) {
            if (! Schema::hasTable($requiredTable)) {
                throw new RuntimeException('Cannot reconcile return foreign keys; missing table: '.$requiredTable);
            }
        }

        $references = [
            'pedido_id' => ['table' => 'pedidos', 'column' => 'id'],
            'cliente_id' => ['table' => 'clientes', 'column' => 'id'],
            'producto_id' => ['table' => 'productos', 'column' => 'id'],
        ];
        $nullable = [];
        $unsigned = [];
        $columnsToConvert = [];

        foreach ($references as $column => $reference) {
            $referencedType = DB::table('information_schema.COLUMNS')
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', $reference['table'])
                ->where('COLUMN_NAME', $reference['column'])
                ->value('COLUMN_TYPE');

            if (preg_match('/^int(?:\(\d+\))?( unsigned)?$/', strtolower((string) $referencedType), $matches) !== 1) {
                throw new RuntimeException('Cannot reconcile devoluciones_garantias.'.$column.' because '.$reference['table'].'.'.$reference['column'].' is not an INT primary key; found '.(string) $referencedType.'.');
            }

            $unsigned[$column] = isset($matches[1]) && $matches[1] === ' unsigned';

            if (! Schema::hasColumn('devoluciones_garantias', $column)) {
                throw new RuntimeException('Cannot reconcile return foreign keys; missing column: devoluciones_garantias.'.$column);
            }

            $metadata = DB::table('information_schema.COLUMNS')
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', 'devoluciones_garantias')
                ->where('COLUMN_NAME', $column)
                ->first(['COLUMN_TYPE', 'IS_NULLABLE']);
            $type = strtolower((string) ($metadata?->COLUMN_TYPE ?? ''));
            $nullable[$column] = $metadata?->IS_NULLABLE === 'YES';

            if (preg_match('/^int(?:\(\d+\))?'.($unsigned[$column] ? ' unsigned' : '').'$/', $type) === 1) {
                continue;
            }

            if (preg_match('/^int(?:\(\d+\))?( unsigned)?$/', $type) !== 1) {
                throw new RuntimeException('Expected devoluciones_garantias.'.$column.' to be INT or INT UNSIGNED; found '.$type.'. No automatic type conversion was attempted.');
            }

            $this->assertValuesFitTarget($column, $unsigned[$column]);
            $this->assertNoOrphans($column, $reference['table'], $reference['column']);
            $columnsToConvert[] = $column;
        }

        if ($columnsToConvert !== []) {
            foreach (Schema::getForeignKeys('devoluciones_garantias') as $foreignKey) {
                if (count(array_intersect($columnsToConvert, $foreignKey['columns'])) === 0) {
                    continue;
                }

                Schema::table('devoluciones_garantias', function (Blueprint $table) use ($foreignKey): void {
                    $table->dropForeign($foreignKey['name']);
                });
            }

            foreach ($columnsToConvert as $column) {
                $nullability = $nullable[$column] ? 'NULL' : 'NOT NULL';
                DB::statement('ALTER TABLE `devoluciones_garantias` MODIFY `'.$column.'` INT'.($unsigned[$column] ? ' UNSIGNED' : '').' '.$nullability);
            }
        }

        foreach ($references as $column => $reference) {
            $this->ensureForeignKey($column, $reference['table'], $reference['column'], $nullable[$column]);
        }
    }

    public function down(): void
    {
        throw new LogicException('Return key normalization is forward-only and preserves inherited business rows.');
    }

    private function assertValuesFitTarget(string $column, bool $unsigned): void
    {
        $hasOutOfRangeValues = DB::table('devoluciones_garantias')
            ->where($column, $unsigned ? '<' : '>', $unsigned ? 0 : 2147483647)
            ->exists();

        if ($hasOutOfRangeValues) {
            throw new RuntimeException('Cannot convert devoluciones_garantias.'.$column.' because values exceed the referenced INT range. Reconcile those rows before migration.');
        }
    }

    private function assertNoOrphans(string $column, string $referenceTable, string $referenceColumn): void
    {
        $orphanExists = DB::table('devoluciones_garantias')
            ->whereNotNull($column)
            ->whereNotExists(function ($query) use ($column, $referenceTable, $referenceColumn): void {
                $query->selectRaw('1')
                    ->from($referenceTable)
                    ->whereColumn($referenceTable.'.'.$referenceColumn, 'devoluciones_garantias.'.$column);
            })
            ->exists();

        if ($orphanExists) {
            throw new RuntimeException('Cannot add the foreign key for devoluciones_garantias.'.$column.'; orphan rows were found. Reconcile on an isolated copy first.');
        }
    }

    private function ensureForeignKey(string $column, string $referenceTable, string $referenceColumn, bool $nullable): void
    {
        foreach (Schema::getForeignKeys('devoluciones_garantias') as $foreignKey) {
            if ($foreignKey['columns'] === [$column]
                && $foreignKey['foreign_table'] === $referenceTable
                && $foreignKey['foreign_columns'] === [$referenceColumn]) {
                return;
            }
        }

        $this->assertNoOrphans($column, $referenceTable, $referenceColumn);
        $constraint = 'devoluciones_garantias_'.$column.'_foreign';

        Schema::table('devoluciones_garantias', function (Blueprint $table) use ($column, $referenceTable, $referenceColumn, $nullable, $constraint): void {
            $foreign = $table->foreign($column, $constraint)->references($referenceColumn)->on($referenceTable);

            if ($nullable) {
                $foreign->nullOnDelete();
            } else {
                $foreign->restrictOnDelete();
            }
        });
    }
};
