@extends('layouts.administracion')

@section('title', 'Segmentación')

@section('content')
    <section class="container content-page">
        <h1>Segmentación por rol de cuenta</h1>
        <p>Los segmentos controlan qué campañas internas se muestran a cada perfil CELL SAC.</p>

        @include('gestion.segmentos.crear', ['roles' => $roles])

        @foreach($segments as $segment)
            <article class="data-row">
                <b>{{ $segment->nombre }}</b>
                <span>{{ implode(', ', $segment->criterios['roles'] ?? []) }}</span>
            </article>
        @endforeach

        {{ $segments->links() }}
    </section>
@endsection


