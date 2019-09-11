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

namespace Hyn\Tenancy\Generators\Uuid;

use Hyn\Tenancy\Contracts\Website\UuidGenerator;
use Hyn\Tenancy\Contracts\Website;
use Ramsey\Uuid\Uuid;

class ShaGenerator implements UuidGenerator
{
    /**
     * @param Website $website
     * @return string
     */
    public function generate(Website $website) : string
    {
        $uuid = Uuid::uuid4()->toString();

        if (config('tenancy.website.uuid-limit-length-to-32')) {
            return str_replace('-', null, $uuid);
        }

        return $uuid;
    }
}
