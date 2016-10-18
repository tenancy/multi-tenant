<?php

namespace Hyn\Tenancy\Contracts\Website;

use Hyn\Tenancy\Models\Website;

interface UuidGenerator
{
    /**
     * @param Website $website
     * @return string
     */
    public function generate(Website $website) : string;
}