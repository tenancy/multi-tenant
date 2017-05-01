<?php

namespace Hyn\Tenancy\Contracts\Repositories;

use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;

interface HostnameRepository
{
    /**
     * @param string $hostname
     * @return Hostname|null
     */
    public function findByHostname(string $hostname): ?Hostname;

    /**
     * @return Hostname|null
     */
    public function getDefault(): ?Hostname;

    /**
     * @param Hostname $hostname
     * @return Hostname
     */
    public function create(Hostname &$hostname): Hostname;

    /**
     * @param Hostname $hostname
     * @return Hostname
     */
    public function update(Hostname &$hostname): Hostname;

    /**
     * @param Hostname $hostname
     * @param bool $hard
     * @return Hostname
     */
    public function delete(Hostname &$hostname, $hard = false): Hostname;
    /**
     * @param Hostname $hostname
     * @param Website $website
     * @return Hostname
     */
    public function attach(Hostname $hostname, Website $website): Hostname;
    /**
     * @param Hostname $hostname
     * @return Hostname
     */
    public function detach(Hostname $hostname): Hostname;
}
