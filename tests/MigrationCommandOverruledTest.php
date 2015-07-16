<?php namespace HynMe\MultiTenant\Tests;

use HynMe\Framework\Testing\TestCase;
use HynMe\MultiTenant\Commands\Migrate\MigrateCommand;

class MigrationCommandOverruledTest extends TestCase
{
    public function testMigrateCommand()
    {
        $migrateCommand = $this->app->make('command.migrate');

        $this->assertEquals(MigrateCommand::class, get_class($migrateCommand));
    }
}