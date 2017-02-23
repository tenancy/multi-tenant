<?php

namespace Hyn\Tenancy\Abstracts;

use Hyn\Tenancy\Database\Connection;
use Illuminate\Database\Migrations\Migration;

abstract class AbstractMigration extends Migration
{
    protected $system = false;

    abstract public function up();

    abstract public function down();

    public function getConnection()
    {
        if ($this->system) {
            return $this->connectionResolver()->systemName();
        }

        return $this->connectionResolver()->tenantName();
    }

    /**
     * @return Connection
     */
    protected function connectionResolver()
    {
        return app(Connection::class);
    }
}
