<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_assignments', function (Blueprint $table) {
            $table->enum('payment_cycle', ['monthly', 'yearly'])->default('monthly')->after('rent_amount_snapshot');
            $table->unsignedTinyInteger('billing_due_day')->nullable()->after('payment_cycle');
            $table->date('next_due_date')->nullable()->after('billing_due_day');
        });
    }

    public function down(): void
    {
        Schema::table('room_assignments', function (Blueprint $table) {
            $table->dropColumn(['payment_cycle', 'billing_due_day', 'next_due_date']);
        });
    }
};
