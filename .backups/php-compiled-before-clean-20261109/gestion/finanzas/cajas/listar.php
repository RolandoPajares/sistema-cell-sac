<?php
    $pagina = (require config_path('gestion/finanzas-facturacion.php'))['cajas'];
?>
<?php ($pagina['clave'] = 'cajas'); ?>
<?php echo $__env->make('gestion.finanzas.finanzas-facturacion.pagina', ['pagina' => $pagina], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
