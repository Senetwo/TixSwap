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
     <?php $__env->slot('title', null, []); ?> Buyer Dashboard <?php $__env->endSlot(); ?>
     <?php $__env->slot('navigation', null, []); ?> 
        <a href="<?php echo e(route('dashboard')); ?>" class="bg-red-700 text-white rounded-md px-3 py-2 text-sm font-medium" aria-current="page">Home</a>
        <a href="<?php echo e(route('cart.index')); ?>" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">My Tickets</a>
        <a href="<?php echo e(route('support')); ?>" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">Support</a>
     <?php $__env->endSlot(); ?>

    <!-- Search Bar -->
    <div class="mb-8">
        <form action="<?php echo e(route('dashboard')); ?>" method="GET" class="flex gap-2">
            <input type="text" name="search" placeholder="Search for events by name or location..." class="w-full p-3 border border-gray-300 rounded-lg" value="<?php echo e(request('search')); ?>">
            <button type="submit" class="bg-red-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-red-700">Search</button>
        </form>
    </div>

    <!-- All Tickets Section -->
    <div>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">All Available Tickets</h2>
        </div>

        <?php if($tickets->isEmpty()): ?>
            <div class="text-center py-16 bg-gray-50 rounded-lg">
                <h3 class="text-xl font-bold">No tickets found</h3>
                <p class="text-gray-500 mt-2">Try adjusting your search criteria.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <img class="h-48 w-full object-cover" src="<?php echo e(asset('storage/' . $ticket->event->image_url)); ?>" alt="<?php echo e($ticket->event->name); ?>">
                    <div class="p-6">
                        <h3 class="text-xl font-bold"><?php echo e($ticket->event->name); ?></h3>
                        <p class="text-sm text-gray-500"><?php echo e(\Carbon\Carbon::parse($ticket->event->date)->format('D, M j, Y')); ?> - <?php echo e($ticket->event->location); ?></p>
                        <p class="mt-2 text-lg font-semibold text-gray-800">KES <?php echo e(number_format($ticket->price)); ?></p>
                        <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="mt-4 w-full block text-center bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700">Buy Now</a>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
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
<?php endif; ?><?php /**PATH C:\Users\senet\TixSwap\TixSwap\resources\views/dashboard.blade.php ENDPATH**/ ?>