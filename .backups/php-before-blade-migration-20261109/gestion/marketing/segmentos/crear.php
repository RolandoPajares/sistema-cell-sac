<?php
// Formulario de alta: la ruta y el token protegen la operación de escritura.
?>
<form method="POST" class="form-card compact-form" action="<?= e(route('management.segments.store')) ?>">
    <?= csrf_field() ?>
    <label>
        Nombre
        <input name="nombre" required maxlength="120">
    </label>

    <!-- Selección de roles que integran el segmento. -->
    <fieldset>
        <legend>Roles incluidos</legend>
        <?php foreach ($roles as $role) { ?>
            <label class="check">
                <input type="checkbox" name="roles[]" value="<?= e($role) ?>">
                <?= e(str_replace('_', ' ', $role)) ?>

            </label>
        <?php } ?>
    </fieldset>
    <button class="btn btn-primary">Crear segmento</button>
</form>
