@extends('layouts.administracion')

@section('title', 'Facturación')
@section('body_class', 'modulo-facturacion')

@section('content')
@if (auth()->user()?->isRole('administrador'))
    @include('paneles.administrador.finanzas.facturacion.listar')
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

