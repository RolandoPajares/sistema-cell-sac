<div class="gestion-pendiente-form-fields">
    <?php $__currentLoopData = ($module['fields'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $fieldType = $field['type'] ?? 'text';
            $fieldId = $module['key'] . '-' . ($mode ?? 'formulario') . '-' . $loop->index;
        ?>
        <label class="gestion-pendiente-field<?php echo e(($field['wide'] ?? false) ? ' gestion-pendiente-field--wide' : ''); ?>" for="<?php echo e($fieldId); ?>">
            <span><?php echo e($field['label']); ?> <?php if($field['required'] ?? false): ?><b>*</b><?php endif; ?></span>
            <?php if($fieldType === 'textarea'): ?>
                <textarea id="<?php echo e($fieldId); ?>" rows="3" placeholder="<?php echo e($field['placeholder'] ?? ''); ?>"></textarea>
            <?php elseif($fieldType === 'select'): ?>
                <select id="<?php echo e($fieldId); ?>">
                    <option value=""><?php echo e($field['placeholder'] ?? 'Seleccionar'); ?></option>
                    <?php $__currentLoopData = ($field['options'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option); ?>"><?php echo e($option); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            <?php else: ?>
                <input id="<?php echo e($fieldId); ?>" type="<?php echo e($fieldType); ?>" placeholder="<?php echo e($field['placeholder'] ?? ''); ?>">
            <?php endif; ?>
        </label>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
