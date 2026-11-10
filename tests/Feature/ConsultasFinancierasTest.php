<?php

use App\Http\Requests\Gestion\Finanzas\AnularOperacionFinancieraRequest;
use App\Http\Requests\Gestion\Finanzas\PagosCobros\FiltroPagosRequest;
use App\Http\Requests\Gestion\Finanzas\PagosCobros\RegistrarPagoRequest;
use App\Http\Requests\Gestion\Finanzas\Reembolsos\AprobarReembolsoRequest;
use App\Http\Requests\Gestion\Finanzas\Reembolsos\FiltroReembolsosRequest;
use App\Http\Requests\Gestion\Finanzas\Reembolsos\SolicitarReembolsoRequest;
use Illuminate\Support\Facades\Validator;

test('los filtros financieros validan periodo, estado y rangos de fecha', function (): void {
    $paymentRequest = new FiltroPagosRequest;
    $paymentValidator = Validator::make(
        ['tipo' => 'transferencia', 'desde' => '2026-11-03', 'hasta' => '2026-11-02'],
        $paymentRequest->rules(),
        $paymentRequest->messages(),
        $paymentRequest->attributes(),
    );
    $refundRequest = new FiltroReembolsosRequest;
    $refundValidator = Validator::make(
        ['buscar' => str_repeat('a', 121)],
        $refundRequest->rules(),
        $refundRequest->messages(),
        $refundRequest->attributes(),
    );

    expect($paymentValidator->fails())->toBeTrue()
        ->and($paymentValidator->errors()->first('tipo'))->toBe('Selecciona un tipo de pago válido.')
        ->and($paymentValidator->errors()->first('hasta'))->toBe('La fecha final debe ser igual o posterior a la fecha inicial.')
        ->and($refundValidator->fails())->toBeTrue()
        ->and($refundValidator->errors()->first('buscar'))->toBe('La búsqueda no puede superar 120 caracteres.');
});

test('el servidor rechaza solicitudes financieras mal formadas con mensajes claros', function (): void {
    $paymentRequest = new RegistrarPagoRequest;
    $paymentErrors = Validator::make(
        ['token' => 'repetido', 'origen' => 'factura:1', 'importe' => '12.345'],
        $paymentRequest->rules(), $paymentRequest->messages(),
    )->errors();
    $refundRequest = new SolicitarReembolsoRequest;
    $refundErrors = Validator::make(
        ['importe' => '0.001'],
        $refundRequest->rules(), $refundRequest->messages(),
    )->errors();
    $approvalRequest = new AprobarReembolsoRequest;
    $approvalErrors = Validator::make(
        ['confirmacion' => 'ejecutar'],
        $approvalRequest->rules(), $approvalRequest->messages(),
    )->errors();
    $cancellationRequest = new AnularOperacionFinancieraRequest;
    $cancellationErrors = Validator::make(
        ['motivo' => 'breve'],
        $cancellationRequest->rules(), $cancellationRequest->messages(),
    )->errors();

    expect($paymentErrors->first('token'))->toBe('El identificador de la solicitud no es válido.')
        ->and($paymentErrors->first('origen'))->toBe('El documento seleccionado no es válido.')
        ->and($paymentErrors->first('importe'))->toBe('Ingresa un importe válido con máximo dos decimales.')
        ->and($refundErrors->first('devolucion_id'))->toBe('Selecciona una devolución resuelta.')
        ->and($refundErrors->first('pago_id'))->toBe('Selecciona el cobro original.')
        ->and($approvalErrors->first('confirmacion'))->toBe('La confirmación no es válida.')
        ->and($cancellationErrors->first('motivo'))->toBe('El motivo debe tener al menos 10 caracteres.');
});
