<?php

namespace Hyn\Tenancy\Generators\Uuid;

use Hyn\Tenancy\Contracts\Website\UuidGenerator;
use Hyn\Tenancy\Models\Website;
use Ramsey\Uuid\Uuid;

class ShaGenerator implements UuidGenerator
{
    /**
     * @param Website $website
     * @return string
     */
    public function generate(Website $website) : string
    {
        return Uuid::uuid4(Uuid::NAMESPACE_X500, $website->id);
    }
}
