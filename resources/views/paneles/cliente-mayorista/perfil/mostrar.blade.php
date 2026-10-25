@extends('layouts.administracion')

@section('title', 'Perfil — cliente mayorista')
@section('body_class', 'modulo-cuenta-admin')

@section('content')
    <x-perfil.pagina mode="mostrar" role="cliente_mayorista" />
@endsection
