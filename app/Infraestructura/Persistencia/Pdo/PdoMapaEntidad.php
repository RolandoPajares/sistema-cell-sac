<?php

namespace App\Infraestructura\Persistencia\Pdo;

use InvalidArgumentException;

/**
 * Describe los campos que una entidad PDO puede leer y persistir.
 */
final readonly class PdoMapaEntidad
{
    /**
     * @param  array<string, string>  $camposPersistibles  alias de propiedad a columna.
     * @param  array<string, string>  $tipos  tipos permitidos: int, bool, string o decimal.
     */
    public function __construct(
        public string $tabla,
        public string $clavePrimaria,
        public array $camposPersistibles,
        public array $tipos,
    ) {
        if ($this->tabla === '' || $this->clavePrimaria === '' || $this->camposPersistibles === []) {
            throw new InvalidArgumentException('El mapa PDO requiere tabla, clave primaria y campos.');
        }

        foreach ([$this->tabla, $this->clavePrimaria] as $identificador) {
            if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identificador)) {
                throw new InvalidArgumentException("Identificador no permitido en el mapa PDO: {$identificador}.");
            }
        }

        foreach ($this->camposPersistibles as $propiedad => $columna) {
            if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $propiedad)) {
                throw new InvalidArgumentException("Propiedad no permitida en el mapa PDO: {$propiedad}.");
            }

            if (! isset($this->tipos[$propiedad])) {
                throw new InvalidArgumentException("Falta el tipo PDO para {$propiedad}.");
            }

            if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $columna)) {
                throw new InvalidArgumentException("Columna no permitida en el mapa PDO: {$columna}.");
            }
        }
    }

    /** @return list<string> */
    public function columnas(): array
    {
        return array_values($this->camposPersistibles);
    }

    public function columna(string $propiedad): string
    {
        if (! isset($this->camposPersistibles[$propiedad])) {
            throw new InvalidArgumentException("Campo no permitido: {$propiedad}.");
        }

        return $this->camposPersistibles[$propiedad];
    }
}
