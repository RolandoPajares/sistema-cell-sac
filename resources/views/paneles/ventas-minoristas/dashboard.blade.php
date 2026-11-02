@extends('layouts.administracion')

@section('title', 'Panel de ventas minoristas')
@section('body_class', 'modulo-dashboard modulo-ventas-minoristas')

@push('styles')
    @vite('resources/css/paneles/ventas-minoristas/index.css')
@endpush

@section('content')
    @include('paneles.ventas-minoristas.secciones.resumen-ventas')
@endsection

@push('scripts')
    @vite('resources/js/modulos/paneles/ventas-minoristas/index.js')
@endpush
