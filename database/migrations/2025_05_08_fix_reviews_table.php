<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('Reviews', function (Blueprint $table) {
            // Add missing updated_at column
            if (!Schema::hasColumn('Reviews', 'updated_at')) {
                $table->dateTime('updated_at')->nullable()->after('created_at');
            }

            // Add missing doctor_reply_updated_at column
            if (!Schema::hasColumn('Reviews', 'doctor_reply_updated_at')) {
                $table->dateTime('doctor_reply_updated_at')->nullable()->after('doctor_reply');
            }

            // Fix comment length (from 500 to 1000)
            if (Schema::hasColumn('Reviews', 'comment')) {
                $table->text('comment')->nullable()->change();
            }

            // Fix doctor_reply length (from 500 to 1000)
            if (Schema::hasColumn('Reviews', 'doctor_reply')) {
                $table->text('doctor_reply')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('Reviews', function (Blueprint $table) {
            if (Schema::hasColumn('Reviews', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
            if (Schema::hasColumn('Reviews', 'doctor_reply_updated_at')) {
                $table->dropColumn('doctor_reply_updated_at');
            }
        });
    }
};
