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
     <?php $__env->slot('title', null, []); ?> List a New Ticket <?php $__env->endSlot(); ?>
     <?php $__env->slot('navigation', null, []); ?> 
        <a href="<?php echo e(route('seller.dashboard')); ?>" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">Dashboard</a>
        <a href="<?php echo e(route('seller.tickets.create')); ?>" class="bg-red-700 text-white rounded-md px-3 py-2 text-sm font-medium" aria-current="page">List a Ticket</a>
     <?php $__env->endSlot(); ?>

    <div class="bg-white rounded-lg shadow-md p-8 max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">List a New Ticket for Resale</h1>

        <!-- Session & Validation Error Messages -->
        <?php if(session('error')): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?php echo e(session('error')); ?></p>
            </div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">Please fix the following errors:</p>
                <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>- <?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('seller.tickets.store')); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="event_name" class="block text-sm font-medium text-gray-700">Event Name</label>
                    <input type="text" name="event_name" id="event_name" value="<?php echo e(old('event_name')); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700">Event Location / Venue</label>
                    <input type="text" name="location" id="location" value="<?php echo e(old('location')); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700">Event Date & Time</label>
                <input type="datetime-local" name="date" id="date" value="<?php echo e(old('date')); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Your Selling Price (KES)</label>
                    <input type="number" name="price" id="price" value="<?php echo e(old('price')); ?>" required min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="seat_details" class="block text-sm font-medium text-gray-700">Seat Details</label>
                    <input type="text" name="seat_details" id="seat_details" value="<?php echo e(old('seat_details')); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>
            <div>
                <label for="original_vendor" class="block text-sm font-medium text-gray-700">Original Ticket Vendor</label>
                <input type="text" name="original_vendor" id="original_vendor" value="<?php echo e(old('original_vendor')); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="event_image" class="block text-sm font-medium text-gray-700">Event Cover Image</label>
                    <input type="file" name="event_image" id="event_image" required class="mt-1 block w-full">
                </div>
                <div>
                    <label for="purchase_proof" class="block text-sm font-medium text-gray-700">Proof of Purchase</label>
                    <input type="file" name="purchase_proof" id="purchase_proof" required class="mt-1 block w-full">
                </div>
            </div>
            <div class="pt-6">
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-red-600 hover:bg-red-700">
                    List My Ticket
                </button>
            </div>
        </form>
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
<?php endif; ?><?php /**PATH C:\Users\senet\TixSwap\TixSwap\resources\views/seller/tickets/create.blade.php ENDPATH**/ ?>