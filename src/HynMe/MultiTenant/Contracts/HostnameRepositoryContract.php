<?php namespace HynMe\MultiTenant\Contracts;

use HynMe\Framework\Contracts\BaseRepositoryContract;

interface HostnameRepositoryContract extends BaseRepositoryContract
{
    /**
     * @param $hostname
     * @return \HynMe\MultiTenant\Models\Hostname
     */
    public function findByHostname($hostname);

    /**
     * @return \HynMe\MultiTenant\Models\Hostname
     */
    public function getDefault();

    /**
     * Creates hostname object for default
     * @return void
     */
    public function createDefault();
}