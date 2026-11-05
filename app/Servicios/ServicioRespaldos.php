<?php

namespace App\Servicios;

use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

final class ServicioRespaldos
{
    /** @return list<array{nombre:string,tamano:int,modificado:int}> */
    public function listar(): array
    {
        $directorio = $this->directorio();
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

    public function crear(User $usuario, ?string $ip, RegistradorAuditoria $auditoria): array
    {
        $conexion = config('database.connections.mysql');
        $base = (string) ($conexion['database'] ?? '');
        if ($base !== 'md_tecnologia_digital_cell') {
            throw new \RuntimeException('El respaldo solo está permitido para md_tecnologia_digital_cell.');
        }

        $directorio = $this->directorio();
        File::ensureDirectoryExists($directorio);
        $nombre = 'technology-cell_'.now()->format('Ymd_His').'_'.Str::lower(Str::random(6)).'.sql';
        $destino = $directorio.DIRECTORY_SEPARATOR.$nombre;
        $binario = $this->binario();
        $password = (string) ($conexion['password'] ?? '');
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

    public function rutaSegura(string $nombre): string
    {
        abort_unless(preg_match('/^[A-Za-z0-9_.-]+\.sql$/', $nombre) === 1, 404);
        $ruta = $this->directorio().DIRECTORY_SEPARATOR.$nombre;
        abort_unless(File::isFile($ruta), 404);

        return $ruta;
    }

    private function directorio(): string
    {
        return storage_path('app/private/respaldos');
    }

    private function binario(): string
    {
        $candidatos = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'mysqldump',
        ];
        foreach ($candidatos as $candidato) {
            if ($candidato === 'mysqldump' || File::exists($candidato)) {
                return $candidato;
            }
        }

        throw new \RuntimeException('No se encontró mysqldump en el entorno autorizado.');
    }
}
