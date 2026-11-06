<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('usuarios') || ! Schema::hasTable('users')) {
            return;
        }

        $legacyUsers = DB::table('usuarios')->get();

        if (DB::table('users')->count() === 0) {
            foreach ($legacyUsers as $legacyUser) {
                DB::table('users')->insert([
                    'id' => $legacyUser->id,
                    'nombre' => $legacyUser->nombre,
                    'correo' => $legacyUser->correo,
                    'password' => $legacyUser->contrasena,
                    'rol' => $legacyUser->rol,
                    'remember_token' => null,
                    'created_at' => $legacyUser->creado_en,
                    'updated_at' => $legacyUser->creado_en,
                ]);
            }
        }

        $missingLegacyUsers = $legacyUsers
            ->pluck('id')
            ->filter(fn (mixed $id): bool => ! DB::table('users')->where('id', $id)->exists());

        if ($missingLegacyUsers->isNotEmpty()) {
            return;
        }

        $this->syncForeignKeys('users', 'BIGINT UNSIGNED');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('usuarios')) {
            return;
        }

        $this->syncForeignKeys('usuarios', 'INT');
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $referencedTable  Parámetro recibido por la operación.
     * @param  mixed  $columnType  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    private function syncForeignKeys(string $referencedTable, string $columnType): void
    {
        $foreignKeys = [
            ['table' => 'clientes', 'column' => 'usuario_id', 'constraint' => 'fk_clientes_usuario'],
            ['table' => 'compras', 'column' => 'creado_por', 'constraint' => 'fk_compras_usuario'],
            ['table' => 'cotizaciones', 'column' => 'creado_por', 'constraint' => 'fk_cotizaciones_usuario'],
            ['table' => 'movimientos_inventario', 'column' => 'usuario_id', 'constraint' => 'fk_movimientos_usuario'],
            ['table' => 'pedidos', 'column' => 'usuario_id', 'constraint' => 'fk_pedidos_usuario'],
            ['table' => 'registros_auditoria', 'column' => 'usuario_id', 'constraint' => 'fk_auditoria_usuario'],
        ];

        foreach ($foreignKeys as $foreignKey) {
            if (! Schema::hasTable($foreignKey['table']) || ! Schema::hasColumn($foreignKey['table'], $foreignKey['column'])) {
                continue;
            }

            $nullable = DB::table('information_schema.COLUMNS')
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', $foreignKey['table'])
                ->where('COLUMN_NAME', $foreignKey['column'])
                ->value('IS_NULLABLE') === 'YES';
            $nullability = $nullable ? 'NULL' : 'NOT NULL';

            if ($this->foreignKeyExists($foreignKey['table'], $foreignKey['constraint'])) {
                DB::statement(sprintf(
                    'ALTER TABLE `%s` DROP FOREIGN KEY `%s`',
                    $foreignKey['table'],
                    $foreignKey['constraint'],
                ));
            }

            DB::statement(sprintf(
                'ALTER TABLE `%s` MODIFY `%s` %s %s',
                $foreignKey['table'],
                $foreignKey['column'],
                $columnType,
                $nullability,
            ));
            DB::statement(sprintf(
                'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`id`)',
                $foreignKey['table'],
                $foreignKey['constraint'],
                $foreignKey['column'],
                $referencedTable,
            ));
        }
    }

    /**
     * Ejecuta la operación foreignKeyExists definida por la clase 2026_10_10_172618_migrate_legacy_users_to_laravel_users.
     *
     * @param  mixed  $table  Parámetro recibido por la operación.
     * @param  mixed  $constraint  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    private function foreignKeyExists(string $table, string $constraint): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->exists();
    }
};
