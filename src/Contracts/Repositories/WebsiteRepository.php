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
}
