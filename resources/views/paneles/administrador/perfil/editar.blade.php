@extends('layouts.administracion')

@section('title', 'Editar perfil — administrador')
@section('body_class', 'modulo-cuenta-admin')

@section('content')
    <x-perfil.pagina mode="editar" role="administrador" />
@endsection
