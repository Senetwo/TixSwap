<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2: real M-Pesa checkout with escrow.
 *
 * - tickets: a short reservation while the buyer approves the STK prompt
 * - orders: payment + escrow lifecycle and the commission split
 * - payments: one row per STK push attempt (receipt filled in on success)
 * - payouts: what TixSwap owes each seller once escrow is released
 * - users.payout_phone: the M-Pesa number sellers are paid to
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Where sellers receive their payouts (2547XXXXXXXX).
            $table->string('payout_phone', 20)->nullable()->after('role');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('reserved_until')->nullable()->after('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('phone_number', 20)->nullable()->after('amount');
            $table->decimal('commission', 10, 2)->default(0)->after('phone_number');
            $table->decimal('seller_amount', 10, 2)->default(0)->after('commission');
            $table->timestamp('expires_at')->nullable()->after('status');
            $table->timestamp('paid_at')->nullable()->after('expires_at');
            $table->string('escrow_status')->nullable()->after('paid_at'); // held, released, refunded
            $table->timestamp('escrow_release_at')->nullable()->after('escrow_status');
            $table->index('status');
            $table->index(['escrow_status', 'escrow_release_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            // M-Pesa receipt number; only known once the payment succeeds.
            $table->string('transaction_id')->nullable()->change();
            $table->string('status')->default('pending')->after('payment_method'); // pending, paid, failed
            $table->string('checkout_request_id')->nullable()->unique()->after('status');
            $table->string('merchant_request_id')->nullable()->after('checkout_request_id');
            $table->string('phone_number', 20)->nullable()->after('merchant_request_id');
            $table->integer('result_code')->nullable()->after('phone_number');
            $table->string('result_desc')->nullable()->after('result_code');
            $table->json('raw_callback')->nullable()->after('result_desc');
            $table->timestamp('paid_at')->nullable()->after('raw_callback');
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, paid, failed
            $table->string('reference')->nullable(); // M-Pesa B2C receipt or manual reference
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['checkout_request_id']);
            $table->dropColumn([
                'status', 'checkout_request_id', 'merchant_request_id', 'phone_number',
                'result_code', 'result_desc', 'raw_callback', 'paid_at',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['escrow_status', 'escrow_release_at']);
            $table->dropColumn([
                'phone_number', 'commission', 'seller_amount', 'expires_at',
                'paid_at', 'escrow_status', 'escrow_release_at',
            ]);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('reserved_until');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('payout_phone');
        });
    }
};
