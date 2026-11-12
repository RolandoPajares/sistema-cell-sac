@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Configuración de inventario')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-configuracion-inventario')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
<section class="tc-cs">
    <header class="tc-cs__header"><div class="tc-cs__title"><span class="tc-cs__title-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><div><h1>Configuración de inventario</h1><p>Administra políticas generales de existencias y ajustes sin modificar movimientos directamente.</p></div></div></header>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
    @if ($canUpdateConfiguration)
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
        <form method="post" action="{{ route('management.configuration.update') }}" class="tc-cs__card tc-cs__dialog-fields">
{{-- proteccion CSRF que valida el origen del formulario. --}}
            @csrf @method('PUT')
            <input type="hidden" name="grupo" value="inventario">
            <label class="tc-cs__field">Alcance<select name="alcance"><option value="global">Global</option><option value="sede">Sede específica</option></select></label>
            <label class="tc-cs__field">Sede<select name="sede_id"><option value="">Seleccionar sede</option>@foreach ($sedes as $sede)<option value="{{ $sede->id }}" @selected((string) $sedeId === (string) $sede->id)>{{ $sede->nombre }}</option>@endforeach</select></label>
            <label class="tc-cs__field">Alerta de stock mínimo<input type="number" name="configuracion[stock_minimo_alerta]" value="{{ old('configuracion.stock_minimo_alerta', $configuracion['stock_minimo_alerta'] ?? 0) }}" min="0" required></label>
            <label class="tc-cs__field">Permitir stock negativo<select name="configuracion[permitir_stock_negativo]"><option value="0">No</option><option value="1" @selected((bool) ($configuracion['permitir_stock_negativo'] ?? false))>Sí</option></select></label>
            <label class="tc-cs__field">Exigir aprobación para ajustes<select name="configuracion[requiere_aprobacion_ajuste]"><option value="1">Sí</option><option value="0" @selected(isset($configuracion['requiere_aprobacion_ajuste']) && ! $configuracion['requiere_aprobacion_ajuste'])>No</option></select></label>
            <button type="submit" class="tc-cs__button tc-cs__button--primary">Guardar configuración de inventario</button>
        </form>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
    @else
        <p class="tc-cs__empty">No tienes autorización para modificar estos parámetros.</p>
    @endif
</section>
@endsection
