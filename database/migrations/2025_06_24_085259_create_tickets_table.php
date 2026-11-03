<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Foreign Keys for relationships
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('buyer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');

            // Ticket Details
            $table->string('seat_details');
            $table->decimal('price', 8, 2);
            $table->string('original_vendor')->nullable(); // <-- THIS IS THE FIX
            $table->string('purchase_proof')->nullable();

            // Ticket Status & Verification
            $table->string('status')->default('pending');
            $table->uuid('validation_code')->nullable()->unique();

            // Admin & Timestamps
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
