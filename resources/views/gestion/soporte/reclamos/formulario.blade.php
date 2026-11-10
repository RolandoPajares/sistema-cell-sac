<div class="gestion-pendiente-form-fields">
    @foreach (($module['fields'] ?? []) as $field)
        <label class="gestion-pendiente-field{{ ($field['wide'] ?? false) ? ' gestion-pendiente-field--wide' : '' }}" for="{{ $module['key'] . '-' . ($mode ?? 'formulario') . '-' . $loop->index }}">
            <span>{{ $field['label'] }} @if ($field['required'] ?? false)<b>*</b>@endif</span>
            @if (($field['type'] ?? 'text') === 'textarea')
                <textarea id="{{ $module['key'] . '-' . ($mode ?? 'formulario') . '-' . $loop->index }}" rows="3" placeholder="{{ $field['placeholder'] ?? '' }}"></textarea>
            @elseif (($field['type'] ?? 'text') === 'select')
                <select id="{{ $module['key'] . '-' . ($mode ?? 'formulario') . '-' . $loop->index }}">
                    <option value="">{{ $field['placeholder'] ?? 'Seleccionar' }}</option>
                    @foreach (($field['options'] ?? []) as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            @else
                <input id="{{ $module['key'] . '-' . ($mode ?? 'formulario') . '-' . $loop->index }}" type="{{ $field['type'] ?? 'text' }}" placeholder="{{ $field['placeholder'] ?? '' }}">
            @endif
        </label>
    @endforeach
</div>
