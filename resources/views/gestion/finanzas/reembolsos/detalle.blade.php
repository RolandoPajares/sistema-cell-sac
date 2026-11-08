@extends('layouts.panel.principal')

@section('title', 'Detalle de reembolso')
@section('body_class', 'modulo-reembolsos')

@section('content')
<section class="admin-finance-module admin-finance-module--reembolsos" data-finance-module="reembolsos">
    <header class="admin-finance-header">
        <nav class="admin-finance-breadcrumb" aria-label="Ruta de navegación">
            <a href="{{ route('management.refunds') }}">Reembolsos</a>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <strong>Solicitud #{{ $record->id }}</strong>
        </nav>
        <div class="admin-finance-heading-row">
            <div class="admin-finance-heading">
                <span class="admin-finance-eyebrow"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Finanzas</span>
                <h1>Solicitud #{{ $record->id }}</h1>
                <p>Estado: {{ $record->estado }}. La aprobación administrativa no ejecuta una devolución de dinero.</p>
            </div>
            <div class="admin-finance-actions"><a class="admin-finance-button admin-finance-button--secondary" href="{{ route('management.refunds') }}">Volver al listado</a></div>
        </div>
    </header>
    @if (session('status')) <p class="alert alert-success" role="status">{{ session('status') }}</p> @endif
    @foreach ($errors->all() as $error) <p class="text-danger" role="alert">{{ $error }}</p> @endforeach
    <section class="admin-finance-table-card">
        <header class="admin-finance-card-header"><div><h2>Datos de la solicitud</h2></div></header>
        <div class="admin-finance-table-wrap">
            <table class="admin-finance-table">
                <tbody>
                    <tr><th scope="row">Devolución</th><td>{{ $record->devolucion?->codigo ?? 'Sin devolución' }}</td></tr>
                    <tr><th scope="row">Resolución</th><td>{{ $record->devolucion?->resolucion ?: 'Sin resolución' }}</td></tr>
                    <tr><th scope="row">Cobro original</th><td>{{ $record->pago?->codigo ?? 'Sin cobro' }}</td></tr>
                    <tr><th scope="row">Importe</th><td>{{ $record->pago?->moneda ?? 'PEN' }} {{ $record->importe }}</td></tr>
                    <tr><th scope="row">Estado</th><td>{{ $record->estado }}</td></tr>
                    <tr><th scope="row">Autorizado por</th><td>{{ $record->autorizadoPor?->nombre ?? 'Pendiente' }}</td></tr>
                    <tr><th scope="row">Registrado el</th><td>{{ $record->creado_en?->format('d/m/Y H:i') }}</td></tr>
                </tbody>
            </table>
        </div>
    </section>
    @if ($canApprove)
        <section class="admin-finance-table-card">
            <header class="admin-finance-card-header"><div><h2>Aprobación administrativa</h2><p>Se comprobará la evidencia del cobro original. No se enviará dinero.</p></div></header>
            <form method="POST" action="{{ route('management.refunds.approve', $record) }}" class="admin-finance-filters">
                @csrf
                @method('PATCH')
                <input type="hidden" name="confirmacion" value="aprobar">
                <button class="admin-finance-button admin-finance-button--primary" type="submit" data-confirm="¿Aprobar esta solicitud? Esta acción no envía dinero.">Aprobar solicitud</button>
            </form>
        </section>
    @endif
    @if ($canCancel)
        <section class="admin-finance-table-card">
            <header class="admin-finance-card-header"><div><h2>Anular solicitud pendiente</h2></div></header>
            <form method="POST" action="{{ route('management.refunds.cancel', $record) }}" class="admin-finance-filters">
                @csrf
                @method('PATCH')
                <label>Motivo de anulación
                    <input type="text" name="motivo" value="{{ old('motivo') }}" minlength="10" maxlength="500" required>
                </label>
                <button class="admin-finance-button admin-finance-button--secondary" type="submit" data-confirm="¿Anular esta solicitud pendiente?">Anular solicitud</button>
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
                        <tr><td colspan="3">No hay eventos registrados para esta solicitud.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</section>
@endsection
