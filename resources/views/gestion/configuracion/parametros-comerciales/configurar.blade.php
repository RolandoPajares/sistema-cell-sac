@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Parámetros comerciales')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-configuracion-comercial')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
<section class="tc-cs">
    <header class="tc-cs__header"><div class="tc-cs__title"><span class="tc-cs__title-icon"><i class="bi bi-cart-check" aria-hidden="true"></i></span><div><h1>Parámetros comerciales</h1><p>Configura valores predeterminados para cotizaciones, pedidos y operaciones comerciales.</p></div></div></header>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
    @if ($canUpdateConfiguration)
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
        <form method="post" action="{{ route('management.configuration.update') }}" class="tc-cs__card tc-cs__dialog-fields">
{{-- proteccion CSRF que valida el origen del formulario. --}}
            @csrf @method('PUT')
            <input type="hidden" name="grupo" value="parametros-comerciales">
            <label class="tc-cs__field">Alcance<select name="alcance"><option value="global">Global</option><option value="sede">Sede específica</option></select></label>
            <label class="tc-cs__field">Sede<select name="sede_id"><option value="">Seleccionar sede</option>@foreach ($sedes as $sede)<option value="{{ $sede->id }}" @selected((string) $sedeId === (string) $sede->id)>{{ $sede->nombre }}</option>@endforeach</select></label>
            <label class="tc-cs__field">Vigencia predeterminada de cotización (días)<input type="number" name="configuracion[vigencia_cotizacion]" value="{{ old('configuracion.vigencia_cotizacion', $configuracion['vigencia_cotizacion'] ?? 15) }}" min="1" max="365" required></label>
            <label class="tc-cs__field">Descuento máximo autorizable (%)<input type="number" step="0.01" name="configuracion[descuento_maximo]" value="{{ old('configuracion.descuento_maximo', $configuracion['descuento_maximo'] ?? 0) }}" min="0" max="100" required></label>
            <label class="tc-cs__field">Plazo predeterminado de pedido (días)<input type="number" name="configuracion[plazo_pedido]" value="{{ old('configuracion.plazo_pedido', $configuracion['plazo_pedido'] ?? 0) }}" min="0" max="365"></label>
            <button type="submit" class="tc-cs__button tc-cs__button--primary">Guardar parámetros comerciales</button>
        </form>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
    @else
        <p class="tc-cs__empty">No tienes autorización para modificar estos parámetros.</p>
    @endif
</section>
@endsection
