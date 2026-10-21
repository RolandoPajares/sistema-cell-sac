
@extends('layouts.admin')

@section('title', 'Cupones')
@section('body_class', 'modulo-cupones')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/modulos/marketing/cupones.css') }}">
@endpush

@section('content')
<section class="coupons-page">

    <header class="coupons-heading">
        <span class="coupons-eyebrow">Marketing Center</span>
        <h1>Cupones <i class="bi bi-ticket-perforated"></i></h1>
        <p>Administra los códigos promocionales de la empresa.</p>
    </header>

    <div class="coupons-notice">
        <i class="bi bi-info-circle"></i>
        <p>
            Los cupones se administran aquí, pero no descuentan
            el total del checkout hasta completar pruebas de
            compatibilidad y límites.
        </p>
    </div>

    @if(session('status'))
        <div class="coupons-success" role="status">
            <i class="bi bi-check-circle"></i>
            {{ session('status') }}
        </div>
    @endif

    <section class="coupons-panel">

        <div class="coupons-panel-header">
            <div>
                <h2>
                    <i class="bi bi-ticket-perforated"></i>
                    Cupones registrados
                </h2>
                <p>Consulta los descuentos y sus límites de uso.</p>
            </div>

            <button type="button"
                    class="coupons-primary-button"
                    onclick="document.getElementById('coupon-create-modal').showModal()">
                <i class="bi bi-plus-lg"></i>
                Nuevo cupón
            </button>
        </div>

        <div class="coupons-grid">

            @forelse($coupons as $coupon)

                @php
                    $isPercentage = $coupon->tipo_descuento === 'porcentaje';
                    $uses = (int) $coupon->usos;
                    $limit = $coupon->limite_usos;
                    $progress = $limit
                        ? min(100, ($uses / max(1, (int) $limit)) * 100)
                        : 0;
                @endphp

                <article class="coupon-card">

                    <div class="coupon-card-top">
                        <span class="coupon-icon">
                            <i class="bi bi-ticket-perforated"></i>
                        </span>

                        <span class="coupon-type">
                            {{ $isPercentage ? 'Porcentaje' : 'Monto fijo' }}
                        </span>
                    </div>

                    <div class="coupon-code-label">
                        Código promocional
                    </div>

                    <div class="coupon-code">
                        {{ $coupon->codigo }}
                    </div>

                    <div class="coupon-discount">
                        @if($isPercentage)
                            {{ number_format((float) $coupon->valor, 2) }}%
                        @else
                            S/ {{ number_format((float) $coupon->valor, 2) }}
                        @endif
                    </div>

                    <div class="coupon-detail">
                        <i class="bi bi-cart-check"></i>
                        <span>
                            Compra mínima:
                            <strong>
                                S/ {{ number_format((float) $coupon->minimo_compra, 2) }}
                            </strong>
                        </span>
                    </div>

                    <div class="coupon-detail">
                        <i class="bi bi-calendar-event"></i>
                        <span>
                            Inicio:
                            {{ $coupon->inicia_en?->format('d/m/Y H:i') ?? 'No especificado' }}
                        </span>
                    </div>

                    <div class="coupon-detail">
                        <i class="bi bi-calendar-check"></i>
                        <span>
                            Fin:
                            {{ $coupon->finaliza_en?->format('d/m/Y H:i') ?? 'No especificado' }}
                        </span>
                    </div>

                    <div class="coupon-usage">
                        <div class="coupon-usage-heading">
                            <span>Usos registrados</span>
                            <strong>
                                {{ $uses }} /
                                {{ $limit ?? 'Ilimitado' }}
                            </strong>
                        </div>

                        <div class="coupon-progress"
                             role="progressbar"
                             aria-label="Utilización del cupón"
                             aria-valuemin="0"
                             aria-valuemax="100"
                             aria-valuenow="{{ round($progress) }}">
                            <div style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    <div class="coupon-card-footer">
                        <span>
                            <i class="bi bi-info-circle"></i>
                            Pendiente de integración con checkout
                        </span>
                    </div>

                </article>

            @empty

                <div class="coupons-empty">
                    <i class="bi bi-ticket-perforated"></i>
                    <h3>No hay cupones registrados</h3>
                    <p>Crea tu primer cupón promocional.</p>
                </div>

            @endforelse

        </div>

        <div class="coupons-pagination">
            {{ $coupons->links() }}
        </div>

    </section>
</section>

{{-- MODAL NUEVO CUPÓN --}}

<dialog id="coupon-create-modal"
        class="coupon-modal"
        aria-labelledby="coupon-modal-title">

    <div class="coupon-modal-header">
        <div>
            <h2 id="coupon-modal-title">Nuevo cupón</h2>
            <p>Completa los datos del código promocional.</p>
        </div>

        <button type="button"
                class="coupon-modal-close"
                aria-label="Cerrar"
                onclick="document.getElementById('coupon-create-modal').close()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <form method="post"
          action="{{ route('management.coupons.store') }}"
          class="coupon-form">

        @csrf

        <div class="coupon-form-body">

            @if($errors->any())
                <div class="coupon-errors" role="alert">
                    <strong>Revisa los siguientes errores:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="coupon-form-grid">

                <label class="coupon-field">
                    <span>Código</span>
                    <input type="text"
                           name="codigo"
                           value="{{ old('codigo') }}"
                           placeholder="Ej. DESCUENTO20"
                           required>
                </label>

                <label class="coupon-field">
                    <span>Tipo de descuento</span>
                    <select name="tipo_descuento" required>
                        <option value="porcentaje"
                            @selected(old('tipo_descuento', 'porcentaje') === 'porcentaje')>
                            Porcentaje
                        </option>
                        <option value="fijo"
                            @selected(old('tipo_descuento') === 'fijo')>
                            Monto fijo
                        </option>
                    </select>
                </label>

                <label class="coupon-field">
                    <span>Valor</span>
                    <input type="number"
                           name="valor"
                           min="0.01"
                           step="0.01"
                           value="{{ old('valor') }}"
                           placeholder="Ej. 20.00"
                           required>
                </label>

                <label class="coupon-field">
                    <span>Compra mínima (S/)</span>
                    <input type="number"
                           name="minimo_compra"
                           min="0"
                           step="0.01"
                           value="{{ old('minimo_compra', 0) }}"
                           required>
                </label>

                <label class="coupon-field coupon-field-full">
                    <span>Límite de usos</span>
                    <input type="number"
                           name="limite_usos"
                           min="1"
                           value="{{ old('limite_usos') }}"
                           placeholder="Vacío para uso ilimitado">
                </label>

                <label class="coupon-field">
                    <span>Fecha de inicio</span>
                    <input type="datetime-local"
                           name="inicia_en"
                           value="{{ old('inicia_en') }}"
                           required>
                </label>

                <label class="coupon-field">
                    <span>Fecha de finalización</span>
                    <input type="datetime-local"
                           name="finaliza_en"
                           value="{{ old('finaliza_en') }}"
                           required>
                </label>

            </div>
        </div>

        <div class="coupon-form-footer">
            <button type="button"
                    class="coupon-cancel-button"
                    onclick="document.getElementById('coupon-create-modal').close()">
                Cancelar
            </button>

            <button type="submit"
                    class="coupons-primary-button">
                <i class="bi bi-check-circle"></i>
                Guardar cupón
            </button>
        </div>

    </form>

</dialog>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('coupon-create-modal');

        if (modal && !modal.open) {
            modal.showModal();
        }
    });
</script>
@endif

@endsection
