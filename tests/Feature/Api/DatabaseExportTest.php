<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\DatabaseExportService;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class DatabaseExportTest extends TestCase
{
    public function test_database_export_requires_authentication(): void
    {
        $this->getJson('/api/database/export')->assertUnauthorized();
    }

    public function test_database_export_rejects_roles_outside_one_two_and_three(): void
    {
        $user = (new User())->forceFill([
            'role_as' => User::ROLE_USER,
        ]);

        Sanctum::actingAs($user, ['server:user']);

        $this->get('/api/database/export')->assertForbidden();
    }

    /**
     * @dataProvider permittedRoleProvider
     */
    public function test_roles_one_two_and_three_can_download_an_export(int $role): void
    {
        $user = (new User())->forceFill([
            'role_as' => $role,
        ]);
        Sanctum::actingAs($user, $role === 1 ? ['server:admin'] : ['server:user']);

        $path = tempnam(sys_get_temp_dir(), 'database-export-test-');
        file_put_contents($path, '-- SQL database export');

        $exporter = Mockery::mock(DatabaseExportService::class);
        $exporter->shouldReceive('export')->once()->andReturn([
            'path' => $path,
            'filename' => 'baker_2026-11-14_00-00-00.sql',
        ]);
        $this->app->instance(DatabaseExportService::class, $exporter);

        try {
            $this->get('/api/database/export')
                ->assertOk()
                ->assertDownload('baker_2026-11-14_00-00-00.sql');
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function permittedRoleProvider(): array
    {
        return [
            'role 1' => [1],
            'role 2' => [2],
            'role 3' => [3],
        ];
    }

    public function test_admin_can_download_a_database_export(): void
    {
        $admin = (new User())->forceFill([
            'role_as' => User::ROLE_ADMIN,
        ]);
        Sanctum::actingAs($admin, ['server:admin']);

        $path = tempnam(sys_get_temp_dir(), 'database-export-test-');
        file_put_contents($path, '-- SQL database export');

        $exporter = Mockery::mock(DatabaseExportService::class);
        $exporter->shouldReceive('export')->once()->andReturn([
            'path' => $path,
            'filename' => 'baker_2026-11-14_00-00-00.sql',
        ]);
        $this->app->instance(DatabaseExportService::class, $exporter);

        try {
            $response = $this
                ->withHeader('Origin', 'http://localhost:3000')
                ->get('/api/database/export');

            $response
                ->assertOk()
                ->assertDownload('baker_2026-11-14_00-00-00.sql')
                ->assertHeader('Content-Type', 'application/sql')
                ->assertHeader('Access-Control-Expose-Headers', 'Content-Disposition');
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_export_errors_do_not_expose_internal_details(): void
    {
        $admin = (new User())->forceFill([
            'role_as' => User::ROLE_ADMIN,
        ]);
        Sanctum::actingAs($admin, ['server:admin']);

        $exporter = Mockery::mock(DatabaseExportService::class);
        $exporter->shouldReceive('export')->once()->andThrow(
            new \RuntimeException('Secret connection detail')
        );
        $this->app->instance(DatabaseExportService::class, $exporter);

        $this->getJson('/api/database/export')
            ->assertStatus(500)
            ->assertExactJson([
                'message' => 'Unable to export the database.',
            ]);
    }
}
