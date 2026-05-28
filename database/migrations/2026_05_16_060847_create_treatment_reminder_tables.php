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
        // 1. Hướng dẫn điều trị tại nhà
        if (! Schema::hasTable('treatment_home_instructions')) {
            Schema::create('treatment_home_instructions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('record_id');
                $table->unsignedInteger('user_id');
                $table->string('instruction_text', 300);
                $table->string('detail', 200)->nullable();
                $table->string('icon', 50)->default('activity');
                $table->unsignedTinyInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('record_id', 'thi_record_id_index');
                $table->index('user_id', 'thi_user_id_index');
            });
        }

        // 2. Xác nhận thực hiện của bệnh nhân
        if (! Schema::hasTable('treatment_confirmations')) {
            Schema::create('treatment_confirmations', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('reminder_id');
                $table->unsignedInteger('user_id');
                $table->datetime('confirmed_at');
                $table->enum('confirm_type', ['medicine', 'instruction'])->default('medicine');
                $table->string('note', 255)->nullable();
                $table->timestamps();

                $table->index('reminder_id', 'tc_reminder_id_index');
                $table->index(['user_id', 'confirmed_at'], 'tc_user_id_date_index');
            });
        }

        // 3. Xác nhận hướng dẫn tại nhà theo ngày
        if (! Schema::hasTable('instruction_daily_checks')) {
            Schema::create('instruction_daily_checks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('instruction_id');
                $table->unsignedInteger('user_id');
                $table->date('checked_date');
                $table->boolean('is_done')->default(false);
                $table->datetime('checked_at')->nullable();
                $table->timestamps();

                $table->unique(['instruction_id', 'user_id', 'checked_date'], 'uq_instruction_user_date');
                $table->index(['user_id', 'checked_date'], 'idc_user_date_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instruction_daily_checks');
        Schema::dropIfExists('treatment_confirmations');
        Schema::dropIfExists('treatment_home_instructions');
    }
};
