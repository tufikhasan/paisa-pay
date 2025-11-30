<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique()->comment('Unique transaction ID from payment gateway');
            $table->decimal('amount', 10, 2)->comment('Transaction amount');
            $table->unsignedBigInteger('user_id')->comment('User who made the transaction');
            $table->string('type')->comment('Transaction type: subscription, one-time, etc.');
            $table->string('payment_gateway')->comment('Payment gateway used: stripe, paypal, bkash');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->json('metadata')->nullable()->comment('Additional transaction data');
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('payment_gateway');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
