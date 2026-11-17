<?php

namespace App\Servicios\Gestion\Configuracion;

use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * Define la responsabilidad de la clase ServicioRespaldos dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ServicioRespaldos
{
    /** @return list<array{nombre:string,tamano:int,modificado:int}> */
    public function listar(): array
    {
        // Obtiene los archivos de respaldo existentes en el directorio configurado.
        // Guarda en $directorio el resultado necesario para continuar la operacion.
        $directorio = $this->directorio();
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! File::isDirectory($directorio)) {
            return [];
        }

        return collect(File::files($directorio))
            ->filter(fn ($archivo): bool => $archivo->getExtension() === 'sql')
            ->sortByDesc(fn ($archivo): int => $archivo->getMTime())
            ->map(fn ($archivo): array => [
                'nombre' => $archivo->getFilename(),
                'tamano' => $archivo->getSize(),
                'modificado' => $archivo->getMTime(),
            ])->values()->all();
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return array Resultado de la operación.
     */
    public function crear(User $usuario, ?string $ip, RegistradorAuditoria $auditoria): array
    {
        // Genera un respaldo de la base y registra la operacion en auditoria.
        // Guarda en $conexion el resultado necesario para continuar la operacion.
        $conexion = config('database.connections.mysql');
        // Guarda en $base el resultado necesario para continuar la operacion.
        $base = (string) ($conexion['database'] ?? '');
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($base !== 'md_tecnologia_digital_cell') {
            throw new \RuntimeException('El respaldo solo está permitido para md_tecnologia_digital_cell.');
        }

        // Guarda en $directorio el resultado necesario para continuar la operacion.
        $directorio = $this->directorio();
        File::ensureDirectoryExists($directorio);
        // Guarda en $nombre el resultado necesario para continuar la operacion.
        $nombre = 'technology-cell_'.now()->format('Ymd_His').'_'.Str::lower(Str::random(6)).'.sql';
        // Guarda en $destino el resultado necesario para continuar la operacion.
        $destino = $directorio.DIRECTORY_SEPARATOR.$nombre;
        // Guarda en $binario el resultado necesario para continuar la operacion.
        $binario = $this->binario();
        // Guarda en $password el resultado necesario para continuar la operacion.
        $password = (string) ($conexion['password'] ?? '');
        // Guarda en $process el resultado necesario para continuar la operacion.
        $process = new Process([
            $binario,
            '--host='.(string) ($conexion['host'] ?? '127.0.0.1'),
            '--port='.(string) ($conexion['port'] ?? '3306'),
            '--user='.(string) ($conexion['username'] ?? ''),
            '--single-transaction',
            '--routines',
            '--events',
            '--databases',
            $base,
            '--result-file='.$destino,
        ], base_path(), ['MYSQL_PWD' => $password]);
        $process->setTimeout(180);
        $process->run();

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! $process->isSuccessful() || ! File::exists($destino) || File::size($destino) < 32) {
            File::delete($destino);
            throw new \RuntimeException('No fue posible generar el respaldo MariaDB: '.trim($process->getErrorOutput() ?: $process->getOutput()));
        }

        $auditoria->record(
            $usuario->getKey(),
            'respaldo.creado',
            'respaldos',
            null,
            null,
            ['nombre' => $nombre, 'tamano' => File::size($destino), 'base' => $base],
            $ip,
            scope: ['empresa_id' => null, 'sede_id' => null],
        );

        return ['nombre' => $nombre, 'tamano' => File::size($destino)];
    }

    /**
     * Ejecuta la operación rutaSegura definida por la clase ServicioRespaldos.
     *
     * @param  mixed  $nombre  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    public function rutaSegura(string $nombre): string
    {
        // Valida el nombre recibido y evita salir del directorio permitido.
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(preg_match('/^[A-Za-z0-9_.-]+\.sql$/', $nombre) === 1, 404);
        // Guarda en $ruta el resultado necesario para continuar la operacion.
        $ruta = $this->directorio().DIRECTORY_SEPARATOR.$nombre;
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(File::isFile($ruta), 404);

        return $ruta;
    }

    /**
     * Ejecuta la operación directorio definida por la clase ServicioRespaldos.
     *
     * @return string Resultado de la operación.
     */
    private function directorio(): string
    {
        // Resuelve la carpeta donde se almacenan los respaldos.
        return storage_path('app/private/respaldos');
    }

    /**
     * Ejecuta la operación binario definida por la clase ServicioRespaldos.
     *
     * @return string Resultado de la operación.
     */
    private function binario(): string
    {
        // Busca el ejecutable mysqldump configurado o disponible en el sistema.
        // Agrupa en $candidatos los datos que se utilizaran en el siguiente paso.
        $candidatos = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'mysqldump',
        ];
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($candidatos as $candidato) {
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($candidato === 'mysqldump' || File::exists($candidato)) {
                return $candidato;
            }
        }

        throw new \RuntimeException('No se encontró mysqldump en el entorno autorizado.');
    }
}
