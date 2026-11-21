<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('reviews', 'doctor_reply_updated_at')) {
                $table->dateTime('doctor_reply_updated_at')->nullable()->after('created_at');
            }

            if (! Schema::hasColumn('reviews', 'updated_at')) {
                $table->dateTime('updated_at')->nullable()->after('doctor_reply_updated_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'updated_at')) {
                $table->dropColumn('updated_at');
            }

            if (Schema::hasColumn('reviews', 'doctor_reply_updated_at')) {
                $table->dropColumn('doctor_reply_updated_at');
            }
        });
    }
};
