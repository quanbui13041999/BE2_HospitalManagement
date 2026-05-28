<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $dumpPath = base_path('HospitalBookingDB_v.sql');

        if (! is_file($dumpPath)) {
            throw new RuntimeException("SQL dump not found: {$dumpPath}");
        }

        $sql = file_get_contents($dumpPath);

        if ($sql === false) {
            throw new RuntimeException("Unable to read SQL dump: {$dumpPath}");
        }

        DB::statement('DROP VIEW IF EXISTS v_doctorratings');
        DB::statement('DROP TABLE IF EXISTS v_doctorratings');
        DB::connection()->getPdo()->exec('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($this->statements($sql) as $statement) {
                if ($this->shouldSkip($statement)) {
                    continue;
                }

                $statement = $this->makeSafe($statement);

                DB::connection()->getPdo()->exec($statement);
            }
        } finally {
            DB::connection()->getPdo()->exec('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        // The imported dump is a baseline dataset. Use migrate:fresh to rebuild it.
    }

    private function shouldSkip(string $statement): bool
    {
        $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $statement)));

        if ($normalized === '') {
            return true;
        }

        return str_starts_with($normalized, 'create database')
            || str_starts_with($normalized, 'use ')
            || str_starts_with($normalized, 'start transaction')
            || str_starts_with($normalized, 'commit')
            || str_starts_with($normalized, 'drop table if exists')
            || str_starts_with($normalized, 'drop view if exists')
            || str_starts_with($normalized, 'create table if not exists `migrations`')
            || str_starts_with($normalized, 'create table if not exists `v_doctorratings`')
            || str_starts_with($normalized, 'insert into `migrations`');
    }

    private function makeSafe(string $statement): string
    {
        $statement = preg_replace('/^insert\s+into\s+/i', 'INSERT IGNORE INTO ', $statement, 1) ?? $statement;
        $statement = preg_replace('/\s+DEFINER=`[^`]+`@`[^`]+`/i', '', $statement) ?? $statement;

        return $statement;
    }

    /**
     * Split a phpMyAdmin dump into executable statements without breaking on
     * semicolons that appear inside quoted strings.
     *
     * @return array<int, string>
     */
    private function statements(string $sql): array
    {
        $statements = [];
        $current = '';
        $quote = null;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

            if ($quote === null && $char === '-' && $next === '-') {
                while ($i < $length && $sql[$i] !== "\n") {
                    $i++;
                }
                continue;
            }

            if ($quote === null && $char === '/' && $next === '*') {
                $end = strpos($sql, '*/', $i + 2);
                if ($end === false) {
                    break;
                }
                $i = $end + 1;
                continue;
            }

            if (($char === "'" || $char === '"') && ($i === 0 || $sql[$i - 1] !== '\\')) {
                $quote = $quote === $char ? null : ($quote ?? $char);
            }

            if ($char === ';' && $quote === null) {
                $statement = trim($current);
                if ($statement !== '') {
                    $statements[] = $statement;
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        $statement = trim($current);
        if ($statement !== '') {
            $statements[] = $statement;
        }

        return $statements;
    }
};
