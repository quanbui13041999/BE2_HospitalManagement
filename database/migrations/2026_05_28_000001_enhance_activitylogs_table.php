<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('activitylogs') && !Schema::hasTable('ActivityLogs')) {
            Schema::create('activitylogs', function (Blueprint $table) {
                $table->increments('log_id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('actor_name', 150)->nullable();
                $table->string('actor_email', 150)->nullable();
                $table->string('role_name', 50)->nullable();
                $table->string('action', 255);
                $table->string('subject_type', 80)->nullable();
                $table->unsignedInteger('subject_id')->nullable();
                $table->text('description')->nullable();
                $table->json('metadata')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('status', 30)->default('success');
                $table->dateTime('created_at')->useCurrent();

                $table->foreign('user_id')->references('user_id')->on('users')->nullOnDelete();
                $table->index('created_at');
                $table->index(['role_name', 'subject_type']);
                $table->index(['user_id', 'created_at']);
            });

            return;
        }

        $tableName = Schema::hasTable('activitylogs') ? 'activitylogs' : 'ActivityLogs';

        try {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (Throwable $e) {
            //
        }

        if (DB::getDriverName() === 'mysql' && Schema::hasColumn($tableName, 'user_id')) {
            DB::statement("ALTER TABLE {$tableName} MODIFY user_id INT UNSIGNED NULL");
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'actor_name')) {
                $table->string('actor_name', 150)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn($tableName, 'actor_email')) {
                $table->string('actor_email', 150)->nullable()->after('actor_name');
            }
            if (!Schema::hasColumn($tableName, 'role_name')) {
                $table->string('role_name', 50)->nullable()->after('actor_email');
            }
            if (!Schema::hasColumn($tableName, 'subject_type')) {
                $table->string('subject_type', 80)->nullable()->after('action');
            }
            if (!Schema::hasColumn($tableName, 'subject_id')) {
                $table->unsignedInteger('subject_id')->nullable()->after('subject_type');
            }
            if (!Schema::hasColumn($tableName, 'description')) {
                $table->text('description')->nullable()->after('subject_id');
            }
            if (!Schema::hasColumn($tableName, 'metadata')) {
                $table->json('metadata')->nullable()->after('description');
            }
            if (!Schema::hasColumn($tableName, 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn($tableName, 'status')) {
                $table->string('status', 30)->default('success')->after('user_agent');
            }
        });

        try {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('user_id')->references('user_id')->on('users')->nullOnDelete();
            });
        } catch (Throwable $e) {
            //
        }

        $this->addIndexIfMissing($tableName, 'activitylogs_created_at_index', ['created_at']);
        $this->addIndexIfMissing($tableName, 'activitylogs_role_name_subject_type_index', ['role_name', 'subject_type']);
        $this->addIndexIfMissing($tableName, 'activitylogs_user_id_created_at_index', ['user_id', 'created_at']);
    }

    public function down(): void
    {
        $tableName = Schema::hasTable('activitylogs') ? 'activitylogs' : (Schema::hasTable('ActivityLogs') ? 'ActivityLogs' : null);
        if (!$tableName) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            foreach (['actor_name', 'actor_email', 'role_name', 'subject_type', 'subject_id', 'description', 'metadata', 'user_agent', 'status'] as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function addIndexIfMissing(string $tableName, string $indexName, array $columns): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $exists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();

        if (!$exists) {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                $table->index($columns);
            });
        }
    }
};
