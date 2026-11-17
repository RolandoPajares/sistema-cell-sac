
<article class="tc-org__card tc-org__table-card">
    <div class="tc-org__card-title"><div><h2>{{ $titulo }} ({{ $total ?? count($filas) }})</h2><p>Mostrando {{ count($filas) }} registros de ejemplo. No corresponden a datos reales.</p></div></div>
    <div class="tc-org__table-wrap"><table><thead><tr><th>#</th>@foreach ($columnas as $columna)<th>{{ $columna }}</th>@endforeach<th>Acciones</th></tr></thead>
        <tbody>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
            @foreach ($filas as $fila)
                <tr data-org-row @foreach ($fila['filtros'] ?? [] as $campo => $valor) data-org-{{ $campo }}="{{ $valor }}" @endforeach>
                    <td>{{ $loop->iteration }}</td>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                    @foreach ($fila['celdas'] as $celda)
                        <td>{{ $celda }}</td>
                    @endforeach
                    <td class="tc-org__row-actions"><button type="button" @if (isset($accionesFila['ver'])) data-org-open="{{ $accionesFila['ver'] }}" aria-label="Ver detalle de {{ $fila['celdas'][0] }}" @else disabled title="Detalles pendientes" @endif><i class="bi bi-eye"></i></button><button type="button" @if (isset($accionesFila['editar'])) data-org-open="{{ $accionesFila['editar'] }}" aria-label="Editar {{ $fila['celdas'][0] }}" @else disabled title="Edición pendiente" @endif><i class="bi bi-pencil"></i></button><button type="button" @if (isset($accionesFila['mas'])) data-org-open="{{ $accionesFila['mas'] }}" aria-label="Más acciones para {{ $fila['celdas'][0] }}" @else disabled title="Más acciones pendientes" @endif><i class="bi bi-three-dots-vertical"></i></button></td>
                </tr>
            @endforeach
        </tbody>
    </table></div>
    <p class="tc-org__no-results" data-org-empty hidden>No hay ejemplos que coincidan con la búsqueda.</p>
</article>
