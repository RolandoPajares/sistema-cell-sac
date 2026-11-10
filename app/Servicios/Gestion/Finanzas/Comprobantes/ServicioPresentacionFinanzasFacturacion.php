<?php

namespace App\Servicios\Gestion\Finanzas\Comprobantes;

final class ServicioPresentacionFinanzasFacturacion
{
    /**
     * @param  array<string, mixed>  $pagina
     * @return array<string, mixed>
     */
    public function preparar(array $pagina): array
    {
        $clave = (string) ($pagina['clave'] ?? '');
        $datosReales = (bool) ($pagina['datosReales'] ?? false);

        $pagina['acciones'] = collect($pagina['acciones'] ?? [])
            ->values()
            ->map(function (array $accion, int $indice) use ($clave, $datosReales): array {
                $identificador = $clave.'-'.$indice;

                $accion['ruta_real'] = $datosReales ? null : match ($identificador) {
                    'pagos-0' => 'pago',
                    'pagos-1' => 'cobro',
                    'reembolsos-0' => 'reembolso',
                    default => null,
                };
                $accion['modal'] = $datosReales ? null : match ($identificador) {
                    'cajas-0' => 'arqueos',
                    'cajas-1' => 'nueva-caja',
                    'aperturas-0' => 'apertura',
                    'aperturas-1' => 'cierre',
                    'cuentas-0' => 'obligacion',
                    'cuentas-1' => 'abono',
                    'reembolsos-1' => 'politicas',
                    'notas-0' => 'nota',
                    'notas-1' => 'validacion-documento',
                    'series-0' => 'nueva-serie',
                    'series-1' => 'correlativo',
                    'sunat-0' => 'sunat-reenviar',
                    'sunat-1' => 'sunat-cdr',
                    default => null,
                };

                return $accion;
            })
            ->all();

        $pagina['filas'] = collect($pagina['filas'] ?? [])
            ->map(function (array $fila) use ($clave, $datosReales): array {
                $estado = (string) ($fila['filtros']['estado'] ?? '');
                $fila['accion'] = $datosReales ? '' : match ($clave) {
                    'cajas' => 'editar-caja',
                    'aperturas' => $estado === 'Abierto' ? 'cierre-caja' : 'arqueo',
                    'cuentas' => 'abono',
                    'reembolsos' => match ($estado) {
                        'Pendiente' => 'revisar-reembolso',
                        'Aprobado' => 'ejecutar-reembolso',
                        default => '',
                    },
                    'notas' => in_array($estado, ['Pendiente', 'Observada'], true) ? 'validacion-documento' : '',
                    'series' => $estado === 'Activa' ? 'cambio-serie' : '',
                    'sunat' => match ($estado) {
                        'Aceptado' => 'sunat-cdr',
                        'Rechazado', 'Observado' => 'sunat-reenviar',
                        default => '',
                    },
                    default => '',
                };

                return $fila;
            })
            ->all();

        $filas = collect($pagina['filas']);
        $pagina['cuentasPorPagar'] = $filas->where('tipo_cuenta', 'Por pagar')->values();
        $pagina['cuentasPorCobrar'] = $filas->where('tipo_cuenta', 'Por cobrar')->values();
        $pagina['tienePagosPorCuenta'] = $this->tieneRegistros($pagina['pagosPorCuenta'] ?? []);
        $pagina['tienePagosCobroPorCuenta'] = $this->tieneRegistros($pagina['pagosCobroPorCuenta'] ?? []);

        return $pagina;
    }

    /** @param array<int|string, mixed> $registros */
    private function tieneRegistros(array $registros): bool
    {
        return collect($registros)->flatten(1)->isNotEmpty();
    }
}
