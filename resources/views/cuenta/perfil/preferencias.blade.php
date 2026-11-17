@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Preferencias de visualización')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
    {{-- Encabezado y navegación de preferencias personales. --}}
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Cuenta personal</span><h1>Preferencias de visualización</h1><p>Guarda preferencias personales sin cambiar la configuración global del ecommerce.</p></div></header>
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
        @include('cuenta.perfil.navegacion')
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
        @unless ($preferencesAvailable)
            {{-- Aviso mostrado si la columna de preferencias no está disponible. --}}
            <div class="admin-alert admin-alert--error" role="status">Las preferencias quedan pendientes hasta aplicar la migración de seguridad de cuenta.</div>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
        @else
            {{-- Formulario para guardar las preferencias de visualización. --}}
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
            <form method="post" action="{{ route('management.account.preferences.update') }}" class="form-card">
{{-- proteccion CSRF que valida el origen del formulario. --}}
                @csrf @method('PUT')
                <input type="hidden" name="menu_compacto" value="0"><label><input type="checkbox" name="menu_compacto" value="1" @checked($preferences['menu_compacto'] ?? false)> Mantener el menú lateral compacto</label>
                <input type="hidden" name="idioma" value="es">
                <button class="btn btn-primary" type="submit">Guardar preferencias</button>
            </form>
        @endunless
    </section>
@endsection
