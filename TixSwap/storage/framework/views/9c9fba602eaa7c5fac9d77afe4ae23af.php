<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('title', null, []); ?> Edit Listing #<?php echo e($ticket->id); ?> <?php $__env->endSlot(); ?>
    <h1 class="text-2xl font-bold mb-6">Edit Listing for <?php echo e($ticket->event->name); ?></h1>
    <form action="<?php echo e(route('admin.listings.update', $ticket)); ?>" method="POST" class="bg-white p-6 rounded-lg shadow space-y-6">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PATCH'); ?>
        <p><strong>Seller:</strong> <?php echo e($ticket->seller->name); ?></p>
        <div>
            <label for="seat_details" class="block text-sm font-medium text-gray-700">Seat Details</label>
            <input type="text" name="seat_details" id="seat_details" value="<?php echo e(old('seat_details', $ticket->seat_details)); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div>
            <label for="price" class="block text-sm font-medium text-gray-700">Price (KES)</label>
            <input type="number" name="price" id="price" value="<?php echo e(old('price', $ticket->price)); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div class="flex justify-end gap-4">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg">Cancel</a>
            <button type="submit" class="bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Update Listing</button>
        </div>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?><?php /**PATH C:\Users\senet\TixSwap\TixSwap\resources\views/admin/listings/edit.blade.php ENDPATH**/ ?>