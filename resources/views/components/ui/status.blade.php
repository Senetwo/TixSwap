@props(['status', 'type' => 'ticket']) {{-- type: ticket | order | dispute | payout --}}
@php
    // One vocabulary for statuses across the whole product.
    $map = [
        'ticket' => [
            'pending' => ['In review', 'amber'],
            'approved' => ['Live', 'green'],
            'reserved' => ['Reserved', 'blue'],
            'sold' => ['Sold', 'gray'],
            'rejected' => ['Rejected', 'red'],
        ],
        'order' => [
            'pending' => ['Awaiting payment', 'amber'],
            'paid' => ['Paid', 'green'],
            'failed' => ['Not completed', 'red'],
            'expired' => ['Expired', 'gray'],
            'needs_refund' => ['Refund due', 'amber'],
            'refunded' => ['Refunded', 'gray'],
        ],
        'dispute' => [
            'open' => ['Under review', 'amber'],
            'refunded' => ['Refunded to buyer', 'green'],
            'released' => ['Released to seller', 'gray'],
        ],
        'payout' => [
            'pending' => ['To send', 'amber'],
            'paid' => ['Paid', 'green'],
            'failed' => ['Failed', 'red'],
        ],
    ];
    [$label, $tone] = $map[$type][$status] ?? [ucfirst(str_replace('_', ' ', $status)), 'gray'];
@endphp
<x-ui.badge :tone="$tone" dot {{ $attributes }}>{{ $label }}</x-ui.badge>
