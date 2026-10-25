@extends('layouts.administracion')

@section('title', 'Perfil — cliente minorista')
@section('body_class', 'modulo-cuenta-admin')

@section('content')
    <x-perfil.pagina mode="mostrar" role="cliente_minorista" />
@endsection
