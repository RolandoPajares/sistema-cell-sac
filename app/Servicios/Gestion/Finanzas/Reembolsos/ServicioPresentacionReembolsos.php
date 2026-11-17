<?php

namespace App\Servicios\Gestion\Finanzas\Reembolsos;

final class ServicioPresentacionReembolsos
{
    /**
     * @param  array<string, mixed>  $module
     * @return array<string, mixed>
     */
    public function preparar(array $module): array
    {
        // Organiza los datos del modulo de reembolsos para su presentacion.
        // Guarda en $formOptions el resultado necesario para continuar la operacion.
        $formOptions = $module['form_options'] ?? [];
        // Guarda en $moduleKey el resultado necesario para continuar la operacion.
        $moduleKey = (string) ($module['key'] ?? 'reembolsos');

        $module['module_key'] = $moduleKey;
        $module['filter_options'] = $module['filter_options'] ?? ['types' => [], 'statuses' => []];
        $module['filter_values'] = $module['filters'] ?? [];
        $module['export_parameters'] = collect($module['filter_values'])
            ->filter(static fn (mixed $value): bool => $value !== null && $value !== '')
            ->all();
        $module['form_options'] = $formOptions;
        $module['can_submit'] = $moduleKey === 'pagos'
            ? ($formOptions['documentos'] ?? collect())->isNotEmpty()
                && ($formOptions['medios'] ?? collect())->isNotEmpty()
            : ($formOptions['devoluciones'] ?? collect())->isNotEmpty()
                && ($formOptions['cobros'] ?? collect())->isNotEmpty();

        return $module;
    }
}
