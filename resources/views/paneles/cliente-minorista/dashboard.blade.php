@extends('layouts.administracion')

@section('title', 'Panel de cliente minorista')
@section('body_class', 'modulo-cuenta rol-cliente-minorista')

@section('content')
    {{-- El panel tiene varias vistas. El menú lateral cambia entre ellas con #perfil, #promociones, etc. --}}
    <div class="cliente-vistas" data-cliente-vistas>
        <div data-cliente-vista="resumen">
            @include('paneles.cliente-minorista.secciones.resumen-minorista')
        </div>
        <div data-cliente-vista="promociones" hidden>
            @include('paneles.cliente-minorista.secciones.promociones')
        </div>
        <div data-cliente-vista="garantias" hidden>
            @include('paneles.cliente-minorista.secciones.garantias')
        </div>
        <div data-cliente-vista="perfil" hidden>
            @include('paneles.cliente-minorista.secciones.perfil')
        </div>
        <div data-cliente-vista="reportes" hidden>
            @include('paneles.cliente-minorista.secciones.reportes')
        </div>
        <div data-cliente-vista="configuracion" hidden>
            @include('paneles.cliente-minorista.secciones.configuracion')
        </div>
    </div>
@endsection
