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
        Schema::table('vaccines', function (Blueprint $table) {
            if (!Schema::hasColumn('vaccines', 'stock_quantity')) {
                $table->integer('stock_quantity')->default(0)->after('manufacturer');
            }
            if (!Schema::hasColumn('vaccines', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('stock_quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccines', function (Blueprint $table) {
            if (Schema::hasColumn('vaccines', 'stock_quantity')) {
                $table->dropColumn('stock_quantity');
            }
            if (Schema::hasColumn('vaccines', 'expiry_date')) {
                $table->dropColumn('expiry_date');
            }
        });
    }
};
