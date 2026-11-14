<?php

return [
    /* Every 30 minutes. */
    'backup_cron' => env('DB_BACKUP_CRON', '0,30 * * * *'),

    /* Every day at 1:00 PM. */
    'cleanup_cron' => env('DB_BACKUP_CLEANUP_CRON', '0 13 * * *'),

    'timezone' => env('DB_BACKUP_TIMEZONE', config('app.timezone')),
];
