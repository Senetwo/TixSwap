<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Automatic M-Pesa B2C payouts. Payout status gains "processing"
 * (sent to M-Pesa, waiting for the result).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->string('method')->default('manual')->after('status'); // manual | mpesa_b2c
            $table->string('originator_conversation_id')->nullable()->unique()->after('reference');
            $table->string('conversation_id')->nullable()->after('originator_conversation_id');
            $table->string('failure_reason')->nullable()->after('conversation_id');
            $table->unsignedSmallInteger('attempts')->default(0)->after('failure_reason');
            $table->json('raw_result')->nullable()->after('attempts');
        });
    }

    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropUnique(['originator_conversation_id']);
            $table->dropColumn(['method', 'originator_conversation_id', 'conversation_id', 'failure_reason', 'attempts', 'raw_result']);
        });
    }
};
