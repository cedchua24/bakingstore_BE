<?php

$xamppBinary = base_path('../../mysql/bin/mysqldump.exe');
$defaultBinary = is_file($xamppBinary) ? $xamppBinary : 'mysqldump';

return [
    /*
    | Full path to mysqldump may be supplied when it is not on PATH.
    | Example for XAMPP: C:\\xampp\\mysql\\bin\\mysqldump.exe
    */
    'binary' => env('DB_DUMP_BINARY', $defaultBinary),

    'timeout' => (int) env('DB_DUMP_TIMEOUT', 300),

    'backup_directory' => env('DB_BACKUP_DIRECTORY')
        ?: storage_path('app/database-backups'),

    'retention_days' => (int) env('DB_BACKUP_RETENTION_DAYS', 7),
];
