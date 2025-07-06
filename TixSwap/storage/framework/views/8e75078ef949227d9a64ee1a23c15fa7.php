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
     <?php $__env->slot('title', null, []); ?> Admin Dashboard <?php $__env->endSlot(); ?>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Sales Overview</h3>
            <canvas id="salesChart"></canvas>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Tickets by Status</h3>
            <canvas id="ticketsChart" class="mx-auto" style="max-width: 250px;"></canvas>
        </div>
    </div>

    <!-- Current Listings Table -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="font-semibold text-lg text-gray-800 mb-4">Current Listings</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $allTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?php echo e($ticket->event->name); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500"><?php echo e($ticket->seller->name); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">KES <?php echo e(number_format($ticket->price)); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                 <span class="px-3 py-1 text-xs font-bold rounded-full text-white
                                    <?php if($ticket->status == 'approved'): ?> bg-green-500
                                    <?php elseif($ticket->status == 'pending'): ?> bg-yellow-500
                                    <?php else: ?> bg-red-500 <?php endif; ?>">
                                    <?php echo e(ucfirst($ticket->status)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="<?php echo e(route('admin.tickets.pending')); ?>" class="mt-4 inline-block bg-green-500 text-white font-bold py-2 px-4 rounded hover:bg-green-600">Review Tickets</a>
                                <a href="<?php echo e(route('admin.listings.edit', $ticket)); ?>" class="bg-red-600 text-white py-1 px-3 rounded-md font-semibold hover:bg-red-700 text-xs">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">No tickets have been listed yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Chart.js script for Sales Overview
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: ['Total Sales'],
                datasets: [{
                    label: 'Sales (KES)',
                    data: [<?php echo e($stats['totalSalesValue'] ?? 0); ?>],
                    backgroundColor: 'rgba(220, 38, 38, 0.8)',
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        // Chart.js script for Tickets Sold
        const ticketsCtx = document.getElementById('ticketsChart').getContext('2d');
        new Chart(ticketsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Sold', 'Available', 'Pending'],
                datasets: [{
                    label: 'Tickets',
                    data: [<?php echo e($stats['ticketsSold']); ?>, <?php echo e($stats['activeListings']); ?>, <?php echo e($stats['pendingTickets']); ?>],
                    backgroundColor: ['rgb(220, 38, 38)','rgb(22, 163, 74)','rgb(255, 205, 86)'],
                    hoverOffset: 4
                }]
            }
        });
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\Users\senet\TixSwap\TixSwap\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>