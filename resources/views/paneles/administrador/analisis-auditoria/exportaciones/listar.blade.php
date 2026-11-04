@extends('layouts.administracion')
@section('title', 'Exportaciones autorizadas · Vista de diseño')
@section('body_class', 'modulo-analisis-auditoria')
@section('content')
    @include('paneles.administrador.analisis-auditoria.componentes.pagina', ['tipo' => 'exportaciones'])
@endsection
