<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('chatrooms') || !Schema::hasColumn('chatrooms', 'doctor_id')) {
            return;
        }

        DB::statement('ALTER TABLE chatrooms MODIFY doctor_id INT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (!Schema::hasTable('chatrooms') || !Schema::hasColumn('chatrooms', 'doctor_id')) {
            return;
        }

        DB::statement('ALTER TABLE chatrooms MODIFY doctor_id INT UNSIGNED NOT NULL');
    }
};
