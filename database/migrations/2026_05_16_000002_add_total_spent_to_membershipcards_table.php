<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('membershipcards', function (Blueprint $table) {
            if (! Schema::hasColumn('membershipcards', 'total_spent')) {
                $table->decimal('total_spent', 12, 2)
                    ->default(0)
                    ->after('points');
            }
        });
    }

    public function down(): void
    {
        Schema::table('membershipcards', function (Blueprint $table) {
            if (Schema::hasColumn('membershipcards', 'total_spent')) {
                $table->dropColumn('total_spent');
            }
        });
    }
};
