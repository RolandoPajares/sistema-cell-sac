@extends('layouts.administracion')

@section('title', 'Detalle de pago')
@section('body_class', 'modulo-pagos')

@section('content')
<section class="admin-finance-module admin-finance-module--pagos" data-finance-module="pagos">
    <header class="admin-finance-header">
        <nav class="admin-finance-breadcrumb" aria-label="Ruta de navegación">
            <a href="{{ route('management.payments') }}">Pagos y cobros</a>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <strong>{{ $record->codigo }}</strong>
        </nav>
        <div class="admin-finance-heading-row">
            <div class="admin-finance-heading">
                <span class="admin-finance-eyebrow"><i class="bi bi-credit-card" aria-hidden="true"></i> Finanzas</span>
                <h1>{{ $record->codigo }}</h1>
                <p>Estado registrado: {{ $record->estado }}. Una solicitud pendiente no confirma dinero recibido o enviado.</p>
            </div>
            <div class="admin-finance-actions"><a class="admin-finance-button admin-finance-button--secondary" href="{{ route('management.payments') }}">Volver al listado</a></div>
        </div>
    </header>
    @if (session('status')) <p class="alert alert-success" role="status">{{ session('status') }}</p> @endif
    @foreach ($errors->all() as $error) <p class="text-danger" role="alert">{{ $error }}</p> @endforeach
    <section class="admin-finance-table-card">
        <header class="admin-finance-card-header"><div><span class="admin-finance-card-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span><div><h2>Datos del registro</h2><p>Información guardada en pagos.</p></div></div></header>
        <div class="admin-finance-table-wrap">
            <table class="admin-finance-table">
                <tbody>
                    <tr><th scope="row">Tipo</th><td>{{ $record->tipo }}</td></tr>
                    <tr><th scope="row">Estado</th><td>{{ $record->estado }}</td></tr>
                    <tr><th scope="row">Importe</th><td>{{ $record->moneda }} {{ $record->importe }}</td></tr>
                    <tr><th scope="row">Medio</th><td>{{ $record->medioPago?->nombre ?? 'Sin medio asociado' }}</td></tr>
                    <tr><th scope="row">Sesión de caja</th><td>{{ $record->sesion_caja_id ? '#'.$record->sesion_caja_id : 'No aplica' }}</td></tr>
                    <tr><th scope="row">Cuenta bancaria</th><td>{{ $record->cuentaBancaria ? $record->cuentaBancaria->codigo.' · '.$record->cuentaBancaria->banco : 'No aplica' }}</td></tr>
                    <tr><th scope="row">Origen</th><td>{{ $record->venta?->codigo ?? ($record->pedido_id ? 'Pedido #'.$record->pedido_id : ($record->compra?->numero_referencia ?: ($record->compra_id ? 'Compra #'.$record->compra_id : 'Sin documento'))) }}</td></tr>
                    <tr><th scope="row">Referencia</th><td>{{ $record->referencia ?: 'Sin referencia' }}</td></tr>
                    <tr><th scope="row">Registrado por</th><td>{{ $record->usuario?->nombre ?? 'Usuario no disponible' }}</td></tr>
                    <tr><th scope="row">Fecha</th><td>{{ $record->creado_en?->format('d/m/Y H:i') }}</td></tr>
                </tbody>
            </table>
        </div>
    </section>
    @if ($record->estado === 'pendiente' && $record->medioPago?->requiere_banco)
        <p class="alert alert-warning" role="status">Transferencia declarada. La referencia debe verificarse y conciliarse administrativamente antes de confirmar dinero recibido o enviado.</p>
    @endif
    @if ($canConfirmCash)
        <section class="admin-finance-table-card">
            <header class="admin-finance-card-header"><div><h2>Verificación manual de efectivo</h2><p>La sesión debe permanecer abierta. Esta acción registrará una entrada o salida real de caja.</p></div></header>
            <form method="POST" action="{{ route('management.payments.cash.update', $record) }}" class="admin-finance-filters" onsubmit="return window.confirm('¿Confirmas que contaste este efectivo y deseas registrar el movimiento?')">
                @csrf @method('PATCH')
                <label>Importe contado (S/) *<input type="text" name="importe_contado" inputmode="decimal" value="{{ old('importe_contado') }}" required></label>
                <label>Observación de verificación *<input type="text" name="observacion" value="{{ old('observacion') }}" maxlength="500" required></label>
                <button class="admin-finance-button admin-finance-button--primary" type="submit">Confirmar efectivo contado</button>
            </form>
        </section>
    @endif
    @if ($canCancel)
        <section class="admin-finance-table-card">
            <header class="admin-finance-card-header"><div><h2>Anular solicitud pendiente</h2></div></header>
            <form method="POST" action="{{ route('management.payments.cancel', $record) }}" class="admin-finance-filters">
                @csrf
                @method('PATCH')
                <label>Motivo de anulación
                    <input type="text" name="motivo" value="{{ old('motivo') }}" minlength="10" maxlength="500" required>
                </label>
                <button class="admin-finance-button admin-finance-button--secondary" type="submit" onclick="return confirm('¿Anular esta solicitud pendiente?')">Anular solicitud</button>
            </form>
        </section>
    @endif
    <section class="admin-finance-table-card">
        <header class="admin-finance-card-header"><div><h2>Historial de auditoría</h2></div></header>
        <div class="admin-finance-table-wrap">
            <table class="admin-finance-table">
                <thead><tr><th>Fecha</th><th>Acción</th><th>Usuario</th></tr></thead>
                <tbody>
                    @forelse ($history as $event)
                        <tr><td>{{ $event->creado_en?->format('d/m/Y H:i') }}</td><td>{{ $event->accion }}</td><td>{{ $event->user?->nombre ?? 'Usuario no disponible' }}</td></tr>
                    @empty
                        <tr><td colspan="3">No hay eventos registrados para este pago.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</section>
@endsection
