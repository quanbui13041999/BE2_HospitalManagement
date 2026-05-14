<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('chatmessages', function (Blueprint $table) {
            $table->tinyInteger('is_ai')->default(0)->after('is_read');
        });
    }

    public function down(): void
    {
        Schema::table('chatmessages', function (Blueprint $table) {
            $table->dropColumn('is_ai');
        });
    }
};
