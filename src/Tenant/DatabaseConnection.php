<?php

namespace Hyn\MultiTenant\Tenant;

use Config;
use DB;
use Hyn\Framework\Exceptions\TenantDatabaseException;
use Hyn\MultiTenant\Models\Website;

/**
 * Class DatabaseConnection.
 *
 * Helps with tenant database connections
 */
class DatabaseConnection
{
    /**
     * See the multi-tenant configuration file. Configuration set
     * to use separate databases.
     */
    const TENANT_MODE_SEPARATE_DATABASE = 'database';

    /**
     * See the multi-tenant configuration file. Configuration set
     * to use prefixed table in same database.
     */
    const TENANT_MODE_TABLE_PREFIX = 'prefix';

    /**
     * @var Website
     */
    protected $website;
    /**
     * @var \Illuminate\Database\Connection
     */
    protected $connection;

    /**
     * @var string
     */
    public $name;

    /**
     * Current active global tenant connection.
     *
     * @var string
     */
    protected static $current;

    public function __construct(Website $website)
    {
        $this->website = $website;

        $this->name = "tenant.{$this->website->id}";

        $this->setup();
    }

    /**
     * Checks whether current connection is set as global tenant connection.
     *
     * @return bool
     */
    public function isCurrent()
    {
        return $this->name === static::getCurrent();
    }

    /**
     * Sets current global tenant connection.
     */
    public function setCurrent()
    {
        static::$current = $this->name;

        Config::set(sprintf('database.connections.%s', static::tenantConnectionName()), $this->config());
    }

    /**
     * Loads the currently set global tenant connection name.
     *
     * @return string
     */
    public static function getCurrent()
    {
        return static::$current;
    }

    /**
     * Loads connection for this database.
     *
     * @return \Illuminate\Database\Connection
     */
    public function get()
    {
        if (is_null($this->connection)) {
            $this->setup();
            $this->connection = DB::connection($this->name);
        }

        return $this->connection;
    }

    /**
     * Generic configuration for tenant.
     *
     * @return array
     * @throws TenantDatabaseException
     * @throws \Laracasts\Presenter\Exceptions\PresenterException
     */
    protected function config()
    {
        $clone = Config::get(sprintf('database.connections.%s', static::systemConnectionName()));

        if (Config::get('multi-tenant.db.tenant-division-mode') == static::TENANT_MODE_SEPARATE_DATABASE) {
            $clone['password'] = md5(Config::get('app.key') . $this->website->id);
            $clone['username'] = $clone['database'] = sprintf('%d-%s', $this->website->id,
                $this->website->present()->identifier);
        } elseif (Config::get('multi-tenant.db.tenant-division-mode') == static::TENANT_MODE_TABLE_PREFIX) {
            $clone['prefix'] = sprintf('t%d_', $this->website->id);
        } else {
            throw new TenantDatabaseException("Unknown database division mode configured in the multi-tenant configuration file.");
        }

        return $clone;
    }

    /**
     * Sets the tenant database connection.
     */
    public function setup()
    {
        Config::set("database.connections.{$this->name}", $this->config());
    }

    /**
     * @return bool
     */
    public function create()
    {
        if (Config::get('multi-tenant.db.tenant-division-mode') != static::TENANT_MODE_SEPARATE_DATABASE) {
            return null;
        }

        $clone = $this->config();

        return DB::connection(static::systemConnectionName())->transaction(function () use ($clone) {
            if (!DB::statement("create database if not exists `{$clone['database']}`")) {
                throw new TenantDatabaseException("Could not create database {$clone['database']}");
            }
            if (!DB::statement("grant all on `{$clone['database']}`.* to `{$clone['username']}`@'localhost' identified by '{$clone['password']}'")) {
                throw new TenantDatabaseException("Could not create or grant privileges to user {$clone['username']} for {$clone['database']}");
            }

            return true;
        });
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function delete()
    {
        if (Config::get('multi-tenant.db.tenant-division-mode') != static::TENANT_MODE_SEPARATE_DATABASE) {
            return null;
        }

        $clone = $this->config();

        return DB::connection(static::systemConnectionName())->transaction(function () use ($clone) {
            if (!DB::statement("revoke all on `{$clone['database']}`.* from `{$clone['username']}`@'localhost'")) {
                throw new TenantDatabaseException("Could not revoke privileges to user {$clone['username']} for {$clone['database']}");
            }
            if (!DB::statement("drop database `{$clone['database']}`")) {
                throw new TenantDatabaseException("Could not drop database {$clone['database']}");
            }
            if (!DB::statement("drop user `{$clone['username']}`@'localhost'")) {
                throw new TenantDatabaseException("Could not drop user {$clone['username']}");
            }

            return true;
        });
    }

    /**
     * Central getter for system connection name.
     *
     * @return string
     */
    public static function systemConnectionName()
    {
        return Config::get('multi-tenant.db.system-connection-name', 'hyn');
    }

    /**
     * Central getter for tenant connection name.
     *
     * @return string
     */
    public static function tenantConnectionName()
    {
        return Config::get('multi-tenant.db.tenant-connection-name', 'tenant');
    }
}
