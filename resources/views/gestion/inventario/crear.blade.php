<form method="post" class="admin-edit-popover" action="{{ route('management.inventory.adjust', $product) }}">
    @csrf
    <div class="admin-form-grid">
        <label class="admin-form-group">
            <span>Tipo</span>
            <select name="tipo_movimiento">
                <option value="entrada">Entrada</option>
                <option value="salida">Salida</option>
                <option value="ajuste">Ajuste final</option>
            </select>
        </label>

        <label class="admin-form-group">
            <span>Cantidad</span>
            <input type="number" name="cantidad" min="1" required>
        </label>

        <label class="admin-form-group is-full">
            <span>Motivo u observación</span>
            <input name="notas" maxlength="500" required>
        </label>
    </div>
    <div class="admin-form-actions"><button class="admin-primary-button" type="submit">Guardar movimiento</button></div>
</form>
