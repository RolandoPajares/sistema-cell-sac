@extends('layouts.administracion')

@section('title', 'Preferencias de visualización')
@section('content')
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Cuenta personal</span><h1>Preferencias de visualización</h1><p>Guarda preferencias personales sin cambiar la configuración global del ecommerce.</p></div></header>
        @include('paneles.administrador.perfil.navegacion')
        @unless ($preferencesAvailable)
            <div class="admin-alert admin-alert--error" role="status">Las preferencias quedan pendientes hasta aplicar la migración de seguridad de cuenta.</div>
        @else
            @php($preferences = (array) auth()->user()->preferencias)
            <form method="post" action="{{ route('management.account.preferences.update') }}" class="form-card">
                @csrf @method('PUT')
                <input type="hidden" name="menu_compacto" value="0"><label><input type="checkbox" name="menu_compacto" value="1" @checked($preferences['menu_compacto'] ?? false)> Mantener el menú lateral compacto</label>
                <input type="hidden" name="idioma" value="es">
                <button class="btn btn-primary" type="submit">Guardar preferencias</button>
            </form>
        @endunless
    </section>
@endsection
