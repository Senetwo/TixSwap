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
     <?php $__env->slot('title', null, []); ?> Payment Successful <?php $__env->endSlot(); ?>

    <!-- This script is for the "Save as Image" functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <!-- Background page content -->
    <div class="text-center py-16">
        <h1 class="text-3xl font-bold text-gray-800">Processing your ticket...</h1>
        <p class="text-gray-500 mt-2">Your ticket details will appear in a moment.</p>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md m-4 flex flex-col" style="max-height: 90vh;">
            <!-- Modal Header -->
            <div class="p-8 text-center border-b">
                 <div class="mx-auto bg-green-100 rounded-full h-20 w-20 flex items-center justify-center">
                    <svg class="h-12 w-12 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mt-6">Payment Successful!</h2>
                <p class="text-gray-600 mt-2">Your ticket has been generated. Keep it safe!</p>
            </div>

            <!-- Scrollable Ticket Details -->
            <div class="p-8 overflow-y-auto" id="ticket-to-save">
                <div class="bg-gray-50 p-6 rounded-lg border">
                    
                    <img class="w-full h-48 object-cover rounded-lg mb-4" src="<?php echo e(asset('storage/' . $ticket->event->image_url)); ?>" alt="<?php echo e($ticket->event->name); ?>">

                    <div class="text-center">
                        <h3 class="text-xl font-bold"><?php echo e($ticket->event->name); ?></h3>
                        <p class="text-sm text-gray-500"><?php echo e(\Carbon\Carbon::parse($ticket->event->date)->format('l, F j, Y')); ?></p>
                        <p class="text-sm text-gray-500 mb-2"><?php echo e($ticket->event->location); ?></p>
                        
                        <p class="text-lg font-semibold text-gray-800">Seat: <?php echo e($ticket->seat_details); ?></p>
                    </div>

                    <div class="mt-4 pt-4 border-t flex justify-center">
                        <?php echo $qrCode; ?>

                    </div>
                    <p class="text-xs text-gray-400 text-center mt-2">Validation Code: <?php echo e($ticket->validation_code); ?></p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="p-8 border-t bg-gray-50 rounded-b-2xl">
                 <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button id="save-ticket-btn" class="w-full block bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700">
                        Save as Image
                    </button>
                    <a href="<?php echo e(route('cart.index')); ?>" class="w-full block text-center bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700">
                        View All My Tickets
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            const modal = document.getElementById('successModal');
            const saveButton = document.getElementById('save-ticket-btn');
            const ticketElement = document.getElementById('ticket-to-save');

            // Show the modal with an animation
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            // A small fix for the animation selector
            modal.querySelector('.bg-white')?.classList.add('scale-100');


            // Save ticket button functionality
            saveButton.addEventListener('click', function() {
                html2canvas(ticketElement, {
                    backgroundColor: '#f9fafb', // Match the bg-gray-50 color
                    useCORS: true // Important for loading cross-origin images
                }).then(canvas => {
                    const link = document.createElement('a');
                    link.download = 'TixSwap-Ticket-<?php echo e($ticket->event->name); ?>.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                });
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
<?php /**PATH C:\Users\senet\TixSwap\TixSwap\resources\views/payment/success.blade.php ENDPATH**/ ?>