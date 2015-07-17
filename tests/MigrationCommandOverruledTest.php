<?php namespace LaraLeague\MultiTenant\Tests;

use HynMe\Framework\Testing\TestCase;
use LaraLeague\MultiTenant\Commands\Migrate\MigrateCommand;
use Illuminate\Database\Console\Migrations\MigrateCommand as IlluminateMigrateCommand;

class MigrationCommandOverruledTest extends TestCase
{
    public function testMigrateCommand()
    {
        $migrateCommand = $this->app->make('command.migrate');

        $this->assertEquals(MigrateCommand::class, get_class($migrateCommand));
        $this->assertFalse(IlluminateMigrateCommand::class === get_class($migrateCommand));
    }
}