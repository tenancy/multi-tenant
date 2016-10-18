<?php

namespace Hyn\Tenancy\Generators\Uuid;

use Hyn\Tenancy\Contracts\Website\UuidGenerator;
use Hyn\Tenancy\Models\Website;

class SimpleStringGenerator implements UuidGenerator
{

    /**
     * @param Website $website
     * @return string
     */
    public function generate(Website $website) : string
    {
        return sprintf(
            'website-%d',
            $website->id
        );
    }
}