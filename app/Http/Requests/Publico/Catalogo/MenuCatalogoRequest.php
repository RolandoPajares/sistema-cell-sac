<?php

namespace App\Http\Requests\Publico\Catalogo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MenuCatalogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string', 'max:120'],
            'categoria' => ['sometimes', 'nullable', 'integer', Rule::exists('categorias', 'id')->where(fn ($query) => $query->where('activo', true))],
            'marca' => ['sometimes', 'nullable', 'array', 'max:20'],
            'marca.*' => ['string', 'max:80'],
            'atributo' => ['sometimes', 'nullable', 'integer', 'exists:atributos,id'],
            'atributos' => ['sometimes', 'array', 'max:10'],
            'atributos.*' => ['array', 'max:20'],
            'atributos.*.*' => ['integer', 'distinct'],
            'orden' => ['sometimes', 'nullable', Rule::in(['precio_asc', 'precio_desc', 'nuevo'])],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty() || ! Schema::hasTable('atributo_valores')) {
                return;
            }

            foreach ((array) $this->input('atributos', []) as $atributoId => $valores) {
                $ids = collect((array) $valores)->map(fn (mixed $valor): int => (int) $valor)->filter()->unique();
                $validos = DB::table('atributo_valores')->where('atributo_id', (int) $atributoId)
                    ->where('activo', true)->whereIn('id', $ids)->count();

                if ($validos !== $ids->count()) {
                    $validator->errors()->add('atributos', 'Uno o más valores no pertenecen al atributo seleccionado.');

                    return;
                }
            }
        }];
    }
}
