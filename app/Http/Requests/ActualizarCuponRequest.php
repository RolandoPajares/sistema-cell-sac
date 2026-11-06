<?php

namespace App\Http\Requests;

use App\Models\Comercio\Cupon;
use App\Servicios\ServicioAutorizacion;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase ActualizarCuponRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarCuponRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    public function rules(): array
    {
        return [
            'tipo_descuento' => ['required', Rule::in(['porcentaje', 'fijo'])],
            'valor' => ['required', 'numeric', 'gt:0', 'max:99999999.99', Rule::when($this->input('tipo_descuento') === 'porcentaje', ['lte:100'])],
            'minimo_compra' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'limite_usos' => [
                'nullable', 'integer', 'min:1', 'max:4294967295',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $coupon = $this->route('coupon');
                    if ($coupon instanceof Cupon && $value !== null && (int) $value < (int) $coupon->usos) {
                        $fail('El límite de usos no puede ser inferior a los canjes ya registrados.');
                    }
                },
            ],
            'inicia_en' => ['required', 'date'],
            'finaliza_en' => ['required', 'date', 'after:inicia_en'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'tipo_descuento' => 'tipo de descuento',
            'valor' => 'valor',
            'minimo_compra' => 'compra mínima',
            'limite_usos' => 'límite de usos',
            'inicia_en' => 'fecha inicial',
            'finaliza_en' => 'fecha final',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'tipo_descuento.in' => 'Selecciona un tipo de descuento válido.',
            'valor.lte' => 'El descuento porcentual no puede superar el 100 %.',
            'valor.max' => 'El valor supera el máximo permitido.',
            'minimo_compra.max' => 'La compra mínima supera el máximo permitido.',
            'limite_usos.max' => 'El límite de usos supera el máximo permitido.',
            'finaliza_en.after' => 'La fecha final debe ser posterior a la fecha inicial.',
        ];
    }
}
