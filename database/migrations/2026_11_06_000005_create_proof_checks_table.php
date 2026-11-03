<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Automated reading of a listing's proof of purchase, to help reviewers.
 * Advisory only: an admin still approves or rejects every listing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proof_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, done, failed
            $table->string('verdict')->nullable(); // looks_good, needs_attention, unreadable
            $table->json('extracted')->nullable(); // what was read from the proof
            $table->json('findings')->nullable(); // mismatches / warnings for the reviewer
            $table->string('model')->nullable();
            $table->string('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proof_checks');
    }
};
