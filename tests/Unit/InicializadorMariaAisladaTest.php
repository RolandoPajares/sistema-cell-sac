<?php

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\IsolatedMariaDatabase;
use Tests\TestCase;

uses(TestCase::class);

it('inicializa solo una instalacion MariaDB aislada y rechaza datos parciales', function (): void {
    $pipe = getenv('TECHNOLOGY_CELL_QA_PIPE');
    $root = getenv('TECHNOLOGY_CELL_QA_ROOT');
    $database = getenv('TECHNOLOGY_CELL_QA_DATABASE');
    if (! is_string($pipe) || ! is_string($root) || ! is_string($database)) {
        $this->markTestSkipped('Requiere la instancia MariaDB descartable identificada.');
    }

    $temp = realpath(sys_get_temp_dir());
    $resolvedRoot = realpath($root);
    if ($temp === false || $resolvedRoot === false
        || ! preg_match('/\Atc_ventas_[a-f0-9]{32}\z/', $pipe)
        || ! preg_match('/\Atc_ventas_laravel_[a-z0-9]+\z/', $database)
        || ! str_starts_with(strtolower($resolvedRoot), strtolower($temp.DIRECTORY_SEPARATOR.'technology_cell_ventas_pipe_'))) {
        throw new RuntimeException('La conexion indicada no es una instancia temporal autorizada.');
    }

    $pdo = new PDO('mysql:host=.;port=1;unix_socket=\\\\.\\pipe\\'.$pipe.';dbname='.$database, 'root', '', [PDO::ATTR_TIMEOUT => 1]);
    $identity = $pdo->query('SELECT @@version, @@datadir, @@skip_networking, @@socket')->fetch(PDO::FETCH_NUM);
    if ($identity[0] !== '10.4.32-MariaDB' || realpath($identity[1]) !== realpath($resolvedRoot.DIRECTORY_SEPARATOR.'datos')
        || (int) $identity[2] !== 1 || $identity[3] !== $pipe) {
        throw new RuntimeException('La identidad MariaDB no corresponde al directorio descartable.');
    }

    [$pdo, $database] = IsolatedMariaDatabase::createFresh($pdo, $resolvedRoot, $pipe);
    config(['database.default' => 'mysql', 'database.connections.mysql.database' => $database, 'database.connections.mysql.url' => null]);
    DB::purge('mysql');
    DB::connection('mysql')->setPdo($pdo)->setReadPdo($pdo);

    expect(DB::table('empresas')->count())->toBe(0)
        ->and(DB::table('usuarios')->count())->toBe(0);
    DB::beginTransaction();
    DB::table('empresas')->insert(['codigo' => 'PARCIAL', 'razon_social' => 'Registro parcial sintetico']);
    $this->artisan('technology-cell:inicializar', ['--confirmar' => true])->assertExitCode(1);
    expect(DB::table('sedes')->count())->toBe(0)
        ->and(DB::table('roles')->count())->toBe(0);
    DB::rollBack();

    $this->artisan('technology-cell:inicializar', ['--confirmar' => true])
        ->expectsQuestion('Código de empresa', 'EMPRESA_QA')
        ->expectsQuestion('Razón social', 'Empresa sintética QA')
        ->expectsQuestion('Código de sede', 'SEDE_QA')
        ->expectsQuestion('Nombre de sede', 'Sede sintética QA')
        ->expectsQuestion('Código de almacén', 'ALMACEN_QA')
        ->expectsQuestion('Nombre de almacén', 'Almacén sintético QA')
        ->expectsQuestion('Nombre del administrador inicial', 'Administrador QA')
        ->expectsQuestion('Correo del administrador inicial', 'admin-qa@example.test')
        ->expectsQuestion('Contraseña del administrador inicial', 'ClaveQAsegura123')
        ->expectsQuestion('Repite la contraseña', 'ClaveQAsegura123')
        ->expectsConfirmation('¿Crear estos registros en la base configurada?', 'yes')
        ->assertExitCode(0);

    $user = User::query()->where('correo', 'admin-qa@example.test')->firstOrFail();
    $company = DB::table('empresas')->sole();
    $site = DB::table('sedes')->sole();
    $warehouse = DB::table('almacenes')->sole();
    $role = DB::table('roles')->where('codigo', 'administrador')->sole();
    expect((int) $site->empresa_id)->toBe((int) $company->id)
        ->and((int) $warehouse->sede_id)->toBe((int) $site->id)
        ->and(Hash::check('ClaveQAsegura123', $user->resumen_contrasena))->toBeTrue()
        ->and(DB::table('usuarios_roles')->where('usuario_id', $user->id)->where('rol_id', $role->id)->count())->toBe(1)
        ->and(app(ServicioAutorizacion::class)->hasRole($user, 'administrador'))->toBeTrue()
        ->and(DB::table('registros_auditoria')->where('accion', 'instalacion.inicializada')->count())->toBe(1);

    $this->artisan('technology-cell:inicializar', ['--confirmar' => true])->assertExitCode(0);
    expect(DB::table('empresas')->count())->toBe(1)
        ->and(DB::table('usuarios')->count())->toBe(1)
        ->and(DB::table('versiones_inicializacion_control_acceso')->count())->toBe(2);
});
