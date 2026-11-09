<?php
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

$__env->startSection('title', 'Sesiones activas'); ?>
<?php $__env->startSection('content'); ?>
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Cuenta personal</span><h1>Sesiones y dispositivos</h1><p>Revoca cualquier sesión que ya no reconozcas. El identificador de sesión nunca se muestra.</p></div></header>
        <?php echo $__env->make('cuenta.perfil.navegacion', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php if (! ($available)) { ?>
            <div class="admin-alert admin-alert--error" role="status">La migración de seguridad aún no está aplicada; esta lista se habilitará cuando exista el registro de sesiones.</div>
        <?php } else { ?>
            <div class="form-card">
                <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Dispositivo</th><th>Dirección IP</th><th>Último acceso</th><th>Estado</th><th></th></tr></thead><tbody>
                    <?php $__empty_1 = true;
            $__currentLoopData = $sessions;
            $__env->addLoop($__currentLoopData);
            foreach ($__currentLoopData as $session) {
                $__env->incrementLoopIndices();
                $loop = $__env->getLastLoop();
                $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e(Str::limit($session->agente_usuario ?: 'Agente no informado', 100)); ?></td>
                            <td><?php echo e($session->direccion_ip ?: 'No disponible'); ?></td>
                            <td><?php echo e(Carbon::parse($session->ultimo_acceso)->format('d/m/Y H:i')); ?></td>
                            <td><?php echo e($session->revocada_en ? 'Revocada' : (hash_equals((string) $session->resumen_identificador, (string) $currentHash) ? 'Sesión actual' : 'Activa')); ?></td>
                            <td><?php if (! $session->revocada_en) { ?><form method="post" action="<?php echo e(route('management.account.sessions.revoke', $session->id)); ?>" data-confirm="¿Revocar esta sesión?"><input type="hidden" name="_method" value="DELETE"><?php echo csrf_field(); ?><button class="btn btn-outline-danger btn-sm" type="submit">Revocar</button></form><?php } ?></td>
                        </tr>
                    <?php } $__env->popLoop();
            $loop = $__env->getLastLoop();
            if ($__empty_1) { ?>
                        <tr><td colspan="5">No hay sesiones registradas.</td></tr>
                    <?php } ?>
                </tbody></table></div>
                <?php if ($sessions instanceof Paginator || $sessions instanceof LengthAwarePaginator) { ?><?php echo e($sessions->links()); ?><?php } ?>
            </div>
        <?php } ?>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>