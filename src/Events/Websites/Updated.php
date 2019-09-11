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

namespace Hyn\Tenancy\Events\Websites;

use Hyn\Tenancy\Abstracts\WebsiteEvent;
use Hyn\Tenancy\Contracts\Website;

class Updated extends WebsiteEvent
{
    /**
     * @var array
     */
    public $dirty;

    public function __construct(Website &$website, array $dirty = [])
    {
        parent::__construct($website);

        $this->dirty = $dirty;
    }
}
