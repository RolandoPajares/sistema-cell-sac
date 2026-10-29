@props(['quotations'])

@foreach($quotations as $quotation)
    <article class="data-row">
        <div><b>Cotización #{{ $quotation->id }} · {{ $quotation->customer->nombre_contacto }}</b><small>{{ $quotation->estado }} · {{ $quotation->creado_en }}</small></div>
        <strong>S/ {{ number_format((float) $quotation->total, 2) }}</strong>
    </article>
@endforeach

{{ $quotations->links() }}
