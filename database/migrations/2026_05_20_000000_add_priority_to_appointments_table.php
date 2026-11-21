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
        if (! Schema::hasTable('appointments')) {
            return;
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('appointments')) {
            return;
        }

        if (Schema::hasColumn('appointments', 'priority_type')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('priority_type');
            });
        }

        if (Schema::hasColumn('appointments', 'is_priority')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('is_priority');
            });
        }
    }
};
