<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use IRPayment\Enums\PaymentChannel;
use IRPayment\Enums\PaymentStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->morphs('paymentable');
            $table->unsignedSmallInteger('code');
            $table->enum('payment_channel', array_column(PaymentChannel::cases(), 'value'))->default('offline');
            $table->string('payment_method')->nullable();
            $table->string('message')->nullable();
            $table->string('card_hash', 64)->nullable();
            $table->string('card_mask', 20);
            $table->string('authority_key', 100)->unique()->nullable();
            $table->string('reference_id', 20)->unique();
            $table->unsignedBigInteger('amount');
            $table->enum('status', array_column(PaymentStatus::cases(), 'value'))->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
