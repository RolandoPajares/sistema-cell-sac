<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros_auditoria', function (Blueprint $table): void {
            $table->dateTime('ocurrido_en', 6)->nullable()->index();
            $table->string('tabla_afectada', 100)->nullable();
            $table->string('identificador_entidad', 128)->nullable();
            $table->json('cambios')->nullable();
            $table->string('origen', 20)->default('laravel')->index();
            $table->index(['tabla_afectada', 'identificador_entidad', 'ocurrido_en'], 'auditoria_entidad_fecha_idx');
            $table->index(['sede_id', 'ocurrido_en'], 'auditoria_sede_fecha_idx');
        });

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $events = [
            ['tc_roles_au', 'UPDATE', 'roles', 'NEW.id', 'seguridad', "JSON_OBJECT('codigo', NEW.codigo, 'activo_anterior', OLD.activo, 'activo_nuevo', NEW.activo)", 'NULL', 'NULL'],
            ['tc_rol_permiso_ai', 'INSERT', 'rol_permiso', 'NEW.rol_id', 'seguridad', "JSON_OBJECT('rol_id', NEW.rol_id, 'permiso_id', NEW.permiso_id, 'alcance', NEW.alcance)", 'NULL', 'NULL'],
            ['tc_rol_permiso_ad', 'DELETE', 'rol_permiso', 'OLD.rol_id', 'seguridad', "JSON_OBJECT('rol_id', OLD.rol_id, 'permiso_id', OLD.permiso_id, 'alcance', OLD.alcance)", 'NULL', 'NULL'],
            ['tc_usuario_roles_ai', 'INSERT', 'usuario_roles', 'NEW.usuario_id', 'seguridad', "JSON_OBJECT('rol_id', NEW.rol_id, 'activo', NEW.activo)", 'NULL', 'NULL'],
            ['tc_usuario_sede_roles_ai', 'INSERT', 'usuario_sede_roles', 'NEW.usuario_id', 'seguridad', "JSON_OBJECT('rol_id', NEW.rol_id, 'sede_id', NEW.sede_id, 'activo', NEW.activo)", 'NULL', 'NEW.sede_id'],
            ['tc_usuario_sede_roles_ad', 'DELETE', 'usuario_sede_roles', 'OLD.usuario_id', 'seguridad', "JSON_OBJECT('rol_id', OLD.rol_id, 'sede_id', OLD.sede_id)", 'NULL', 'OLD.sede_id'],
            ['tc_elementos_menu_au', 'UPDATE', 'elementos_menu', 'NEW.id', 'seguridad', "JSON_OBJECT('habilitado_anterior', OLD.habilitado, 'habilitado_nuevo', NEW.habilitado, 'orden_anterior', OLD.orden, 'orden_nuevo', NEW.orden)", 'NULL', 'NULL'],
            ['tc_movimientos_inventario_ai', 'INSERT', 'movimientos_inventario', 'NEW.id', 'inventario', "JSON_OBJECT('producto_id', NEW.producto_id, 'variante_id', NEW.variante_id, 'almacen_id', NEW.almacen_id, 'tipo', NEW.tipo_movimiento, 'cantidad', NEW.cantidad)", 'NEW.empresa_id', 'NEW.sede_id'],
            ['tc_pedidos_au', 'UPDATE', 'pedidos', 'NEW.id', 'pedidos', "JSON_OBJECT('estado_anterior', OLD.estado, 'estado_nuevo', NEW.estado)", 'NEW.empresa_id', 'NEW.sede_id'],
            ['tc_compras_au', 'UPDATE', 'compras', 'NEW.id', 'compras', "JSON_OBJECT('estado_anterior', OLD.estado, 'estado_nuevo', NEW.estado)", 'NEW.empresa_id', 'NEW.sede_id'],
            ['tc_ventas_ai', 'INSERT', 'ventas', 'NEW.id', 'ventas', "JSON_OBJECT('estado', NEW.estado, 'total', NEW.total)", 'NEW.empresa_id', 'NEW.sede_id'],
            ['tc_pagos_ai', 'INSERT', 'pagos', 'NEW.id', 'pagos', "JSON_OBJECT('estado', NEW.estado, 'importe', NEW.importe)", 'NEW.empresa_id', 'NEW.sede_id'],
            ['tc_documentos_electronicos_ai', 'INSERT', 'documentos_electronicos', 'NEW.id', 'facturacion', "JSON_OBJECT('tipo_documento', NEW.tipo_documento, 'estado', NEW.estado)", 'NEW.empresa_id', 'NEW.sede_id'],
        ];

        foreach ($events as [$name, $event, $table, $recordId, $module, $changes, $companyId, $siteId]) {
            DB::unprepared("CREATE TRIGGER `{$name}` AFTER {$event} ON `{$table}` FOR EACH ROW
                INSERT INTO registros_auditoria
                    (evento_uuid, ocurrido_en, usuario_id, rol_codigo, empresa_id, sede_id, modulo, accion, entidad, tabla_afectada, identificador_entidad, cambios, origen, resultado, creado_en)
                VALUES (UUID(), UTC_TIMESTAMP(6), @tc_user_id, @tc_role_code, {$companyId}, {$siteId}, '{$module}', '{$event}', '{$table}', '{$table}', CAST({$recordId} AS CHAR), {$changes}, 'trigger', 'exito', UTC_TIMESTAMP())");
        }

        DB::unprepared("CREATE TRIGGER `tc_auditoria_bu` BEFORE UPDATE ON `registros_auditoria` FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'registros_auditoria is append-only'");
        DB::unprepared("CREATE TRIGGER `tc_auditoria_bd` BEFORE DELETE ON `registros_auditoria` FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'registros_auditoria is append-only'");
    }

    public function down(): void
    {
        throw new LogicException('Audit events are forward-only and must remain traceable.');
    }
};
