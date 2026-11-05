@extends('layouts.administracion')

@section('title', 'Facturación')
@section('body_class', 'modulo-facturacion')

@section('content')
{{-- La consulta usa siempre el origen real de documentos_electronicos. No se
     muestra la maqueta anterior a ningún rol autorizado. --}}
@include('paneles.administrador.finanzas.facturacion.listar')
@endsection

