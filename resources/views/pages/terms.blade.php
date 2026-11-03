@php($fee = rtrim(rtrim(number_format(config('services.tixswap.commission_percent'), 1), '0'), '.'))
<x-tixswap-layout title="Terms and Conditions" width="max-w-3xl">
    <x-ui.page-header title="Terms and Conditions" description="Last updated: November 3, 2026" />
    <x-ui.card padding="p-6 sm:p-10">
        @component('pages._prose')
            <p>Please read these Terms and Conditions ("Terms") carefully before using the TixSwap website (the "Service") operated by TixSwap ("us", "we", or "our").</p>

            <h2>Accounts</h2>
            <p>When you create an account with us, you must provide information that is accurate, complete, and current at all times. Failure to do so is a breach of the Terms and may result in immediate termination of your account.</p>

            <h2>Ticket listings</h2>
            <p>Sellers are responsible for the accuracy of the tickets they list and must hold a valid ticket they are entitled to resell. Every listing is reviewed by our team before it goes on sale, and sellers must provide proof of purchase. We may reject or remove any listing that we believe to be fraudulent, inaccurate, duplicated, or in breach of our policies.</p>

            <h2>Purchases and payment</h2>
            <p>Payments are made through M-Pesa. When you buy a ticket, it is reserved for you for a short time while you approve the payment on your phone. We will never ask for your M-Pesa PIN on the Service.</p>

            <h2>Escrow</h2>
            <p>We hold the buyer's payment until after the event. Once the escrow period has ended and no problem has been reported, the seller is paid to their registered M-Pesa number, less our service fee ({{ $fee }}% of the sale price).</p>

            <h2>Problems and refunds</h2>
            <p>If a ticket is not valid, buyers may report a problem from their ticket page before the escrow period ends. While a report is under review, the seller's payment is held. If we find that the ticket was not valid, the buyer is refunded and the seller is not paid. Our decision on a reported problem is final.</p>
        @endcomponent
    </x-ui.card>
</x-tixswap-layout>
