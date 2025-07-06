<?php if (isset($component)) { $__componentOriginalb70bbe65d6069ad304684c255813fc0e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb70bbe65d6069ad304684c255813fc0e = $attributes; } ?>
<?php $component = App\View\Components\TixswapLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tixswap-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\TixswapLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('title', null, []); ?> Your Profile <?php $__env->endSlot(); ?>
     <?php $__env->slot('navigation', null, []); ?> 
         <!-- You can add specific navigation for the profile page if needed -->
     <?php $__env->endSlot(); ?>

    <div class="space-y-10">
        <!-- Profile Header -->
        <div>
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e($user->name); ?></h1>
        </div>

        <!-- Ticket Management Section -->
        <div>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Ticket Management</h2>
            <div class="bg-white shadow-md rounded-lg">
                <div class="p-6">
                    <h3 class="font-medium text-gray-900">Current Listings</h3>
                    <ul class="mt-4 space-y-3">
                        <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <li class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                <p class="font-medium text-gray-700"><?php echo e($ticket->event->name); ?></p>
                                <a href="#" class="bg-red-600 text-white text-sm font-semibold py-1 px-4 rounded-md hover:bg-red-700">Edit</a>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li class="text-center text-gray-500 py-4">You have no active listings.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Notifications Section -->
        <div>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Notifications</h2>
            <div class="bg-white shadow-md rounded-lg">
               <div class="p-6">
                   <ul class="space-y-3">
                       <li class="text-center text-gray-500 py-4">You have no new notifications.</li>
                   </ul>
               </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb70bbe65d6069ad304684c255813fc0e)): ?>
<?php $attributes = $__attributesOriginalb70bbe65d6069ad304684c255813fc0e; ?>
<?php unset($__attributesOriginalb70bbe65d6069ad304684c255813fc0e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb70bbe65d6069ad304684c255813fc0e)): ?>
<?php $component = $__componentOriginalb70bbe65d6069ad304684c255813fc0e; ?>
<?php unset($__componentOriginalb70bbe65d6069ad304684c255813fc0e); ?>
<?php endif; ?><?php /**PATH C:\Users\senet\TixSwap\TixSwap\resources\views/profile/edit.blade.php ENDPATH**/ ?>