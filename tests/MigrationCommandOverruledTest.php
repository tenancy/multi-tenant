<?php

namespace Hyn\MultiTenant\Tests;

use Hyn\Framework\Testing\TestCase;
use Illuminate\Database\Console\Migrations\MigrateCommand as IlluminateMigrateCommand;
use Hyn\MultiTenant\Commands\Migrate\MigrateCommand;

/**
 * Class MigrationCommandOverruledTest.
 */
class MigrationCommandOverruledTest extends TestCase
{
    /**
     * @covers \Hyn\MultiTenant\Commands\Migrate\MigrateCommand
     */
    public function testMigrateCommand()
    {
        $migrateCommand = $this->app->make('command.migrate');

        $this->assertEquals(MigrateCommand::class, get_class($migrateCommand));
        $this->assertFalse(IlluminateMigrateCommand::class === get_class($migrateCommand));
    }
}
