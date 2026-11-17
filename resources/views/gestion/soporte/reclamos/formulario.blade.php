
<div class="gestion-pendiente-form-fields">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
    @foreach (($module['fields'] ?? []) as $field)
        <label class="gestion-pendiente-field{{ ($field['wide'] ?? false) ? ' gestion-pendiente-field--wide' : '' }}" for="{{ $module['key'] . '-' . ($mode ?? 'formulario') . '-' . $loop->index }}">
            <span>{{ $field['label'] }} @if ($field['required'] ?? false)<b>*</b>@endif</span>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
            @if (($field['type'] ?? 'text') === 'textarea')
                <textarea id="{{ $module['key'] . '-' . ($mode ?? 'formulario') . '-' . $loop->index }}" rows="3" placeholder="{{ $field['placeholder'] ?? '' }}"></textarea>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
            @elseif (($field['type'] ?? 'text') === 'select')
                <select id="{{ $module['key'] . '-' . ($mode ?? 'formulario') . '-' . $loop->index }}">
                    <option value="">{{ $field['placeholder'] ?? 'Seleccionar' }}</option>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                    @foreach (($field['options'] ?? []) as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
            @else
                <input id="{{ $module['key'] . '-' . ($mode ?? 'formulario') . '-' . $loop->index }}" type="{{ $field['type'] ?? 'text' }}" placeholder="{{ $field['placeholder'] ?? '' }}">
            @endif
        </label>
    @endforeach
</div>
