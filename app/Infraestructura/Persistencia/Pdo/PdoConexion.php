<?php

namespace App\Infraestructura\Persistencia\Pdo;

use Closure;
use PDO;
use Throwable;

/**
 * Configura PDO y encapsula la ejecución de transacciones con commit y rollback.
 */
final class PdoConexion
{
    /**
     * Configura el PDO recibido con excepciones, fetch asociativo y prepares nativos.
     *
     * @param PDO $pdo Conexión PDO que utilizarán los DAOs.
     */
    public function __construct(private readonly PDO $pdo)
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * Ejecuta un callback y confirma o revierte la transacción que esta llamada inició.
     *
     * Si ya existe una transacción externa, conserva su control y solo propaga el
     * resultado o la excepción del callback.
     *
     * @param Closure $operacion Callback que recibe el PDO configurado.
     * @return mixed Resultado producido por el callback.
     * @throws Throwable La excepción original del callback cuando falla la operación.
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
     * Prepara una sentencia SQL usando la conexión PDO configurada.
     *
     * @param string $sql SQL con marcadores para parámetros enlazados.
     * @return \PDOStatement Sentencia preparada.
     */
    public function preparar(string $sql): \PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    /**
     * Devuelve el último identificador autoincremental generado por PDO.
     *
     * @return int Identificador convertido a entero.
     */
    public function ultimoId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }
}
