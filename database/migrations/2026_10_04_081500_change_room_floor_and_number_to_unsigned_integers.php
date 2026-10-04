<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('rooms')
            ->where('floor_name', 'like', 'Lantai %')
            ->update([
                'floor_name' => DB::raw("CAST(SUBSTRING_INDEX(floor_name, ' ', -1) AS UNSIGNED)"),
            ]);

        $hasInvalidValues = DB::table('rooms')
            ->whereRaw("floor_name NOT REGEXP '^[0-9]+$' OR room_number NOT REGEXP '^[0-9]+$'")
            ->exists();

        if ($hasInvalidValues) {
            throw new RuntimeException('Rooms contain non-numeric floor_name or room_number values.');
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedInteger('floor_name')->change();
            $table->unsignedInteger('room_number')->change();
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('floor_name')->change();
            $table->string('room_number')->change();
        });
    }
};