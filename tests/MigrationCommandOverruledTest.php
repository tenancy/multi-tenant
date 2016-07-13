<?php

namespace Hyn\Tenancy\Tests;

use Hyn\Framework\Testing\TestCase;
use Hyn\Tenancy\Commands\Migrate\MigrateCommand;
use Illuminate\Database\Console\Migrations\MigrateCommand as IlluminateMigrateCommand;

/**
 * Class MigrationCommandOverruledTest.
 */
class MigrationCommandOverruledTest extends TestCase
{
    /**
     * @covers \Hyn\Tenancy\Commands\Migrate\MigrateCommand
     */
    public function testMigrateCommand()
    {
        $migrateCommand = $this->app->make('command.migrate');

        $this->assertEquals(MigrateCommand::class, get_class($migrateCommand));
        $this->assertFalse(IlluminateMigrateCommand::class === get_class($migrateCommand));
    }
}
