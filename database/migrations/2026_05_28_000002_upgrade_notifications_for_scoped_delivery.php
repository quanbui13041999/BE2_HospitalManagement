<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private function notificationsTable(): ?string
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            return Schema::hasTable('Notifications') ? 'Notifications' : null;
        }

        if (Schema::hasTable('notifications')) {
            return 'notifications';
        }

        if (Schema::hasTable('Notifications')) {
            Schema::rename('Notifications', 'notifications');
            return 'notifications';
        }

        return null;
    }

    public function up(): void
    {
        $notificationsTable = $this->notificationsTable();

        if (! $notificationsTable) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->increments('notification_id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('notif_type', 50)->nullable();
                $table->string('title', 200)->nullable();
                $table->text('content')->nullable();
                $table->unsignedInteger('ref_id')->nullable();
                $table->string('ref_type', 50)->nullable();
                $table->boolean('is_read')->default(false);
                $table->dateTime('created_at')->useCurrent();
            });

            $notificationsTable = 'notifications';
        }

        Schema::table($notificationsTable, function (Blueprint $table) use ($notificationsTable) {
            if (Schema::hasColumn($notificationsTable, 'user_id') && \Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                $table->unsignedInteger('user_id')->nullable()->change();
            }

            if (! Schema::hasColumn($notificationsTable, 'message')) {
                $table->text('message')->nullable()->after('content');
            }

            if (! Schema::hasColumn($notificationsTable, 'type')) {
                $table->string('type', 80)->nullable()->after('message');
            }

            if (! Schema::hasColumn($notificationsTable, 'target_type')) {
                $table->string('target_type', 20)->default('user')->after('type');
            }

            if (! Schema::hasColumn($notificationsTable, 'target_user_id')) {
                $table->unsignedInteger('target_user_id')->nullable()->after('target_type');
            }

            if (! Schema::hasColumn($notificationsTable, 'target_role')) {
                $table->string('target_role', 80)->nullable()->after('target_user_id');
            }

            if (! Schema::hasColumn($notificationsTable, 'related_type')) {
                $table->string('related_type', 80)->nullable()->after('target_role');
            }

            if (! Schema::hasColumn($notificationsTable, 'related_id')) {
                $table->unsignedInteger('related_id')->nullable()->after('related_type');
            }

            if (! Schema::hasColumn($notificationsTable, 'sender_id')) {
                $table->unsignedInteger('sender_id')->nullable()->after('related_id');
            }

            if (! Schema::hasColumn($notificationsTable, 'action_url')) {
                $table->string('action_url', 500)->nullable()->after('sender_id');
            }

            if (! Schema::hasColumn($notificationsTable, 'updated_at')) {
                $table->dateTime('updated_at')->nullable()->after('created_at');
            }
        });

        if (! Schema::hasTable('notification_user')) {
            Schema::create('notification_user', function (Blueprint $table) use ($notificationsTable) {
                $table->increments('id');
                $table->unsignedInteger('notification_id');
                $table->unsignedInteger('user_id');
                $table->dateTime('read_at')->nullable();
                $table->timestamps();

                $table->unique(['notification_id', 'user_id']);
                $table->index(['user_id', 'read_at']);
                $table->foreign('notification_id')
                    ->references('notification_id')->on($notificationsTable)
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('user_id')
                    ->references('user_id')->on('users')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_user');

        $notificationsTable = $this->notificationsTable();
        if (! $notificationsTable) {
            return;
        }

        Schema::table($notificationsTable, function (Blueprint $table) use ($notificationsTable) {
            foreach (['message', 'type', 'target_type', 'target_user_id', 'target_role', 'related_type', 'related_id', 'sender_id', 'action_url', 'updated_at'] as $column) {
                if (Schema::hasColumn($notificationsTable, $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
