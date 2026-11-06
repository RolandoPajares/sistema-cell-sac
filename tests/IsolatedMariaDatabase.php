<?php

namespace Tests;

use PDO;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Define la responsabilidad de la clase IsolatedMariaDatabase dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class IsolatedMariaDatabase
{
    /** @return array{0:PDO,1:string} */
    public static function createFresh(PDO $server, string $root, string $pipe): array
    {
        $temporaryDirectory = realpath(sys_get_temp_dir());
        $resolvedRoot = realpath($root);
        $identity = $server->query('SELECT @@version, @@datadir, @@skip_networking, @@socket, @@basedir')->fetch(PDO::FETCH_NUM);
        $baseDirectory = is_array($identity) ? realpath((string) $identity[4]) : false;
        if ($temporaryDirectory === false || $resolvedRoot === false || $baseDirectory === false
            || ! is_array($identity) || $identity[0] !== '10.4.32-MariaDB'
            || realpath((string) $identity[1]) !== realpath($resolvedRoot.DIRECTORY_SEPARATOR.'datos')
            || (int) $identity[2] !== 1 || $identity[3] !== $pipe
            || ! preg_match('/\Atc_ventas_[a-f0-9]{32}\z/', $pipe)
            || ! str_starts_with(strtolower($resolvedRoot), strtolower($temporaryDirectory.DIRECTORY_SEPARATOR.'technology_cell_ventas_pipe_'))
            || ! str_starts_with(strtolower($baseDirectory), strtolower($temporaryDirectory.DIRECTORY_SEPARATOR.'technology_cell_mariadb_10432_'))) {
            throw new RuntimeException('La importación de prueba requiere la instancia MariaDB descartable verificada.');
        }

        $client = $baseDirectory.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'mysql.exe';
        $reference = base_path('md_tecnologia_digital_cell.sql');
        if (! is_file($client) || ! is_file($reference)) {
            throw new RuntimeException('Falta el cliente MariaDB o el SQL de referencia del proyecto.');
        }

        $database = 'tc_ventas_laravel_qa'.bin2hex(random_bytes(8));
        $server->exec('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $sql = file_get_contents($reference);
        if ($sql === false) {
            throw new RuntimeException('No se pudo leer el SQL de referencia.');
        }
        $import = new Process([
            $client, '--no-defaults', '--protocol=pipe', '--socket='.$pipe, '-uroot',
            '--default-character-set=utf8mb4', '--database='.$database,
        ], base_path(), null, $sql, 180);
        $import->run();
        if (! $import->isSuccessful()) {
            throw new RuntimeException('Falló la importación aislada: '.$import->getErrorOutput());
        }

        $pdo = new PDO('mysql:host=.;port=1;unix_socket=\\\\.\\pipe\\'.$pipe.';dbname='.$database.';charset=utf8mb4', 'root', '', [PDO::ATTR_TIMEOUT => 1]);
        $tables = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '".$database."' AND TABLE_TYPE = 'BASE TABLE'")->fetchColumn();
        if ((int) $tables !== 97) {
            throw new RuntimeException('El SQL aislado no produjo las 97 tablas esperadas.');
        }

        return [$pdo, $database];
    }
}
