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
     <?php $__env->slot('title', null, []); ?> Your Ticket: <?php echo e($ticket->event->name); ?> <?php $__env->endSlot(); ?>

    <!-- This script is for the "Save as Image" functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <div class="bg-white rounded-lg shadow-xl max-w-lg mx-auto my-8">
        <!-- This is the element that will be captured as an image -->
        <div id="ticket-to-save" class="p-8 bg-white">
            <div class="text-center">
                <img class="w-full h-48 object-cover rounded-lg mb-4" src="<?php echo e(asset('storage/' . $ticket->event->image_url)); ?>" alt="<?php echo e($ticket->event->name); ?>">
                <h1 class="text-2xl font-bold text-gray-900"><?php echo e($ticket->event->name); ?></h1>
                <p class="text-md text-gray-500 mt-2"><?php echo e(\Carbon\Carbon::parse($ticket->event->date)->format('l, F j, Y \a\t g:i A')); ?></p>
                <p class="text-md text-gray-500"><?php echo e($ticket->event->location); ?></p>
            </div>

            <div class="mt-6 border-t pt-6 text-center">
                <p class="text-lg font-semibold text-gray-800">Seat: <?php echo e($ticket->seat_details); ?></p>
                <p class="text-sm text-gray-500">Presented to: <?php echo e(auth()->user()->name); ?></p>
            </div>

            <div class="mt-6 pt-6 border-t flex justify-center">
                <?php echo $qrCode; ?>

            </div>
            <p class="text-xs text-gray-400 text-center mt-2">Validation Code: <?php echo e($ticket->validation_code); ?></p>
        </div>

        <!-- Action Buttons (These are outside the captured element) -->
        <div class="p-8 border-t bg-gray-50 rounded-b-lg">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <button id="save-ticket-btn" class="w-full block bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700">
                    Save as Image
                </button>
                <a href="<?php echo e(route('cart.index')); ?>" class="w-full block text-center bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700">
                    Back to My Tickets
                </a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('save-ticket-btn').addEventListener('click', function() {
            const ticketElement = document.getElementById('ticket-to-save');
            
            html2canvas(ticketElement, {
                // Set a white background for the captured image
                backgroundColor: '#ffffff', 
                // This helps with loading images from your server
                useCORS: true 
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'TixSwap-Ticket-<?php echo e(Str::slug($ticket->event->name)); ?>.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        });
    </script>
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
<?php /**PATH C:\Users\senet\TixSwap\TixSwap\resources\views/tickets/purchased_show.blade.php ENDPATH**/ ?>