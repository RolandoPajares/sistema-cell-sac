@extends('layouts.administracion')

@section('title', 'Panel de cliente mayorista')
@section('body_class', 'modulo-cuenta rol-cliente-mayorista')

@section('content')
    {{-- El panel tiene varias vistas. El menú lateral cambia entre ellas con #perfil, #configuracion, etc. --}}
    <div class="cliente-vistas" data-cliente-vistas>
        <div data-cliente-vista="resumen">
            @include('paneles.cliente-mayorista.secciones.resumen-mayorista')
        </div>
        <div data-cliente-vista="cotizaciones" hidden>
            @include('paneles.cliente-mayorista.secciones.cotizaciones')
        </div>
        <div data-cliente-vista="beneficios" hidden>
            @include('paneles.cliente-mayorista.secciones.beneficios')
        </div>
        <div data-cliente-vista="garantias" hidden>
            @include('paneles.cliente-mayorista.secciones.garantias')
        </div>
        <div data-cliente-vista="perfil" hidden>
            @include('paneles.cliente-mayorista.secciones.perfil')
        </div>
        <div data-cliente-vista="reportes" hidden>
            @include('paneles.cliente-mayorista.secciones.reportes')
        </div>
        <div data-cliente-vista="configuracion" hidden>
            @include('paneles.cliente-mayorista.secciones.configuracion')
        </div>
    </div>
@endsection
