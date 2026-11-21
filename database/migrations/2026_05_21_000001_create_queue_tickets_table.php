<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('queue_tickets')) {
            return;
        }

        Schema::create('queue_tickets', function (Blueprint $table) {
            $table->id('ticket_id');
            $table->unsignedInteger('appointment_id')->nullable(); // null nếu walk-in
            $table->unsignedInteger('schedule_id');               // ca khám hôm nay
            $table->unsignedInteger('user_id')->nullable();       // bệnh nhân (nếu có tài khoản)
            $table->string('patient_name', 100);                  // tên hiển thị
            $table->string('patient_phone', 15)->nullable();
            $table->string('patient_email', 100)->nullable();
            $table->date('queue_date');                           // ngày xếp hàng
            $table->unsignedSmallInteger('queue_number');         // số thứ tự trong ngày+ca
            $table->enum('priority', ['normal', 'elderly', 'disabled', 'emergency'])->default('normal');
            // normal=Thường, elderly=Cao tuổi (≥60), disabled=Khuyết tật, emergency=Cấp cứu
            $table->enum('status', ['waiting', 'calling', 'in_progress', 'completed', 'skipped', 'cancelled'])
                  ->default('waiting');
            $table->unsignedSmallInteger('priority_sort');        // 1=emergency,2=disabled,3=elderly,4=normal
            $table->datetime('checkin_time')->useCurrent();
            $table->datetime('called_at')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->unsignedSmallInteger('est_wait_minutes')->nullable();
            $table->string('notes', 255)->nullable();
            $table->unsignedInteger('served_by')->nullable();     // bác sĩ/lễ tân xử lý
            $table->timestamps();
            
            $table->index('appointment_id');
            $table->index('schedule_id');
            $table->index('user_id');
            $table->index('served_by');
            $table->index(['queue_date', 'schedule_id', 'status'], 'idx_qt_date_sched_status');
            $table->index(['queue_date', 'schedule_id', 'priority_sort', 'queue_number'], 'idx_qt_date_sched_priority_num');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_tickets');
    }
};
