<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospitalnews', function (Blueprint $table) {
            if (! Schema::hasColumn('hospitalnews', 'news_version')) {
                $table->unsignedBigInteger('news_version')->default(1)->after('email_sent');
            }

            if (! Schema::hasColumn('hospitalnews', 'updated_at')) {
                $table->dateTime('updated_at')->nullable()->after('created_at');
            }
        });

        DB::table('hospitalnews')
            ->whereNull('updated_at')
            ->update(['updated_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('hospitalnews', function (Blueprint $table) {
            if (Schema::hasColumn('hospitalnews', 'news_version')) {
                $table->dropColumn('news_version');
            }

            if (Schema::hasColumn('hospitalnews', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};
