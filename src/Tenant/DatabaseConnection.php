<?php

namespace Laraflock\MultiTenant\Tenant;

use Config;
use DB;
use HynMe\Framework\Exceptions\TenantDatabaseException;
use Laraflock\MultiTenant\Models\Website;

/**
 * Class DatabaseConnection.
 *
 * Helps with tenant database connections
 */
class DatabaseConnection
{
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

        Config::set('database.connections.tenant', $this->config());
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
     */
    protected function config()
    {
        $clone = Config::get(sprintf('database.connections.%s', static::systemConnectionName()));
        $clone['password'] = md5(Config::get('app.key').$this->website->id);
        $clone['username'] = $clone['database'] = sprintf('%d-%s', $this->website->id, $this->website->present()->identifier);

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
