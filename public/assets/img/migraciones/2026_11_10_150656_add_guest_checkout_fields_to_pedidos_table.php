<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table): void {
            $table->unsignedBigInteger('usuario_id')->nullable()->change();
            if (! Schema::hasColumn('pedidos', 'cliente_invitado_nombre')) {
                $table->string('cliente_invitado_nombre', 160)->nullable()->after('usuario_id');
            }
            if (! Schema::hasColumn('pedidos', 'cliente_invitado_correo')) {
                $table->string('cliente_invitado_correo', 160)->nullable()->after('cliente_invitado_nombre');
            }
            if (! Schema::hasColumn('pedidos', 'cliente_invitado_telefono')) {
                $table->string('cliente_invitado_telefono', 30)->nullable()->after('cliente_invitado_correo');
            }
            if (! Schema::hasColumn('pedidos', 'token_acceso_publico')) {
                $table->char('token_acceso_publico', 64)->nullable()->unique()->after('cliente_invitado_telefono');
            }
            if (! Schema::hasColumn('pedidos', 'direccion_entrega_snapshot')) {
                $table->json('direccion_entrega_snapshot')->nullable()->after('token_acceso_publico');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table): void {
            if (Schema::hasColumn('pedidos', 'token_acceso_publico')) {
                $table->dropUnique(['token_acceso_publico']);
            }
            foreach (['cliente_invitado_nombre', 'cliente_invitado_correo', 'cliente_invitado_telefono', 'token_acceso_publico'] as $column) {
                if (Schema::hasColumn('pedidos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
