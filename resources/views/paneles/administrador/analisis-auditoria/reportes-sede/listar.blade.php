@extends('layouts.administracion')
@section('title', 'Reportes por sede · Vista de diseño')
@section('body_class', 'modulo-analisis-auditoria')
@section('content')
    @include('paneles.administrador.analisis-auditoria.componentes.pagina', ['tipo' => 'sedes'])
@endsection
