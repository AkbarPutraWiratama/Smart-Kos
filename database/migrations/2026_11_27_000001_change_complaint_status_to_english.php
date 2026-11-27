<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Expand enum to allow both old Indonesian and new English values
        DB::statement("ALTER TABLE `complaints` MODIFY `status` ENUM('belum','dalam_perbaikan','selesai','open','in_progress','resolved') NOT NULL DEFAULT 'open'");
        DB::statement("ALTER TABLE `complaint_actions` MODIFY `status` ENUM('belum','dalam_perbaikan','selesai','open','in_progress','resolved') NOT NULL DEFAULT 'in_progress'");

        // Step 2: Migrate existing records
        DB::table('complaints')->where('status', 'belum')->update(['status' => 'open']);
        DB::table('complaints')->where('status', 'dalam_perbaikan')->update(['status' => 'in_progress']);
        DB::table('complaints')->where('status', 'selesai')->update(['status' => 'resolved']);

        DB::table('complaint_actions')->where('status', 'belum')->update(['status' => 'open']);
        DB::table('complaint_actions')->where('status', 'dalam_perbaikan')->update(['status' => 'in_progress']);
        DB::table('complaint_actions')->where('status', 'selesai')->update(['status' => 'resolved']);

        // Step 3: Restrict enum strictly to English values
        DB::statement("ALTER TABLE `complaints` MODIFY `status` ENUM('open','in_progress','resolved') NOT NULL DEFAULT 'open'");
        DB::statement("ALTER TABLE `complaint_actions` MODIFY `status` ENUM('open','in_progress','resolved') NOT NULL DEFAULT 'in_progress'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `complaints` MODIFY `status` ENUM('belum','dalam_perbaikan','selesai','open','in_progress','resolved') NOT NULL DEFAULT 'belum'");
        DB::statement("ALTER TABLE `complaint_actions` MODIFY `status` ENUM('belum','dalam_perbaikan','selesai','open','in_progress','resolved') NOT NULL DEFAULT 'dalam_perbaikan'");

        DB::table('complaints')->where('status', 'open')->update(['status' => 'belum']);
        DB::table('complaints')->where('status', 'in_progress')->update(['status' => 'dalam_perbaikan']);
        DB::table('complaints')->where('status', 'resolved')->update(['status' => 'selesai']);

        DB::table('complaint_actions')->where('status', 'open')->update(['status' => 'belum']);
        DB::table('complaint_actions')->where('status', 'in_progress')->update(['status' => 'dalam_perbaikan']);
        DB::table('complaint_actions')->where('status', 'resolved')->update(['status' => 'selesai']);

        DB::statement("ALTER TABLE `complaints` MODIFY `status` ENUM('belum','dalam_perbaikan','selesai') NOT NULL DEFAULT 'belum'");
        DB::statement("ALTER TABLE `complaint_actions` MODIFY `status` ENUM('belum','dalam_perbaikan','selesai') NOT NULL DEFAULT 'dalam_perbaikan'");
    }
};
