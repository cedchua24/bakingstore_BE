<?php

namespace App\Console\Commands;

use App\Services\DatabaseExportService;
use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'database:backup';

    protected $description = 'Export the database to the configured backup directory';

    public function handle(DatabaseExportService $exporter): int
    {
        $backup = $exporter->store((string) config('database-export.backup_directory'));

        $this->info('Database backup created: '.$backup['filename']);

        return self::SUCCESS;
    }
}
