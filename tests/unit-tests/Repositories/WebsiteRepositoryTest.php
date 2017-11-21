<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Tests;

use Illuminate\Contracts\Foundation\Application;

class WebsiteRepositoryTest extends Test
{

    /**
     * The table for the users.
     * @var null|string
     */
    private $tableForUsers = null;

    public function __construct()
    {
        parent::__construct();
        $db = getenv('DB_CONNECTION');
        if (!$db || $db == 'mysql') {
            $this->tableForUsers = 'mysql.user';
        } else {
            $this->tableForUsers = 'pg_catalog.pg_user';
        }
    }

    /**
     * @test
     */
    public function creates_website()
    {
        $this->websites->create($this->website);

        $this->assertTrue($this->website->exists);
    }

    /**
     * @test
     */
    public function create_website_with_sql_user()
    {
        $amount_of_sql_users_before = $this->connection->system()->table($this->tableForUsers)->count();
        $this->websites->create($this->website);
        $this->assertTrue($this->website->exists);
        $amount_of_sql_users_after = $this->connection->system()->table($this->tableForUsers)->count();
        $this->assertEquals($amount_of_sql_users_before + 1, $amount_of_sql_users_after,
            'An unexpected amount of SQL-users has been created.');
    }

    /**
     * @test
     */
    public function create_website_without_sql_user()
    {
        config(['tenancy.db.generate-sql-user' => false]);
        $amount_of_sql_users_before = $this->connection->system()->table($this->tableForUsers)->count();
        $this->websites->create($this->website);
        $this->assertTrue($this->website->exists);
        $amount_of_sql_users_after = $this->connection->system()->table($this->tableForUsers)->count();
        $this->assertEquals($amount_of_sql_users_before, $amount_of_sql_users_after, 'A SQL-user has been created.');
    }

    /**
     * @test
     */
    public function updates_website()
    {
        $this->setUpWebsites(true);

        $saved = $this->websites->update($this->website);

        $this->assertEquals($this->website->id, $saved->id);
    }

    /**
     * @test
     * @depends creates_website
     */
    public function deletes_website()
    {
        $this->websites->delete($this->website);

        $this->assertFalse($this->website->exists);
    }

    protected function duringSetUp(Application $app)
    {
        $this->setUpWebsites();
        $this->setUpHostnames();
    }
}
