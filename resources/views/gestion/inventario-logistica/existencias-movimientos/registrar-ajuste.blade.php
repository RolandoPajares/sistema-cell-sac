
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Ajuste</title>
</head>
<body>
    

{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
<form method="post" class="admin-edit-popover" action="{{ route('management.inventory.adjust', $product) }}">
{{-- proteccion CSRF que valida el origen del formulario. --}}
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
            <input type="number" name="cantidad" min="0" required>
        </label>

        <label class="admin-form-group is-full">
            <span>Motivo u observación</span>
            <input name="notas" maxlength="500" required>
        </label>
    </div>
    <div class="admin-form-actions"><button class="admin-primary-button" type="submit">Guardar movimiento</button></div>
</form>
</body>
</html>