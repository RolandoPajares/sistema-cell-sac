<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('registros_auditoria')) {
            throw new RuntimeException('The users and registros_auditoria legacy tables are required before applying account security fields.');
        }
        if (Schema::hasTable('sesiones_usuario')) {
            throw new RuntimeException('The sesiones_usuario table already exists; inspect it before applying the security migration.');
        }

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'foto_perfil')) {
                $table->string('foto_perfil', 500)->nullable();
            }

            if (! Schema::hasColumn('users', 'preferencias')) {
                $table->json('preferencias')->nullable();
            }

            if (! Schema::hasColumn('users', 'activo')) {
                $table->boolean('activo')->default(true)->index();
            }

            if (! Schema::hasColumn('users', 'bloqueado_hasta')) {
                $table->dateTime('bloqueado_hasta')->nullable();
            }

            if (! Schema::hasColumn('users', 'rbac_controlado')) {
                $table->boolean('rbac_controlado')->default(false)->index();
            }
        });

        Schema::create('sesiones_usuario', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->char('identificador_hash', 64)->unique();
            $table->string('direccion_ip', 45)->nullable();
            $table->text('agente_usuario')->nullable();
            $table->timestamp('ultimo_acceso')->useCurrent();
            $table->timestamp('revocada_en')->nullable();
            $table->timestamps();
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

    public function down(): void
    {
        throw new LogicException('This account-security migration is forward-only to preserve profile, session, and notification data.');
    }
};
