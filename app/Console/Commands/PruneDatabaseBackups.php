<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PruneDatabaseBackups extends Command
{
    protected $signature = 'database:prune-backups';

    protected $description = 'Delete database backup files older than the retention period';

    public function handle(): int
    {
        $directory = (string) config('database-export.backup_directory');

        if (! File::isDirectory($directory)) {
            $this->info('No database backup directory exists yet.');

            return self::SUCCESS;
        }

        $cutoff = now()->subDays((int) config('database-export.retention_days', 7))->getTimestamp();
        $deleted = 0;

        foreach (File::files($directory) as $file) {
            if ($file->getExtension() !== 'sql' || $file->getMTime() >= $cutoff) {
                continue;
            }

            if (File::delete($file->getPathname())) {
                $deleted++;
            }
        }

        $this->info("Deleted {$deleted} expired database backup(s).");

        return self::SUCCESS;
    }
}
