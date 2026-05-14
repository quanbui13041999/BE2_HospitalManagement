<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void {
    Schema::table('Appointments', function (Blueprint $table) {
        $table->dateTime('appointment_timeEnd')->nullable()->after('appointment_time');
    });
}

public function down(): void {
    Schema::table('Appointments', function (Blueprint $table) {
        $table->dropColumn('appointment_timeEnd');
    });
}
};
