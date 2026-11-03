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

Artisan::command('tixswap:send-payouts', function (App\Services\PayoutService $payouts) {
    if (! App\Services\PayoutService::enabled()) {
        $this->warn('M-Pesa payouts are switched off (MPESA_B2C_ENABLED).');

        return;
    }
    [$sent, $failed] = $payouts->sendAllPending();
    $this->info("Sent {$sent} payout(s); {$failed} failed.");
})->purpose('Send pending seller payouts via M-Pesa B2C');

Artisan::command('tixswap:check-proofs {--all : Re-check listings that were already checked}', function (App\Services\ProofCheck\ProofCheckService $checks) {
    if (! App\Services\ProofCheck\ProofCheckService::enabled()) {
        $this->warn('The AI proof check is switched off (AI_PROOF_DRIVER).');

        return;
    }
    $tickets = App\Models\Ticket::where('status', 'pending')
        ->when(! $this->option('all'), fn ($q) => $q->whereDoesntHave('proofCheck', fn ($c) => $c->where('status', 'done')))
        ->get();
    foreach ($tickets as $ticket) {
        $result = $checks->check($ticket);
        $this->line("#{$ticket->id}: ".($result?->verdict ?? $result?->error ?? 'skipped'));
    }
    $this->info('Checked '.$tickets->count().' listing(s).');
})->purpose('Run the AI proof-of-purchase check on listings waiting for review');

Artisan::command('tixswap:make-admin {email}', function (string $email) {
    $user = App\Models\User::where('email', $email)->first();

    if (! $user) {
        $this->error("No account with the email {$email}. Ask them to sign up first.");

        return 1;
    }

    $user->forceFill(['role' => 'admin'])->save();
    $this->info("{$user->name} ({$email}) is now an admin.");
})->purpose('Make an existing account an admin (admins cannot be created from the website)');

// Requires the scheduler on the server: * * * * * php artisan schedule:run
Schedule::command('tixswap:expire-reservations')->everyMinute()->withoutOverlapping();
Schedule::command('tixswap:release-escrow')->hourly()->withoutOverlapping();
Schedule::command('tixswap:send-payouts')->hourly()->withoutOverlapping()
    ->when(fn () => config('services.mpesa.b2c_enabled') && config('services.mpesa.b2c_auto'));

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
