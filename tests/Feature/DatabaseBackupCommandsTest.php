<?php

namespace Tests\Feature;

use App\Services\DatabaseExportService;
use Mockery;
use Tests\TestCase;

class DatabaseBackupCommandsTest extends TestCase
{
    private string $backupDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backupDirectory = storage_path('framework/testing/database-backups');
        config()->set('database-export.backup_directory', $this->backupDirectory);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->backupDirectory)) {
            foreach (glob($this->backupDirectory.'/*') ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            rmdir($this->backupDirectory);
        }

        parent::tearDown();
    }

    public function test_backup_command_stores_a_database_export(): void
    {
        $exporter = Mockery::mock(DatabaseExportService::class);
        $exporter->shouldReceive('store')
            ->once()
            ->with($this->backupDirectory)
            ->andReturn([
                'path' => $this->backupDirectory.'/baker_2026-11-14_12-30-00.sql',
                'filename' => 'baker_2026-11-14_12-30-00.sql',
            ]);
        $this->app->instance(DatabaseExportService::class, $exporter);

        $this->artisan('database:backup')
            ->expectsOutput('Database backup created: baker_2026-11-14_12-30-00.sql')
            ->assertSuccessful();
    }

    public function test_prune_command_only_deletes_sql_backups_older_than_one_week(): void
    {
        mkdir($this->backupDirectory, 0755, true);

        $expired = $this->backupDirectory.'/expired.sql';
        $current = $this->backupDirectory.'/current.sql';
        $unrelated = $this->backupDirectory.'/keep.txt';

        file_put_contents($expired, 'expired');
        file_put_contents($current, 'current');
        file_put_contents($unrelated, 'keep');
        touch($expired, now()->subDays(8)->getTimestamp());
        touch($current, now()->subDays(6)->getTimestamp());
        touch($unrelated, now()->subDays(8)->getTimestamp());

        $this->artisan('database:prune-backups')
            ->expectsOutput('Deleted 1 expired database backup(s).')
            ->assertSuccessful();

        $this->assertFileDoesNotExist($expired);
        $this->assertFileExists($current);
        $this->assertFileExists($unrelated);
    }
}
