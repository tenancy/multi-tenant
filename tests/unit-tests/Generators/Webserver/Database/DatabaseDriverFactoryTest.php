<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Tests\Generators\Webserver\Database;

use Hyn\Tenancy\Generators\Webserver\Database\DatabaseDriverFactory;
use Hyn\Tenancy\Generators\Webserver\Database\Drivers\MariaDB;
use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Tests\Extend\DatabaseDriverExtend;

class DatabaseDriverFactoryTest extends Test
{
    /**
     * @test
     */
    public function creates_mysql_driver_by_default()
    {
        $driver = (new DatabaseDriverFactory())->create();

        $this->assertInstanceOf(MariaDB::class, $driver);
    }

    /**
     * @test
     */
    public function throws_an_exception_if_driver_doesnt_exist()
    {
        $this->expectException(\Hyn\Tenancy\Exceptions\GeneratorFailedException::class);

        (new DatabaseDriverFactory())->create('non-existing-driver');
    }

    /**
     * @test
     */
    public function throws_an_exception_if_driver_doesnt_implement_contract()
    {
        $this->expectException(\TypeError::class);

        app('tenancy.db.drivers')->put('random', \stdClass::class);

        (new DatabaseDriverFactory())->create('random');
    }

    /**
     * @test
     */
    public function allows_to_create_custom_driver()
    {
        app('tenancy.db.drivers')->put('custom', DatabaseDriverExtend::class);

        $this->assertInstanceOf(DatabaseDriverExtend::class, (new DatabaseDriverFactory())->create('custom'));
    }
}
