<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fingerprints for spotting the same ticket listed more than once:
 * a SHA-256 of the proof-of-purchase file, and the ticket/order number
 * from the receipt (normalised: upper case, no spaces or dashes).
 * Backfill existing listings with: php artisan tixswap:hash-proofs
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('proof_hash', 64)->nullable()->index()->after('purchase_proof');
            $table->string('ticket_reference', 100)->nullable()->index()->after('proof_hash');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['proof_hash']);
            $table->dropIndex(['ticket_reference']);
            $table->dropColumn(['proof_hash', 'ticket_reference']);
        });
    }
};
