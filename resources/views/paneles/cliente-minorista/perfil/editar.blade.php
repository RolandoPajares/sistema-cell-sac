@extends('layouts.administracion')

@section('title', 'Editar perfil — cliente minorista')
@section('body_class', 'modulo-cuenta-admin')

@section('content')
    <x-perfil.pagina mode="editar" role="cliente_minorista" />
@endsection
