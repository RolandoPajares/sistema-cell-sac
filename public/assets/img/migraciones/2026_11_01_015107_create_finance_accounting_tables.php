<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la operación up definida por la clase 2026_11_01_015107_create_finance_accounting_tables.
     *
     * @return void Resultado de la operación.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        foreach (['medios_pago', 'cajas', 'sesiones_caja', 'cuentas_bancarias', 'pagos', 'intentos_pago', 'movimientos_caja', 'movimientos_bancarios', 'cuentas_cobrar', 'cuentas_pagar', 'aplicaciones_cobro', 'aplicaciones_pago', 'plan_cuentas', 'asientos_contables', 'asiento_lineas'] as $table) {
            if (Schema::hasTable($table)) {
                throw new RuntimeException('The enterprise table already exists: '.$table.'. Restore a clean copy before retrying.');
            }
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE medios_pago (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(40) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  requiere_caja BOOLEAN NOT NULL DEFAULT FALSE,
  requiere_banco BOOLEAN NOT NULL DEFAULT FALSE,
  activo BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (id),
  UNIQUE KEY uq_medios_pago_codigo (codigo),
  KEY ix_medios_pago_activo (activo, nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE cajas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  sede_id BIGINT UNSIGNED NOT NULL,
  codigo VARCHAR(40) NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  activa BOOLEAN NOT NULL DEFAULT TRUE,
  created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_cajas_sede_codigo (sede_id, codigo),
  CONSTRAINT fk_cajas_sede FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE sesiones_caja (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  caja_id BIGINT UNSIGNED NOT NULL,
  usuario_apertura_id BIGINT UNSIGNED NOT NULL,
  usuario_cierre_id BIGINT UNSIGNED NULL,
  estado VARCHAR(20) NOT NULL DEFAULT 'abierta',
  saldo_inicial DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  saldo_cierre_declarado DECIMAL(15,2) NULL,
  abierta_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  cerrada_en DATETIME(6) NULL,
  PRIMARY KEY (id),
  KEY ix_sesiones_caja_caja_estado (caja_id, estado, abierta_en),
  KEY ix_sesiones_caja_usuario (usuario_apertura_id, abierta_en),
  CONSTRAINT fk_sesiones_caja_caja FOREIGN KEY (caja_id) REFERENCES cajas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_sesiones_caja_apertura FOREIGN KEY (usuario_apertura_id) REFERENCES users(id) ON DELETE RESTRICT,
  CONSTRAINT fk_sesiones_caja_cierre FOREIGN KEY (usuario_cierre_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT ck_sesiones_caja_saldos CHECK (saldo_inicial >= 0 AND (saldo_cierre_declarado IS NULL OR saldo_cierre_declarado >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE cuentas_bancarias (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  codigo VARCHAR(40) NOT NULL,
  banco VARCHAR(120) NOT NULL,
  tipo_cuenta VARCHAR(40) NOT NULL,
  moneda CHAR(3) NOT NULL DEFAULT 'PEN',
  cuenta_mascara VARCHAR(40) NULL COMMENT 'Solo identificador enmascarado; no guardar credenciales ni cuenta completa si no es necesario',
  referencia_secreto VARCHAR(255) NULL COMMENT 'Referencia externa a vault; nunca guardar secreto aqui',
  activa BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cuentas_bancarias_empresa_codigo (empresa_id, codigo),
  CONSTRAINT fk_cuentas_bancarias_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE pagos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  sede_id BIGINT UNSIGNED NOT NULL,
  codigo VARCHAR(60) NOT NULL,
  tipo ENUM('cobro','pago','reembolso') NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'registrado',
  medio_pago_id BIGINT UNSIGNED NOT NULL,
  sesion_caja_id BIGINT UNSIGNED NULL,
  cuenta_bancaria_id BIGINT UNSIGNED NULL,
  usuario_id BIGINT UNSIGNED NULL,
  pedido_id INT NULL,
  venta_id BIGINT UNSIGNED NULL,
  compra_id INT NULL,
  moneda CHAR(3) NOT NULL DEFAULT 'PEN',
  importe DECIMAL(15,2) NOT NULL,
  referencia VARCHAR(120) NULL,
  fecha_pago DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_pagos_empresa_codigo (empresa_id, codigo),
  KEY ix_pagos_sede_tipo_estado_fecha (sede_id, tipo, estado, fecha_pago),
  KEY ix_pagos_medio_fecha (medio_pago_id, fecha_pago),
  KEY ix_pagos_pedido_fecha (pedido_id, fecha_pago),
  KEY ix_pagos_venta_fecha (venta_id, fecha_pago),
  KEY ix_pagos_compra_fecha (compra_id, fecha_pago),
  CONSTRAINT fk_pagos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_pagos_sede FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_pagos_medio FOREIGN KEY (medio_pago_id) REFERENCES medios_pago(id) ON DELETE RESTRICT,
  CONSTRAINT fk_pagos_sesion_caja FOREIGN KEY (sesion_caja_id) REFERENCES sesiones_caja(id) ON DELETE RESTRICT,
  CONSTRAINT fk_pagos_cuenta_bancaria FOREIGN KEY (cuenta_bancaria_id) REFERENCES cuentas_bancarias(id) ON DELETE RESTRICT,
  CONSTRAINT fk_pagos_usuario FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_pagos_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_pagos_venta FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_pagos_compra FOREIGN KEY (compra_id) REFERENCES compras(id) ON DELETE RESTRICT,
  CONSTRAINT ck_pagos_importe CHECK (importe > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE intentos_pago (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  pedido_id INT NOT NULL,
  medio_pago_id BIGINT UNSIGNED NOT NULL,
  pago_id BIGINT UNSIGNED NULL,
  proveedor VARCHAR(100) NOT NULL,
  clave_idempotencia CHAR(64) NOT NULL,
  referencia_externa VARCHAR(160) NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'iniciado',
  moneda CHAR(3) NOT NULL DEFAULT 'PEN',
  importe DECIMAL(15,2) NOT NULL,
  codigo_respuesta VARCHAR(80) NULL,
  detalle_seguro JSON NULL COMMENT 'Solo datos saneados; no almacenar PAN, CVV, tokens ni secretos',
  solicitado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  respondido_en DATETIME(6) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_intentos_pago_idempotencia (empresa_id, clave_idempotencia),
  UNIQUE KEY uq_intentos_pago_pago (pago_id),
  KEY ix_intentos_pago_pedido_estado (pedido_id, estado, solicitado_en),
  KEY ix_intentos_pago_proveedor_ref (proveedor, referencia_externa),
  CONSTRAINT fk_intentos_pago_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_intentos_pago_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_intentos_pago_medio FOREIGN KEY (medio_pago_id) REFERENCES medios_pago(id) ON DELETE RESTRICT,
  CONSTRAINT fk_intentos_pago_pago FOREIGN KEY (pago_id) REFERENCES pagos(id) ON DELETE RESTRICT,
  CONSTRAINT ck_intentos_pago_importe CHECK (importe > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE movimientos_caja (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  sesion_caja_id BIGINT UNSIGNED NOT NULL,
  pago_id BIGINT UNSIGNED NULL,
  tipo VARCHAR(40) NOT NULL,
  direccion ENUM('entrada','salida') NOT NULL,
  importe DECIMAL(15,2) NOT NULL,
  descripcion VARCHAR(500) NULL,
  creado_por BIGINT UNSIGNED NULL,
  creado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  KEY ix_movimientos_caja_sesion_fecha (sesion_caja_id, creado_en),
  KEY ix_movimientos_caja_pago (pago_id),
  CONSTRAINT fk_movimientos_caja_sesion FOREIGN KEY (sesion_caja_id) REFERENCES sesiones_caja(id) ON DELETE RESTRICT,
  CONSTRAINT fk_movimientos_caja_pago FOREIGN KEY (pago_id) REFERENCES pagos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_movimientos_caja_usuario FOREIGN KEY (creado_por) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT ck_movimientos_caja_importe CHECK (importe > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE movimientos_bancarios (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cuenta_bancaria_id BIGINT UNSIGNED NOT NULL,
  pago_id BIGINT UNSIGNED NULL,
  tipo VARCHAR(40) NOT NULL,
  importe DECIMAL(15,2) NOT NULL,
  referencia VARCHAR(120) NULL,
  ocurrido_en DATETIME(6) NOT NULL,
  conciliado BOOLEAN NOT NULL DEFAULT FALSE,
  conciliado_en DATETIME(6) NULL,
  PRIMARY KEY (id),
  KEY ix_movimientos_bancarios_cuenta_fecha (cuenta_bancaria_id, ocurrido_en),
  KEY ix_movimientos_bancarios_conciliacion (cuenta_bancaria_id, conciliado, ocurrido_en),
  CONSTRAINT fk_movimientos_bancarios_cuenta FOREIGN KEY (cuenta_bancaria_id) REFERENCES cuentas_bancarias(id) ON DELETE RESTRICT,
  CONSTRAINT fk_movimientos_bancarios_pago FOREIGN KEY (pago_id) REFERENCES pagos(id) ON DELETE RESTRICT,
  CONSTRAINT ck_movimientos_bancarios_importe CHECK (importe > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE cuentas_cobrar (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  documento_electronico_id BIGINT UNSIGNED NOT NULL,
  monto_original DECIMAL(15,2) NOT NULL,
  moneda CHAR(3) NOT NULL DEFAULT 'PEN',
  vence_en DATE NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
  created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_cuentas_cobrar_documento (documento_electronico_id),
  KEY ix_cuentas_cobrar_estado_vencimiento (estado, vence_en),
  CONSTRAINT fk_cuentas_cobrar_documento FOREIGN KEY (documento_electronico_id) REFERENCES documentos_electronicos(id) ON DELETE RESTRICT,
  CONSTRAINT ck_cuentas_cobrar_monto CHECK (monto_original > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE cuentas_pagar (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  compra_id INT NOT NULL,
  monto_original DECIMAL(15,2) NOT NULL,
  moneda CHAR(3) NOT NULL DEFAULT 'PEN',
  vence_en DATE NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
  created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_cuentas_pagar_compra (compra_id),
  KEY ix_cuentas_pagar_estado_vencimiento (estado, vence_en),
  CONSTRAINT fk_cuentas_pagar_compra FOREIGN KEY (compra_id) REFERENCES compras(id) ON DELETE RESTRICT,
  CONSTRAINT ck_cuentas_pagar_monto CHECK (monto_original > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE aplicaciones_cobro (
  pago_id BIGINT UNSIGNED NOT NULL,
  cuenta_cobrar_id BIGINT UNSIGNED NOT NULL,
  importe_aplicado DECIMAL(15,2) NOT NULL,
  aplicado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (pago_id, cuenta_cobrar_id),
  KEY ix_aplicaciones_cobro_cuenta (cuenta_cobrar_id, aplicado_en),
  CONSTRAINT fk_aplicaciones_cobro_pago FOREIGN KEY (pago_id) REFERENCES pagos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_aplicaciones_cobro_cuenta FOREIGN KEY (cuenta_cobrar_id) REFERENCES cuentas_cobrar(id) ON DELETE RESTRICT,
  CONSTRAINT ck_aplicaciones_cobro_importe CHECK (importe_aplicado > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE aplicaciones_pago (
  pago_id BIGINT UNSIGNED NOT NULL,
  cuenta_pagar_id BIGINT UNSIGNED NOT NULL,
  importe_aplicado DECIMAL(15,2) NOT NULL,
  aplicado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (pago_id, cuenta_pagar_id),
  KEY ix_aplicaciones_pago_cuenta (cuenta_pagar_id, aplicado_en),
  CONSTRAINT fk_aplicaciones_pago_pago FOREIGN KEY (pago_id) REFERENCES pagos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_aplicaciones_pago_cuenta FOREIGN KEY (cuenta_pagar_id) REFERENCES cuentas_pagar(id) ON DELETE RESTRICT,
  CONSTRAINT ck_aplicaciones_pago_importe CHECK (importe_aplicado > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE plan_cuentas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  cuenta_padre_id BIGINT UNSIGNED NULL,
  codigo VARCHAR(40) NOT NULL,
  nombre VARCHAR(180) NOT NULL,
  tipo VARCHAR(30) NOT NULL,
  acepta_movimientos BOOLEAN NOT NULL DEFAULT TRUE,
  activa BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (id),
  UNIQUE KEY uq_plan_cuentas_empresa_codigo (empresa_id, codigo),
  KEY ix_plan_cuentas_padre (cuenta_padre_id),
  CONSTRAINT fk_plan_cuentas_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_plan_cuentas_padre FOREIGN KEY (cuenta_padre_id) REFERENCES plan_cuentas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE asientos_contables (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  sede_id BIGINT UNSIGNED NOT NULL,
  numero VARCHAR(60) NOT NULL,
  fecha_asiento DATE NOT NULL,
  descripcion VARCHAR(500) NOT NULL,
  estado VARCHAR(20) NOT NULL DEFAULT 'borrador',
  origen_modulo VARCHAR(80) NULL,
  origen_tipo VARCHAR(80) NULL,
  origen_id VARCHAR(100) NULL,
  usuario_id BIGINT UNSIGNED NULL,
  creado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_asientos_empresa_numero (empresa_id, numero),
  KEY ix_asientos_sede_fecha_estado (sede_id, fecha_asiento, estado),
  KEY ix_asientos_origen (origen_modulo, origen_tipo, origen_id),
  CONSTRAINT fk_asientos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_asientos_sede FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_asientos_usuario FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE asiento_lineas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  asiento_id BIGINT UNSIGNED NOT NULL,
  cuenta_id BIGINT UNSIGNED NOT NULL,
  descripcion VARCHAR(500) NULL,
  debe DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  haber DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  moneda CHAR(3) NOT NULL DEFAULT 'PEN',
  PRIMARY KEY (id),
  KEY ix_asiento_lineas_asiento (asiento_id),
  KEY ix_asiento_lineas_cuenta (cuenta_id, asiento_id),
  CONSTRAINT fk_asiento_lineas_asiento FOREIGN KEY (asiento_id) REFERENCES asientos_contables(id) ON DELETE RESTRICT,
  CONSTRAINT fk_asiento_lineas_cuenta FOREIGN KEY (cuenta_id) REFERENCES plan_cuentas(id) ON DELETE RESTRICT,
  CONSTRAINT ck_asiento_lineas_debe_haber CHECK (debe >= 0 AND haber >= 0 AND NOT (debe > 0 AND haber > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

    }

    /**
     * Ejecuta la operación down definida por la clase 2026_11_01_015107_create_finance_accounting_tables.
     *
     * @return void Resultado de la operación.
     */
    public function down(): void
    {
        throw new LogicException('Enterprise tables are forward-only to protect business data.');
    }
};
