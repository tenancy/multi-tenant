<?php

namespace Hyn\Tenancy\Contracts\Database;

use Hyn\Tenancy\Models\Website;

interface PasswordGenerator
{
    /**
     * @param Website $website
     * @return string
     */
    public function generate(Website $website) : string;
}