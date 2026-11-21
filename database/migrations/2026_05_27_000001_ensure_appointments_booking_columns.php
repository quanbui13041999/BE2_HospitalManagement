<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('appointments')) {
            return;
        }

        if (! Schema::hasColumn('appointments', 'appointment_timeEnd')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dateTime('appointment_timeEnd')->nullable()->after('appointment_time');
            });
        }

        if (
            Schema::hasColumn('appointments', 'appointment_time_end')
            && Schema::hasColumn('appointments', 'appointment_timeEnd')
        ) {
            DB::table('appointments')
                ->whereNull('appointment_timeEnd')
                ->update(['appointment_timeEnd' => DB::raw('appointment_time_end')]);
        }

        if (! Schema::hasColumn('appointments', 'is_priority')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->boolean('is_priority')->default(false)->after('status');
            });
        }

        if (! Schema::hasColumn('appointments', 'priority_type')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('priority_type')->nullable()->after('is_priority');
            });
        }
    }

    public function down(): void
    {
        // This migration only makes existing databases match the app schema.
    }
};
