<?php

namespace Hyn\Tenancy\Database;

use Illuminate\Database\ConnectionResolverInterface;

class Resolver implements ConnectionResolverInterface
{
    /**
     * @var string
     */
    private $connection;
    /**
     * @var ConnectionResolverInterface
     */
    private $connectionResolver;

    public function __construct(string $connection, ConnectionResolverInterface $connectionResolver)
    {
        $this->connection = $connection;

        $connectionResolver->setDefaultConnection($connection);

        $this->connectionResolver = $connectionResolver;
    }

    public function connection($name = null)
    {
        return $this->connectionResolver->connection($this->connection);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->connectionResolver, $name], $arguments);
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->connection;
    }

    /**
     * Set the default connection name.
     *
     * @param  string $name
     * @return void
     */
    public function setDefaultConnection($name)
    {
        throw new \InvalidArgumentException("Resetting the default connection of a forced model is impossible.");
    }
}
