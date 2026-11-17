<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class VerificarPreparacionProduccion extends Command
{
    protected $signature = 'technology:preflight {--strict : Devuelve código de error si existe alguna advertencia}';

    protected $description = 'Verifica requisitos operativos sin modificar la aplicación ni la base de datos.';

    public function handle(): int
    {
        $checks = [];
        $checks['APP_KEY configurada'] = filled(config('app.key'));
        $checks['APP_DEBUG desactivado'] = config('app.debug') === false;
        $checks['Extensión PDO MySQL'] = extension_loaded('pdo_mysql');
        $checks['Extensión mbstring'] = extension_loaded('mbstring');
        $checks['Extensión fileinfo'] = extension_loaded('fileinfo');
        $checks['Extensión openssl'] = extension_loaded('openssl');
        $checks['Extensión ZIP'] = extension_loaded('zip');
        $checks['Assets compilados'] = is_file(public_path('build/manifest.json'));
        $checks['Enlace public/storage'] = is_dir(public_path('storage'));
        $checks['Storage framework escribible'] = is_writable(storage_path('framework'));
        $checks['Cache escribible'] = is_writable(storage_path('framework/cache'));
        $checks['Logs escribibles'] = is_writable(storage_path('logs'));

        try {
            DB::select('select 1 as ok');
            $checks['Conexión de base de datos'] = true;
            foreach (['usuarios', 'productos', 'categorias', 'existencias_almacen', 'mensajes_contacto'] as $table) {
                $checks["Tabla $table"] = Schema::hasTable($table);
            }
        } catch (Throwable $exception) {
            $checks['Conexión de base de datos'] = false;
            $this->line('<fg=yellow>DB: '.$exception->getMessage().'</>');
        }

        $warnings = 0;
        foreach ($checks as $label => $passed) {
            if ($passed) {
                $this->components->twoColumnDetail($label, '<fg=green>OK</>');

                continue;
            }

            $warnings++;
            $this->components->twoColumnDetail($label, '<fg=yellow>REVISAR</>');
        }

        $this->newLine();
        $this->line($warnings === 0
            ? '<fg=green>Preflight sin observaciones.</>'
            : "<fg=yellow>$warnings observación(es) requieren revisión.</>");

        return $this->option('strict') && $warnings > 0 ? self::FAILURE : self::SUCCESS;
    }
}
