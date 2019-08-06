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

namespace Hyn\Tenancy\Events\Hostnames;

use Hyn\Tenancy\Abstracts\AbstractEvent;
use Illuminate\Http\Request;

class NoneFound extends AbstractEvent
{
    /**
     * @var Request
     */
    public $request;

    /**
     * NoneFound constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
