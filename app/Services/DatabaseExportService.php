<?php

namespace App\Services;

use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class DatabaseExportService
{
    /**
     * Create a MySQL dump and return its temporary path and download name.
     *
     * @return array{path: string, filename: string}
     */
    public function export(): array
    {
        $connectionName = config('database.default');
        $connection = config("database.connections.{$connectionName}");

        if (! is_array($connection) || ($connection['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('Database export currently supports MySQL connections only.');
        }

        $database = (string) ($connection['database'] ?? '');

        if ($database === '') {
            throw new RuntimeException('The database name is not configured.');
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'database-export-');

        if ($temporaryPath === false) {
            throw new RuntimeException('Unable to create a temporary database export file.');
        }

        $command = [
            (string) config('database-export.binary', 'mysqldump'),
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--events',
            '--no-tablespaces',
            '--default-character-set='.($connection['charset'] ?? 'utf8mb4'),
            '--host='.(string) ($connection['host'] ?? '127.0.0.1'),
            '--port='.(string) ($connection['port'] ?? '3306'),
            '--user='.(string) ($connection['username'] ?? ''),
            '--result-file='.$temporaryPath,
        ];

        if (! empty($connection['unix_socket'])) {
            $command[] = '--socket='.(string) $connection['unix_socket'];
        }

        $command[] = $database;

        $environment = [
            'MYSQL_PWD' => (string) ($connection['password'] ?? ''),
        ];

        // PHP's built-in web server may omit these from $_SERVER on Windows.
        // mysqldump needs them to initialize the Windows TCP/IP stack.
        if (PHP_OS_FAMILY === 'Windows') {
            foreach (['SYSTEMROOT', 'WINDIR', 'COMSPEC', 'PATH', 'PATHEXT', 'TEMP', 'TMP'] as $name) {
                $value = getenv($name);

                if ($value !== false) {
                    $environment[$name] = $value;
                }
            }
        }

        $process = new Process($command, null, $environment);
        $process->setTimeout((float) config('database-export.timeout', 300));

        try {
            $process->mustRun();
        } catch (\Throwable $exception) {
            @unlink($temporaryPath);

            throw new RuntimeException('The database export could not be created.', 0, $exception);
        }

        if (! is_file($temporaryPath) || filesize($temporaryPath) === 0) {
            @unlink($temporaryPath);

            throw new RuntimeException('The database export command produced an empty file.');
        }

        $safeDatabaseName = Str::slug($database, '_') ?: 'database';

        return [
            'path' => $temporaryPath,
            'filename' => $safeDatabaseName.'_'.now()->format('Y-m-d_H-i-s').'.sql',
        ];
    }

    /**
     * Create a database dump and keep it in the requested directory.
     *
     * @return array{path: string, filename: string}
     */
    public function store(string $directory): array
    {
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create the database backup directory.');
        }

        $export = $this->export();
        $destination = rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$export['filename'];

        if (! @rename($export['path'], $destination)) {
            @unlink($export['path']);

            throw new RuntimeException('Unable to move the database export into the backup directory.');
        }

        return [
            'path' => $destination,
            'filename' => $export['filename'],
        ];
    }
}
