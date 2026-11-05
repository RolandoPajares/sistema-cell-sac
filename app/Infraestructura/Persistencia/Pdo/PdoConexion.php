<?php

namespace App\Infraestructura\Persistencia\Pdo;

use Closure;
use PDO;
use Throwable;

final class PdoConexion
{
    public function __construct(private readonly PDO $pdo)
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

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

    public function preparar(string $sql): \PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    public function ultimoId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }
}
