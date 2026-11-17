
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
@if ($dynamicFields->isNotEmpty())
    <section class="tc-profile-dynamic-fields" data-profile-dynamic-fields aria-labelledby="tc-profile-dynamic-title">
        <header class="tc-profile-dynamic-heading"><i class="bi bi-sliders2" aria-hidden="true"></i><div><h3 id="tc-profile-dynamic-title">Información adicional</h3><p>Campos definidos por la configuración de tus roles vigentes.</p></div></header>
        <div class="tc-profile-fields">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
            @foreach ($dynamicFields as $field)
{{-- logica auxiliar de la vista que prepara valores para la presentacion. --}}
                @php($fieldValue = old('perfil.'.$field->id, $dynamicValues[$field->id] ?? null))
                <label class="{{ $field->tipo === 'textarea' ? 'tc-profile-field--full' : '' }}">
                    <span>{{ $field->etiqueta }} @if ($field->perfil_requerido)<b>*</b>@endif</span>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                    @if ($field->tipo === 'textarea')
                        <textarea name="perfil[{{ $field->id }}]" rows="3" @disabled(! $field->perfil_editable) @required($field->perfil_requerido)>{{ $fieldValue }}</textarea>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                    @elseif ($field->tipo === 'select')
                        <select name="perfil[{{ $field->id }}]" @disabled(! $field->perfil_editable) @required($field->perfil_requerido)>
                            <option value="">Seleccionar</option>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                            @foreach (($field->opciones ?? []) as $option)
                                <option value="{{ $option }}" @selected((string) $fieldValue === (string) $option)>{{ $option }}</option>
                            @endforeach
                        </select>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                    @else
                        <input type="{{ $field->tipo }}" name="perfil[{{ $field->id }}]" value="{{ $fieldValue }}" @disabled(! $field->perfil_editable) @required($field->perfil_requerido)>
                    @endif
                </label>
            @endforeach
        </div>
    </section>
@endif
