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
     <?php $__env->slot('title', null, []); ?> Buy Ticket for <?php echo e($ticket->event->name); ?> <?php $__env->endSlot(); ?>

    <div class="bg-white rounded-lg shadow-xl max-w-4xl mx-auto p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Ticket Image -->
            <div>
                <img class="rounded-lg object-cover w-full" src="<?php echo e(asset('storage/' . $ticket->event->image_url)); ?>" alt="<?php echo e($ticket->event->name); ?>">
            </div>

            <!-- Ticket and Payment Details -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e($ticket->event->name); ?></h1>
                <p class="text-md text-gray-500 mt-2"><?php echo e(\Carbon\Carbon::parse($ticket->event->date)->format('l, F j, Y \a\t g:i A')); ?></p>
                <p class="text-md text-gray-500"><?php echo e($ticket->event->location); ?></p>
                <p class="mt-4 text-lg font-semibold text-gray-800">Seat: <?php echo e($ticket->seat_details); ?></p>

                <div class="mt-6 border-t pt-4">
                    <div class="flex justify-between items-center text-xl font-bold">
                        <span>Total Price:</span>
                        <span>KES <?php echo e(number_format($ticket->price)); ?></span>
                    </div>
                </div>

                <!-- Payment Form with M-Pesa mock-up -->
                <form id="payment-form" action="<?php echo e(route('pay')); ?>" method="POST" class="mt-8 space-y-4">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="ticket_id" value="<?php echo e($ticket->id); ?>">
                    <div>
                       <label for="phone_number" class="block text-sm font-medium text-gray-700">M-Pesa Phone Number</label>
                       <div class="mt-1 relative rounded-md shadow-sm">
                           <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm">+254</span></div>
                           <input type="text" name="phone_number" id="phone_number" placeholder="712 345 678" required class="pl-12 block w-full rounded-md border-gray-300">
                       </div>
                    </div>
                    <button type="submit" id="pay-button" class="w-full bg-green-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-green-700">
                        Pay KES <?php echo e(number_format($ticket->price)); ?>

                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- M-Pesa PIN Modal -->
    <div id="mpesa-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center" style="display: none;">
        <div class="bg-white rounded-lg shadow-xl p-8 max-w-sm w-full">
            <h3 class="text-lg font-bold text-center">Enter M-Pesa PIN</h3>
            <p class="text-center text-sm text-gray-600 mt-2">to pay KES <?php echo e(number_format($ticket->price)); ?> to TixSwap</p>
            <div class="mt-6">
                <input type="password" id="mpesa-pin" maxlength="4" class="w-full text-center text-2xl tracking-[1em] p-2 border-2 border-gray-300 rounded-md">
            </div>
            <div class="mt-6 grid grid-cols-2 gap-4">
                <button id="cancel-payment" class="w-full bg-gray-200 py-2 rounded-lg font-semibold hover:bg-gray-300">Cancel</button>
                <button id="confirm-payment" class="w-full bg-green-600 text-white py-2 rounded-lg font-semibold hover:bg-green-700">OK</button>
            </div>
        </div>
    </div>

    <script>
        const paymentForm = document.getElementById('payment-form');
        const mpesaModal = document.getElementById('mpesa-modal');
        const cancelPaymentButton = document.getElementById('cancel-payment');
        const confirmPaymentButton = document.getElementById('confirm-payment');

        paymentForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the form from submitting immediately
            mpesaModal.style.display = 'flex'; // Show the PIN pop-up
        });

        cancelPaymentButton.addEventListener('click', function() {
            mpesaModal.style.display = 'none'; // Hide the pop-up
        });

        confirmPaymentButton.addEventListener('click', function() {
            // When user clicks "OK", submit the original form to the backend
            paymentForm.submit();
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
<?php /**PATH C:\Users\senet\TixSwap\TixSwap\resources\views/tickets/show.blade.php ENDPATH**/ ?>