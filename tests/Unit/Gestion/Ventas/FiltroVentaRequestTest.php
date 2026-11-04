<?php

use App\Http\Requests\FiltroVentaRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class);

it('rejects invalid sales filter dates, channels and identifiers with Spanish messages', function (): void {
    $request = new FiltroVentaRequest;
    $input = [
        'buscar' => str_repeat('a', 121), 'canal' => 'marketplace', 'cliente_id' => 0,
        'desde' => '2026-11-20', 'hasta' => '2026-11-03',
    ];
    $validator = Validator::make($input, $request->rules(), $request->messages(), $request->attributes());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('buscar'))->toBe('La búsqueda no puede superar 120 caracteres.')
        ->and($validator->errors()->first('canal'))->toBe('Selecciona un canal válido.')
        ->and($validator->errors()->first('cliente_id'))->toBe('Selecciona un cliente válido.')
        ->and($validator->errors()->first('hasta'))->toBe('La fecha final debe ser igual o posterior a la fecha inicial.');
});

it('accepts the public sales filters used by both the list and its export', function (): void {
    $request = new FiltroVentaRequest;
    $input = [
        'buscar' => 'V-100', 'estado' => 'confirmada', 'canal' => 'presencial',
        'cliente_id' => '4', 'sede_id' => '2', 'desde' => '2026-11-01', 'hasta' => '2026-11-03',
    ];
    $validator = Validator::make($input, $request->rules(), $request->messages(), $request->attributes());

    expect($validator->passes())->toBeTrue();
});
