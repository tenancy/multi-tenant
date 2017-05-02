<?php

namespace Hyn\Tenancy\Contracts\Generator;

use Hyn\Tenancy\Models\Website;

interface GeneratesConfiguration
{
    /**
     * @param Website $website
     * @return string
     */
    public function generate(Website $website): string;
}
