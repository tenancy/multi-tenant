<?php namespace HynMe\MultiTenant\Tenant;

use DB;
use Config;
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
     * @return mixed
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
     * @param Hostname $hostname
     * @return mixed
     */
    protected function config()
    {
        $clone = Config::get('database.connections.system');
        $clone['password'] = md5(Config::get('app.key') . $this->website->id);
        $clone['username'] = $clone['database'] = str_replace(['.'], '-', $this->website->present()->identifier);
        return $clone;
    }

    /**
     * Sets the tenant database connection
     * @param Hostname $hostname
     */
    public function setup()
    {
        Config::set("database.connections.{$this->name}", $this->config());
    }

    /**
     * @param Hostname $hostname
     * @return bool
     */
    public function create(Website $website)
    {
        $clone = $this->config();
        return
            DB::statement("CREATE DATABASE {$clone['database']}")
            && DB::statement("CREATE USER {$clone['username']} IDENTIFIED BY '{$clone['password']}'")
            && DB::statement("GRANT ALL ON {$clone['database']}.* TO '{$clone['username']}'@'localhost'");
    }
}