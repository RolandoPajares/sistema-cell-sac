<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la operación up definida por la clase 2026_11_05_140000_add_tipo_documento_to_clientes.
     *
     * @return void Resultado de la operación.
     */
    public function up(): void
    {
        if (! Schema::hasTable('clientes') || Schema::hasColumn('clientes', 'tipo_documento')) {
            return;
        }

        Schema::table('clientes', function (Blueprint $table): void {
            $table->string('tipo_documento', 3)->nullable()->after('tipo')->index();
        });
    }

    /**
     * Ejecuta la operación down definida por la clase 2026_11_05_140000_add_tipo_documento_to_clientes.
     *
     * @return void Resultado de la operación.
     */
    public function down(): void
    {
        if (Schema::hasTable('clientes') && Schema::hasColumn('clientes', 'tipo_documento')) {
            Schema::table('clientes', function (Blueprint $table): void {
                $table->dropColumn('tipo_documento');
            });
        }
    }
};
