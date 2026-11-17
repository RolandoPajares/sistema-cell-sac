@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Configuración financiera')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-configuracion-finanzas')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
<section class="tc-cs">
    <header class="tc-cs__header"><div class="tc-cs__title"><span class="tc-cs__title-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><div><h1>Configuración financiera</h1><p>Define parámetros de redondeo y tolerancias sin alterar documentos históricos.</p></div></div></header>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
    @if ($canUpdateConfiguration)
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
        <form method="post" action="{{ route('management.configuration.update') }}" class="tc-cs__card tc-cs__dialog-fields">
{{-- proteccion CSRF que valida el origen del formulario. --}}
            @csrf @method('PUT')
            <input type="hidden" name="grupo" value="finanzas">
            <label class="tc-cs__field">Alcance<select name="alcance"><option value="global">Global</option><option value="sede">Sede específica</option></select></label>
            <label class="tc-cs__field">Sede<select name="sede_id"><option value="">Seleccionar sede</option>@foreach ($sedes as $sede)<option value="{{ $sede->id }}" @selected((string) $sedeId === (string) $sede->id)>{{ $sede->nombre }}</option>@endforeach</select></label>
            <label class="tc-cs__field">Moneda predeterminada<input name="configuracion[moneda]" value="{{ old('configuracion.moneda', $configuracion['moneda'] ?? 'PEN') }}" minlength="3" maxlength="3" required></label>
            <label class="tc-cs__field">Decimales de redondeo<input type="number" name="configuracion[redondeo_monetario]" value="{{ old('configuracion.redondeo_monetario', $configuracion['redondeo_monetario'] ?? 2) }}" min="0" max="4" required></label>
            <label class="tc-cs__field">Tolerancia de arqueo<input type="number" step="0.01" name="configuracion[tolerancia_arqueo]" value="{{ old('configuracion.tolerancia_arqueo', $configuracion['tolerancia_arqueo'] ?? 0) }}" min="0" max="99999999.99"></label>
            <label class="tc-cs__field">Tolerancia de pagos<input type="number" step="0.01" name="configuracion[tolerancia_pago]" value="{{ old('configuracion.tolerancia_pago', $configuracion['tolerancia_pago'] ?? 0) }}" min="0" max="99999999.99"></label>
            <button type="submit" class="tc-cs__button tc-cs__button--primary">Guardar configuración financiera</button>
        </form>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
    @else
        <p class="tc-cs__empty">No tienes autorización para modificar estos parámetros.</p>
    @endif
</section>
@endsection
