<?php

namespace App\Infraestructura\Persistencia\Pdo;

use InvalidArgumentException;

/**
 * Normaliza filas PDO y limita la conversión a los campos declarados por mapa.
 */
final class PdoHidratador
{
    /**
     * @param  array<string, mixed>  $fila
     * @return array<string, mixed>
     */
    public function atributosDesdeFila(array $fila, PdoMapaEntidad $mapa): array
    {
        $atributos = [];
        foreach ($mapa->camposPersistibles as $propiedad => $columna) {
            $atributos[$propiedad] = $this->convertir($fila[$columna] ?? null, $mapa->tipos[$propiedad]);
        }

        return $atributos;
    }

    /**
     * @return array<string, mixed>
     */
    public function valoresDesdeEntidad(object $entidad, PdoMapaEntidad $mapa): array
    {
        $valores = [];
        foreach ($mapa->camposPersistibles as $propiedad => $columna) {
            if (! property_exists($entidad, $propiedad)) {
                throw new InvalidArgumentException("La entidad no expone el campo {$propiedad}.");
            }

            $valores[$columna] = $this->convertir(get_object_vars($entidad)[$propiedad] ?? null, $mapa->tipos[$propiedad]);
        }

        return $valores;
    }

    /**
     * @return array<string, mixed>
     */
    public function valoresSinClave(object $entidad, PdoMapaEntidad $mapa): array
    {
        $valores = $this->valoresDesdeEntidad($entidad, $mapa);
        unset($valores[$mapa->clavePrimaria]);

        return $valores;
    }

    private function convertir(mixed $valor, string $tipo): mixed
    {
        if ($valor === null) {
            return null;
        }

        return match ($tipo) {
            'int' => (int) $valor,
            'bool' => (bool) $valor,
            'string', 'decimal' => (string) $valor,
            default => throw new InvalidArgumentException("Tipo PDO no permitido: {$tipo}."),
        };
    }
}
