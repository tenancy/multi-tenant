<?php

namespace Hyn\Tenancy\Abstracts;

use Hyn\Tenancy\Database\Connection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

abstract class AbstractMigration extends Migration
{
    abstract public function up();

    abstract public function down();

    /**
     * @return Connection
     */
    protected function connectionResolver()
    {
        return app(Connection::class);
    }

    /**
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function systemConnection()
    {
        return Schema::connection($this->connectionResolver()->systemName());
    }

    /**
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function tenantConnection()
    {
        return Schema::connection($this->connectionResolver()->tenantName());
    }

    /**
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function defaultConnection()
    {
        return Schema::connection();
    }
}
