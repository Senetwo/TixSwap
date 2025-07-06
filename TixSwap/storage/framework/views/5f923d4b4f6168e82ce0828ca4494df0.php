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
     <?php $__env->slot('title', null, []); ?> My Tickets <?php $__env->endSlot(); ?>
     <?php $__env->slot('navigation', null, []); ?> 
        <a href="<?php echo e(route('dashboard')); ?>" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">Home</a>
        <a href="<?php echo e(route('cart.index')); ?>" class="bg-red-700 text-white rounded-md px-3 py-2 text-sm font-medium" aria-current="page">My Tickets</a>
        <a href="<?php echo e(route('support')); ?>" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">Support</a>
     <?php $__env->endSlot(); ?>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">My Purchased Tickets</h1>

        <div class="space-y-6">
            <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="flex items-center">
                        <!-- Event Image -->
                        <div class="flex-shrink-0">
                            <img class="h-32 w-32 object-cover" src="<?php echo e(asset('storage/' . $ticket->event->image_url)); ?>" alt="<?php echo e($ticket->event->name); ?>">
                        </div>
                        <!-- Ticket Details -->
                        <div class="p-4 flex-grow flex justify-between items-center">
                            <div>
                                <h2 class="text-xl font-bold text-gray-800"><?php echo e($ticket->event->name); ?></h2>
                                <p class="text-sm text-gray-600">Seat: <?php echo e($ticket->seat_details); ?></p>
                                <p class="text-sm text-gray-500"><?php echo e(\Carbon\Carbon::parse($ticket->event->date)->format('D, M j, Y')); ?></p>
                            </div>
                            <!-- Action Button -->
                            <div class="flex-shrink-0 ml-4">
                                <a href="<?php echo e(route('tickets.purchased.show', $ticket)); ?>" class="bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 text-sm whitespace-nowrap">
                                    View Ticket & QR Code
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-16 bg-gray-50 rounded-lg">
                    <h3 class="text-xl font-bold">You haven't purchased any tickets yet.</h3>
                    <a href="<?php echo e(route('dashboard')); ?>" class="mt-4 inline-block bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700">Browse Events</a>
                </div>
            <?php endif; ?>
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
<?php endif; ?>
<?php /**PATH C:\Users\senet\TixSwap\TixSwap\resources\views/cart/index.blade.php ENDPATH**/ ?>