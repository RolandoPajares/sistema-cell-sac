<?php

namespace App\Infraestructura\Persistencia\Pdo;

use Closure;
use PDO;
use Throwable;

/**
 * Define la responsabilidad de la clase PdoConexion dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class PdoConexion
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $pdo  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly PDO $pdo)
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * Ejecuta la operación principal asignada al componente.
     *
     * @param  mixed  $operacion  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function ejecutarEnTransaccion(Closure $operacion): mixed
    {
        $iniciadaAqui = ! $this->pdo->inTransaction();
        if ($iniciadaAqui) {
            $this->pdo->beginTransaction();
        }

        try {
            $resultado = $operacion($this->pdo);
            if ($iniciadaAqui) {
                $this->pdo->commit();
            }

            return $resultado;
        } catch (Throwable $exception) {
            if ($iniciadaAqui && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Ejecuta la operación preparar definida por la clase PdoConexion.
     *
     * @param  mixed  $sql  Parámetro recibido por la operación.
     * @return \PDOStatement Resultado de la operación.
     */
    public function preparar(string $sql): \PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    /**
     * Ejecuta la operación ultimoId definida por la clase PdoConexion.
     *
     * @return int Resultado de la operación.
     */
    public function ultimoId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }
}
