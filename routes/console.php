<?php

use App\Services\CheckoutService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tixswap:expire-reservations', function (CheckoutService $checkout) {
    $this->info('Settled '.$checkout->expireStale().' unpaid order(s).');
})->purpose('Release tickets whose M-Pesa payment window has run out');

Artisan::command('tixswap:release-escrow', function (CheckoutService $checkout) {
    $this->info('Released escrow for '.$checkout->releaseDueEscrow().' order(s).');
})->purpose('Release escrow after events and create seller payouts');

// Requires the scheduler on the server: * * * * * php artisan schedule:run
Schedule::command('tixswap:expire-reservations')->everyMinute()->withoutOverlapping();
Schedule::command('tixswap:release-escrow')->hourly()->withoutOverlapping();

Artisan::command('tixswap:hash-proofs', function () {
    $count = 0;

    App\Models\Ticket::whereNull('proof_hash')->whereNotNull('purchase_proof')->each(function ($ticket) use (&$count) {
        // Older proofs live on the public disk, newer ones on the private one.
        foreach (['local', 'public'] as $disk) {
            $storage = Illuminate\Support\Facades\Storage::disk($disk);
            if ($storage->exists($ticket->purchase_proof)) {
                $ticket->forceFill(['proof_hash' => hash_file('sha256', $storage->path($ticket->purchase_proof))])->save();
                $count++;

                return;
            }
        }
    });

    $this->info("Fingerprinted {$count} existing proof(s) of purchase.");
})->purpose('Backfill proof-of-purchase fingerprints for duplicate detection');
