@extends('layouts.administracion')

@section('title', 'Perfil — administrador')
@section('body_class', 'modulo-cuenta-admin')

@section('content')
    <x-perfil.pagina mode="mostrar" role="administrador" />
@endsection
