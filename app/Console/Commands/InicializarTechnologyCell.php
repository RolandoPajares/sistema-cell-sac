<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use Database\Seeders\RbacInicialSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Throwable;

#[Signature('technology-cell:inicializar {--confirmar : Autoriza la creación inicial de empresa y administrador} {--vista-previa : Describe las operaciones sin escribir datos}')]
#[Description('Inicializa una instalación vacía sin modificar cuentas existentes')]
/**
 * Define la responsabilidad de la clase InicializarTechnologyCell dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class InicializarTechnologyCell extends Command
{
    private const VERSION = 'instalacion-inicial-a25';

    /**
     * Ejecuta la operación principal asignada al componente.
     *
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return int Resultado de la operación.
     */
    public function handle(RegistradorAuditoria $audit): int
    {
        if ($this->option('vista-previa')) {
            $this->mostrarPlan();
            $this->mostrarConciliacion();

            return self::SUCCESS;
        }

        if (! $this->option('confirmar')) {
            $this->error('La inicialización requiere autorización explícita mediante --confirmar.');

            return self::FAILURE;
        }

        foreach (['usuarios', 'empresas', 'sedes', 'almacenes', 'roles', 'usuarios_roles', 'registros_auditoria', 'versiones_inicializacion_control_acceso'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->error('Falta una tabla requerida: '.$table);

                return self::FAILURE;
            }
        }

        if (DB::table('versiones_inicializacion_control_acceso')->where('codigo', self::VERSION)->exists()) {
            $this->info('La instalación inicial ya fue registrada; no se modificó ningún dato.');

            return self::SUCCESS;
        }

        foreach (['usuarios', 'empresas', 'sedes', 'almacenes'] as $table) {
            if (DB::table($table)->exists()) {
                $this->error('La instalación contiene datos previos y requiere conciliación: '.$table);

                return self::FAILURE;
            }
        }

        $data = [
            'empresa_codigo' => $this->ask('Código de empresa'),
            'razon_social' => $this->ask('Razón social'),
            'sede_codigo' => $this->ask('Código de sede'),
            'sede_nombre' => $this->ask('Nombre de sede'),
            'almacen_codigo' => $this->ask('Código de almacén'),
            'almacen_nombre' => $this->ask('Nombre de almacén'),
            'nombre' => $this->ask('Nombre del administrador inicial'),
            'correo' => $this->ask('Correo del administrador inicial'),
            'password' => $this->secret('Contraseña del administrador inicial'),
            'password_confirmation' => $this->secret('Repite la contraseña'),
        ];
        $validator = Validator::make($data, [
            'empresa_codigo' => ['required', 'string', 'max:30', 'regex:/\A[a-zA-Z0-9_-]+\z/'],
            'razon_social' => ['required', 'string', 'max:180'],
            'sede_codigo' => ['required', 'string', 'max:30', 'regex:/\A[a-zA-Z0-9_-]+\z/'],
            'sede_nombre' => ['required', 'string', 'max:140'],
            'almacen_codigo' => ['required', 'string', 'max:30', 'regex:/\A[a-zA-Z0-9_-]+\z/'],
            'almacen_nombre' => ['required', 'string', 'max:140'],
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email', 'max:160'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'max' => ['string' => 'El campo :attribute no puede superar :max caracteres.'],
            'min' => ['string' => 'La contraseña debe tener al menos :min caracteres.'],
            'regex' => 'El formato de :attribute no es válido.',
            'email' => 'El correo electrónico no tiene un formato válido.',
            'confirmed' => 'La confirmación de :attribute no coincide.',
            'password.letters' => 'La contraseña debe incluir al menos una letra.',
            'password.numbers' => 'La contraseña debe incluir al menos un número.',
        ], [
            'empresa_codigo' => 'código de empresa',
            'razon_social' => 'razón social',
            'sede_codigo' => 'código de sede',
            'sede_nombre' => 'nombre de sede',
            'almacen_codigo' => 'código de almacén',
            'almacen_nombre' => 'nombre de almacén',
            'nombre' => 'nombre del administrador',
            'correo' => 'correo del administrador',
            'password' => 'contraseña del administrador',
        ]);
        if ($validator->fails()) {
            Log::warning('Inicialización empresarial: validación rechazada.', [
                'campos' => array_keys($validator->errors()->messages()),
            ]);
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->mostrarPlan();
        $this->line('Base configurada: '.DB::connection()->getDatabaseName());
        $this->line('Empresa: '.$data['razon_social'].' ('.$data['empresa_codigo'].')');
        $this->line('Sede: '.$data['sede_nombre'].'; almacén: '.$data['almacen_nombre']);
        $this->line('Administrador inicial: '.$data['nombre'].' ('.$data['correo'].')');
        if (! $this->confirm('¿Crear estos registros en la base configurada?', false)) {
            $this->info('Inicialización cancelada; no se escribieron datos.');
            Log::info('Inicialización empresarial: confirmación rechazada.');

            return self::FAILURE;
        }

        $etapa = 'preparación';
        try {
            DB::transaction(function () use ($data, $audit, &$etapa): void {
                $etapa = 'control de ejecución duplicada';
                $inserted = DB::table('versiones_inicializacion_control_acceso')->insertOrIgnore([
                    'codigo' => self::VERSION,
                    'aplicado_en' => now(),
                ]);
                if ($inserted !== 1) {
                    return;
                }

                foreach (['usuarios', 'empresas', 'sedes', 'almacenes'] as $table) {
                    if (DB::table($table)->exists()) {
                        throw new \RuntimeException('La instalación dejó de estar vacía.');
                    }
                }

                $etapa = 'catálogos RBAC';
                app(RbacInicialSeeder::class)->run();
                $roleId = DB::table('roles')->where('codigo', 'administrador')->where('alcance_permitido', 'empresa')
                    ->where('es_sistema', true)->where('activo', true)->value('id');
                if ($roleId === null) {
                    throw new \RuntimeException('El rol administrador no está disponible.');
                }

                $etapa = 'empresa';
                $companyId = DB::table('empresas')->insertGetId([
                    'codigo' => $data['empresa_codigo'], 'razon_social' => $data['razon_social'],
                    'activo' => true, 'creado_en' => now(), 'actualizado_en' => now(),
                ]);
                $etapa = 'sede';
                $siteId = DB::table('sedes')->insertGetId([
                    'empresa_id' => $companyId, 'codigo' => $data['sede_codigo'], 'nombre' => $data['sede_nombre'],
                    'activa' => true, 'creado_en' => now(), 'actualizado_en' => now(),
                ]);
                $etapa = 'almacén';
                $warehouseId = DB::table('almacenes')->insertGetId([
                    'sede_id' => $siteId, 'codigo' => $data['almacen_codigo'], 'nombre' => $data['almacen_nombre'],
                    'activo' => true, 'creado_en' => now(), 'actualizado_en' => now(),
                ]);
                $etapa = 'administrador';
                $user = User::create([
                    'nombre' => $data['nombre'], 'correo' => mb_strtolower(trim($data['correo'])),
                    'password' => $data['password'], 'rol' => 'administrador', 'acceso_controlado' => true,
                ]);
                $etapa = 'asignación RBAC';
                DB::table('usuarios_roles')->insert([
                    'usuario_id' => $user->getKey(), 'rol_id' => $roleId, 'asignado_en' => now(), 'activo' => true,
                ]);
                $etapa = 'auditoría';
                $audit->record($user->getKey(), 'instalacion.inicializada', 'empresas', $companyId, null, [
                    'empresa_id' => $companyId, 'sede_id' => $siteId, 'almacen_id' => $warehouseId,
                ], null, scope: ['empresa_id' => $companyId, 'sede_id' => $siteId]);
            }, 3);
        } catch (Throwable $exception) {
            $codigoMariaDb = $exception instanceof QueryException ? ($exception->errorInfo[1] ?? null) : null;
            $diagnostico = [
                'etapa' => $etapa,
                'tipo' => class_basename($exception),
                'codigo' => (string) $exception->getCode(),
                'codigo_mariadb' => $codigoMariaDb,
            ];
            Log::error('Inicialización empresarial revertida.', $diagnostico);
            $this->error('No se completó la inicialización. Se revirtió la transacción.');
            $this->error('Etapa: '.$etapa.'; tipo: '.$diagnostico['tipo'].'; código: '.($diagnostico['codigo'] ?: 'sin código')
                .($codigoMariaDb === null ? '' : '; MariaDB: '.$codigoMariaDb).'.');

            return self::FAILURE;
        }

        $this->info('Instalación inicial completada. No se modificaron cuentas existentes.');

        return self::SUCCESS;
    }

    /**
     * Ejecuta la operación mostrarPlan definida por la clase InicializarTechnologyCell.
     *
     * @return void Resultado de la operación.
     */
    private function mostrarPlan(): void
    {
        $this->line('Vista previa: crear los catálogos RBAC iniciales, una empresa, una sede, un almacén y un administrador.');
        $this->line('Solo se asignará el rol administrador a la cuenta nueva; se registrará auditoría y no se modificarán cuentas existentes.');
        $this->line('Datos requeridos: códigos y nombres de empresa, sede y almacén; nombre, correo y contraseña del administrador.');
    }

    /**
     * Ejecuta la operación mostrarConciliacion definida por la clase InicializarTechnologyCell.
     *
     * @return void Resultado de la operación.
     */
    private function mostrarConciliacion(): void
    {
        $this->line('Base configurada: '.DB::connection()->getDatabaseName());
        if (DB::getDriverName() === 'mysql') {
            $schema = DB::connection()->getDatabaseName();
            $objects = DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', $schema)
                ->selectRaw('TABLE_TYPE, COUNT(*) AS cantidad')->groupBy('TABLE_TYPE')->pluck('cantidad', 'TABLE_TYPE');
            $triggers = DB::table('information_schema.TRIGGERS')->where('TRIGGER_SCHEMA', $schema)->count();
            $this->line('Estructura: '.$objects->get('BASE TABLE', 0).' tablas, '.$objects->get('VIEW', 0).' vistas, '.$triggers.' disparadores.');
        }

        foreach (['empresas', 'sedes', 'almacenes', 'usuarios', 'roles', 'permisos', 'usuarios_roles', 'usuarios_sedes_roles', 'versiones_inicializacion_control_acceso', 'migraciones_laravel', 'migrations'] as $table) {
            $this->line($table.': '.(Schema::hasTable($table) ? DB::table($table)->count().' registro(s)' : 'tabla ausente'));
        }

        if (Schema::hasTable('usuarios_roles') && Schema::hasTable('roles') && Schema::hasTable('usuarios')) {
            $administrators = DB::table('usuarios_roles')->join('roles', 'roles.id', '=', 'usuarios_roles.rol_id')
                ->join('usuarios', 'usuarios.id', '=', 'usuarios_roles.usuario_id')
                ->where('roles.codigo', 'administrador')->where('roles.activo', true)
                ->where('usuarios_roles.activo', true)->where('usuarios.activo', true)
                ->where(function ($query): void {
                    $query->whereNull('usuarios_roles.vigente_hasta')->orWhere('usuarios_roles.vigente_hasta', '>', now());
                })->count();
            $this->line('Administradores con asignación RBAC vigente: '.$administrators);
        }

        foreach ([
            'ventas' => ['canal', 'observaciones'],
            'detalles_transferencias_existencias' => ['cantidad_devuelta', 'cantidad_conciliada'],
        ] as $table => $columns) {
            foreach ($columns as $column) {
                $this->line($table.'.'.$column.': '.(Schema::hasColumn($table, $column) ? 'presente' : 'pendiente'));
            }
        }

        $this->line('La vista previa no crea ni modifica registros. Una instalación parcial requiere conciliación antes de inicializar.');
    }
}
