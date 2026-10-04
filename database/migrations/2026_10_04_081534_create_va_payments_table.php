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
        Schema::create('va_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('va_account_id')->constrained('midtrans_va_accounts')->cascadeOnDelete();
            $table->foreignId('room_assignment_id')->constrained('room_assignments')->cascadeOnDelete();
            $table->string('order_id')->unique();
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('transaction_status');
            $table->timestamp('transaction_time')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('va_account_id');
            $table->index('room_assignment_id');
            $table->index('transaction_status');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('va_payments');
    }
};
