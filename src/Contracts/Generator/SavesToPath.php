<?php

namespace Hyn\Tenancy\Contracts\Generator;

use Hyn\Tenancy\Models\Website;

interface SavesToPath
{
    /**
     * @param Website $website
     * @return string
     */
    public function targetPath(Website $website): string;
}
