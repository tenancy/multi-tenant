<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Contracts\Repositories;

use Hyn\Tenancy\Contracts\Hostname;
use Hyn\Tenancy\Contracts\Website;
use Illuminate\Database\Eloquent\Builder;

interface HostnameRepository
{
    /**
     * @param string $hostname
     * @return Hostname|null
     */
    public function findByHostname(string $hostname);

    /**
     * @return Hostname|null
     */
    public function getDefault();

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
    public function attach(Hostname &$hostname, Website &$website): Hostname;
    /**
     * @param Hostname $hostname
     * @return Hostname
     */
    public function detach(Hostname &$hostname): Hostname;

    /**
     * @warn Only use for querying.
     * @return Builder
     */
    public function query(): Builder;
}
