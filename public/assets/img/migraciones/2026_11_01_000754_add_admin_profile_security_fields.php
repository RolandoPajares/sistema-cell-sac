<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la operación up definida por la clase 2026_11_01_000754_add_admin_profile_security_fields.
     *
     * @return void Resultado de la operación.
     */
    public function up(): void
    {
        $userTable = Schema::hasTable('usuarios') ? 'usuarios' : 'users';

        if (! Schema::hasTable($userTable) || ! Schema::hasTable('registros_auditoria')) {
            throw new RuntimeException('The application user table and registros_auditoria are required before applying account security fields.');
        }
        $sessionTable = $userTable === 'usuarios' ? 'sesiones_usuarios' : 'sesiones_usuario';
        if (Schema::hasTable($sessionTable)) {
            throw new RuntimeException('The '.$sessionTable.' table already exists; inspect it before applying the security migration.');
        }

        Schema::table($userTable, function (Blueprint $table) use ($userTable): void {
            if (! Schema::hasColumn($userTable, 'foto_perfil')) {
                $table->string('foto_perfil', 500)->nullable();
            }

            if (! Schema::hasColumn($userTable, 'preferencias')) {
                $table->json('preferencias')->nullable();
            }

            if (! Schema::hasColumn($userTable, 'activo')) {
                $table->boolean('activo')->default(true)->index();
            }

            if (! Schema::hasColumn($userTable, 'bloqueado_hasta')) {
                $table->dateTime('bloqueado_hasta')->nullable();
            }

            $controlColumn = $userTable === 'usuarios' ? 'acceso_controlado' : 'rbac_controlado';
            if (! Schema::hasColumn($userTable, $controlColumn)) {
                $table->boolean($controlColumn)->default(false)->index();
            }
        });

        Schema::create($sessionTable, function (Blueprint $table) use ($userTable, $sessionTable): void {
            $table->id();
            $table->foreignId('usuario_id')->constrained($userTable)->cascadeOnDelete();
            $table->char($sessionTable === 'sesiones_usuarios' ? 'resumen_identificador' : 'identificador_hash', 64)->unique();
            $table->string('direccion_ip', 45)->nullable();
            $table->text('agente_usuario')->nullable();
            $table->timestamp('ultimo_acceso')->useCurrent();
            $table->timestamp('revocada_en')->nullable();
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->useCurrent();
            $table->index(['usuario_id', 'revocada_en', 'ultimo_acceso']);
        });

        if (! Schema::hasTable('registros_auditoria')) {
            throw new RuntimeException('The legacy registros_auditoria table is required before adding audit context.');
        }

        Schema::table('registros_auditoria', function (Blueprint $table): void {
            if (! Schema::hasColumn('registros_auditoria', 'evento_uuid')) {
                $table->uuid('evento_uuid')->nullable()->unique();
            }
            if (! Schema::hasColumn('registros_auditoria', 'rol_codigo')) {
                $table->string('rol_codigo', 40)->nullable()->index();
            }
            if (! Schema::hasColumn('registros_auditoria', 'empresa_id')) {
                $table->foreignId('empresa_id')->nullable()->constrained('empresas')->restrictOnDelete();
            }
            if (! Schema::hasColumn('registros_auditoria', 'sede_id')) {
                $table->foreignId('sede_id')->nullable()->constrained('sedes')->restrictOnDelete();
            }
            if (! Schema::hasColumn('registros_auditoria', 'modulo')) {
                $table->string('modulo', 80)->nullable()->index();
            }
            if (! Schema::hasColumn('registros_auditoria', 'resultado')) {
                $table->string('resultado', 20)->nullable()->index();
            }
            if (! Schema::hasColumn('registros_auditoria', 'agente_usuario')) {
                $table->text('agente_usuario')->nullable();
            }
            if (! Schema::hasColumn('registros_auditoria', 'correlacion_id')) {
                $table->string('correlacion_id', 64)->nullable()->index();
            }
            if (! Schema::hasColumn('registros_auditoria', 'observaciones')) {
                $table->text('observaciones')->nullable();
            }
        });

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
            });
        }
    }

    /**
     * Ejecuta la operación down definida por la clase 2026_11_01_000754_add_admin_profile_security_fields.
     *
     * @return void Resultado de la operación.
     */
    public function down(): void
    {
        throw new LogicException('This account-security migration is forward-only to preserve profile, session, and notification data.');
    }
};
