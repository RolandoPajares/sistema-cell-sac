@extends('layouts.administracion')

@section('title', 'Compras')

@section('content')
    <section class="container content-page">
        <h1>Compras a proveedores</h1>

        @include('gestion.compras.crear', ['suppliers' => $suppliers, 'products' => $products])

        @foreach($purchases as $purchase)
            <article class="data-row">
                <div>
                    <b>Compra #{{ $purchase->id }} — {{ $purchase->supplier->nombre }}</b>
                    <small>{{ $purchase->fecha_compra }} · {{ $purchase->estado }}</small>
                </div>
                <strong>S/ {{ number_format((float) $purchase->total, 2) }}</strong>
            </article>
        @endforeach

        {{ $purchases->links() }}
    </section>
@endsection


