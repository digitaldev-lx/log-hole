<?php

namespace DigitalDevLx\LogHole\Tests\Helpers;

use Illuminate\Support\Facades\DB;

trait LogSeeder
{
    protected function seedLogs(int $count = 5, string $level = 'INFO'): void
    {
        $table = config('log-hole.database.table', 'logs_hole');

        for ($i = 1; $i <= $count; $i++) {
            DB::table($table)->insert([
                'level' => $level,
                'message' => "Test log message {$i}",
                'context' => json_encode(['key' => "value_{$i}"]),
                'logged_at' => now()->subMinutes($count - $i),
            ]);
        }
    }

    protected function seedMixedLogs(): void
    {
        $levels = ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG'];
        $table = config('log-hole.database.table', 'logs_hole');

        foreach ($levels as $i => $level) {
            DB::table($table)->insert([
                'level' => $level,
                'message' => "Test {$level} log",
                'context' => json_encode(['level' => $level]),
                'logged_at' => now()->subMinutes(count($levels) - $i),
            ]);
        }
    }
}
