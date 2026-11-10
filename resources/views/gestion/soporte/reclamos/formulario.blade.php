<div class="gestion-pendiente-form-fields">
    @foreach (($module['fields'] ?? []) as $field)
        @php
            $fieldType = $field['type'] ?? 'text';
            $fieldId = $module['key'] . '-' . ($mode ?? 'formulario') . '-' . $loop->index;
        @endphp
        <label class="gestion-pendiente-field{{ ($field['wide'] ?? false) ? ' gestion-pendiente-field--wide' : '' }}" for="{{ $fieldId }}">
            <span>{{ $field['label'] }} @if ($field['required'] ?? false)<b>*</b>@endif</span>
            @if ($fieldType === 'textarea')
                <textarea id="{{ $fieldId }}" rows="3" placeholder="{{ $field['placeholder'] ?? '' }}"></textarea>
            @elseif ($fieldType === 'select')
                <select id="{{ $fieldId }}">
                    <option value="">{{ $field['placeholder'] ?? 'Seleccionar' }}</option>
                    @foreach (($field['options'] ?? []) as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            @else
                <input id="{{ $fieldId }}" type="{{ $fieldType }}" placeholder="{{ $field['placeholder'] ?? '' }}">
            @endif
        </label>
    @endforeach
</div>
