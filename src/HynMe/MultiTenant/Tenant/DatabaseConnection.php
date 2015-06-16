<?php namespace HynMe\MultiTenant\Tenant;

use DB;
use Config;
use HynMe\Framework\Exceptions\TenantDatabaseException;
use HynMe\MultiTenant\Models\Website;

/**
 * Class DatabaseConnection
 *
 * Helps with tenant database connections
 * @package HynMe\MultiTenant\Tenant
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

    protected static $current;

    public function __construct(Website $website)
    {
        $this->website = $website;

        $this->name = "tenant.{$this->website->id}";
    }

    /**
     * Checks whether current connection is set as global tenant connection
     * @return bool
     */
    public function isCurrent()
    {
        return $this->name === static::getCurrent();
    }

    /**
     * Sets current global tenant connection
     */
    public function setCurrent()
    {
        static::$current = $this->name;

        Config::set("database.connections.tenant", $this->config());
    }

    /**
     * Loads the currently set global tenant connection name
     * @return string
     */
    public static function getCurrent()
    {
        return static::$current;
    }

    /**
     * Loads connection for this database
     * @return \Illuminate\Database\Connection
     */
    public function get()
    {
        if(is_null($this->connection))
        {
            $this->setup();
            $this->connection = DB::connection("database.connections.{$this->name}");
        }

        return $this->connection;
    }

    /**
     * Generic configuration for tenant
     * @return array
     */
    protected function config()
    {
        $clone = Config::get('database.connections.system');
        $clone['password'] = md5(Config::get('app.key') . $this->website->id);
        $clone['username'] = $clone['database'] = sprintf("%d-%s", $this->website->id, $this->website->present()->identifier);
        return $clone;
    }

    /**
     * Sets the tenant database connection
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

        DB::beginTransaction();
        try {
            if (!DB::statement("create database `{$clone['database']}`")) {
                throw new TenantDatabaseException("Could not create database {$clone['database']}");
            }
            if (!DB::statement("create user `{$clone['username']}`@'localhost' identified by '{$clone['password']}'")) {
                throw new TenantDatabaseException("Could not create user {$clone['username']}");
            }
            if (!DB::statement("grant all on `{$clone['database']}`.* to `{$clone['username']}`@'localhost'")) {
                throw new TenantDatabaseException("Could not grant privileges to user {$clone['username']} for {$clone['database']}");
            }
            DB::commit();
        } catch (\Exception $e)
        {
            DB::rollBack();
            throw $e;
        }

        return true;
    }
}