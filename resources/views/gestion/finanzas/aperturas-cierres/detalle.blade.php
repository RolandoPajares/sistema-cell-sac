@extends('layouts.panel.principal')

@section('title', 'Arqueo de caja')
@section('body_class', 'modulo-finanzas-facturacion')

@section('content')
<section class="tc-ff" data-cajas-operativas data-cajas-reopen="{{ old('_form') === 'movimiento' && $errors->any() ? 'crear' : '' }}">
    <header class="tc-ff__header">
        <div>
            <div class="tc-ff__breadcrumb"><a href="{{ route('management.payments.preview.cash_sessions') }}">Apertura y cierre</a><i class="bi bi-chevron-right" aria-hidden="true"></i><strong>Arqueo de caja</strong></div>
            <h1><i class="bi bi-safe" aria-hidden="true"></i>Arqueo de {{ $session->caja->codigo }}</h1>
            <p>{{ $session->caja->sede->nombre }} · Apertura {{ $session->abierta_en?->format('d/m/Y H:i') }} · {{ $session->usuarioApertura?->nombre }}</p>
        </div>
        <div class="tc-ff__header-actions">
            @if ($canMove)<button type="button" class="tc-ff__button tc-ff__button--primary" data-box-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i>Registrar movimiento</button>@endif
            <a class="tc-ff__button" href="{{ route('management.payments.preview.cash_sessions') }}">Historial</a>
        </div>
    </header>

    @if (session('status')) <p class="alert alert-success" role="status">{{ session('status') }}</p> @endif
    @foreach ($errors->all() as $error) <p class="text-danger" role="alert">{{ $error }}</p> @endforeach

    <div class="tc-ff__metrics">
        <article class="tc-ff__metric tc-ff__metric--azul"><span class="tc-ff__metric-icon"><i class="bi bi-wallet" aria-hidden="true"></i></span><div><small>Saldo inicial contado</small><strong>S/ {{ $session->saldo_inicial }}</strong><span>Al abrir la sesión</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--verde"><span class="tc-ff__metric-icon"><i class="bi bi-arrow-down-left" aria-hidden="true"></i></span><div><small>Entradas registradas</small><strong>S/ {{ $balance['entradas'] }}</strong><span>Movimientos de caja</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--ambar"><span class="tc-ff__metric-icon"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></span><div><small>Salidas registradas</small><strong>S/ {{ $balance['salidas'] }}</strong><span>Movimientos de caja</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--azul"><span class="tc-ff__metric-icon"><i class="bi bi-calculator" aria-hidden="true"></i></span><div><small>Saldo esperado</small><strong>S/ {{ $balance['saldo_esperado'] }}</strong><span>Inicial + entradas − salidas</span></div></article>
    </div>

    <div class="tc-ff__main-grid">
        <article class="tc-ff__card tc-ff__table-card">
            <div class="tc-ff__card-heading"><h2><i class="bi bi-list-ul" aria-hidden="true"></i>Movimientos</h2><small>{{ $movements->total() }} registros</small></div>
            <div class="tc-ff__table-wrap">
                <table>
                    <thead><tr><th>Fecha</th><th>Dirección</th><th>Tipo</th><th>Importe</th><th>Descripción</th><th>Usuario</th></tr></thead>
                    <tbody>
                        @forelse ($movements as $movement)
                            <tr><td>{{ $movement->creado_en?->format('d/m/Y H:i') }}</td><td><span class="tc-ff__pill tc-ff__pill--{{ $movement->direccion === 'entrada' ? 'verde' : 'rojo' }}">{{ ucfirst($movement->direccion) }}</span></td><td>{{ $movement->tipo }}</td><td>S/ {{ $movement->importe }}</td><td>{{ $movement->descripcion ?? '—' }}</td><td>{{ $movement->creador?->nombre ?? 'Sistema' }}</td></tr>
                        @empty
                            <tr><td colspan="6">Esta sesión todavía no tiene movimientos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($movements->hasPages())<div class="tc-ff__card-heading"><small>Página {{ $movements->currentPage() }} de {{ $movements->lastPage() }}</small><div class="tc-ff__header-actions">@if ($movements->previousPageUrl()) <a class="tc-ff__button" href="{{ $movements->previousPageUrl() }}">Anterior</a> @endif @if ($movements->nextPageUrl()) <a class="tc-ff__button" href="{{ $movements->nextPageUrl() }}">Siguiente</a> @endif</div></div>@endif
        </article>
        <aside class="tc-ff__card tc-ff__aside">
            <h2><i class="bi bi-clipboard-check" aria-hidden="true"></i>Estado y cierre</h2>
            <p><span class="tc-ff__pill tc-ff__pill--{{ $session->estado === 'abierta' ? 'verde' : 'azul' }}">{{ ucfirst($session->estado) }}</span></p>
            @if ($session->estado === 'cerrada')
                <div class="tc-ff__summary"><div><span>Efectivo declarado</span><b>S/ {{ $session->saldo_cierre_declarado }}</b></div><div><span>Diferencia</span><b>S/ {{ $balance['diferencia'] }}</b></div><div><span>Responsable del cierre</span><b>{{ $session->usuarioCierre?->nombre }}</b></div><div><span>Fecha de cierre</span><b>{{ $session->cerrada_en?->format('d/m/Y H:i') }}</b></div></div>
            @elseif ($canClose)
                <p>Cuenta físicamente el efectivo. Si difiere del saldo esperado, el cierre se bloquea hasta documentar y conciliar la diferencia.</p>
                <form method="POST" action="{{ route('management.payments.cash_sessions.update', $session) }}" onsubmit="return window.confirm('¿Confirmas el arqueo y cierre de esta sesión?')">
                    @csrf @method('PATCH')
                    <input type="hidden" name="token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                    <div class="tc-ff__form-grid"><label>Efectivo contado al cierre (S/) *<input type="text" name="saldo_cierre_declarado" inputmode="decimal" value="{{ old('saldo_cierre_declarado') }}" required></label></div>
                    <button class="tc-ff__button tc-ff__button--primary tc-ff__submit" type="submit">Comprobar y cerrar</button>
                </form>
            @endif
        </aside>
    </div>

    @if ($canMove)
        <dialog class="tc-ff__dialog" data-box-dialog="crear" aria-labelledby="cash-movement-title">
            <form class="tc-ff__dialog-card" method="POST" action="{{ route('management.payments.cash_sessions.movements.store', $session) }}">
                @csrf
                <input type="hidden" name="_form" value="movimiento">
                <input type="hidden" name="token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                <header class="tc-ff__dialog-header"><div><span class="tc-ff__dialog-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span><div><h2 id="cash-movement-title">Registrar movimiento manual</h2><p>La operación quedará auditada en esta sesión.</p></div></div><button type="button" class="tc-ff__dialog-close" data-box-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                <div class="tc-ff__dialog-body"><div class="tc-ff__field-grid">
                    <label class="tc-ff__field">Dirección *<select name="direccion" required><option value="">Selecciona</option><option value="entrada" @selected(old('direccion') === 'entrada')>Entrada</option><option value="salida" @selected(old('direccion') === 'salida')>Salida</option></select></label>
                    <label class="tc-ff__field">Importe (S/) *<input type="text" name="importe" inputmode="decimal" value="{{ old('importe') }}" required></label>
                    <label class="tc-ff__field tc-ff__field--full">Motivo *<textarea name="descripcion" maxlength="500" required>{{ old('descripcion') }}</textarea></label>
                </div></div>
                <footer class="tc-ff__dialog-footer"><button type="button" class="tc-ff__button" data-box-close>Cancelar</button><button type="submit" class="tc-ff__button tc-ff__button--primary">Registrar movimiento</button></footer>
            </form>
        </dialog>
    @endif
</section>
@endsection
