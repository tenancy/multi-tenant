<?php namespace Laraflock\MultiTenant\Tests;

use HynMe\Framework\Testing\TestCase;
use Laraflock\MultiTenant\Commands\Migrate\MigrateCommand;
use Illuminate\Database\Console\Migrations\MigrateCommand as IlluminateMigrateCommand;

/**
 * Class MigrationCommandOverruledTest
 * @package Laraflock\MultiTenant\Tests
 * @coversDefaultClass \Laraflock\MultiTenant\Commands\Migrate\MigrateCommand
 */
class MigrationCommandOverruledTest extends TestCase
{
    /**
     * @covers
     */
    public function testMigrateCommand()
    {
        $migrateCommand = $this->app->make('command.migrate');

        $this->assertEquals(MigrateCommand::class, get_class($migrateCommand));
        $this->assertFalse(IlluminateMigrateCommand::class === get_class($migrateCommand));
    }
}