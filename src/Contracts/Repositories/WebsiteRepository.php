<?php

namespace Hyn\Tenancy\Contracts\Repositories;

use Hyn\Tenancy\Models\Website;

interface WebsiteRepository
{
    /**
     * @param string $uuid
     * @return Website|null
     */
    public function findByUuid(string $uuid): ?Website;
    /**
     * @param Website $website
     * @return Website
     */
    public function create(Website &$website): Website;
    /**
     * @param Website $website
     * @return Website
     */
    public function update(Website &$website): Website;
    /**
     * @param Website $website
     * @param bool $hard
     * @return Website
     */
    public function delete(Website &$website, $hard = false): Website;
}
