<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Validators;

use Hyn\Tenancy\Abstracts\Validator;

class WebsiteValidator extends Validator
{
    protected $create = [
        'uuid' => ['required', 'string', 'unique:%system%.websites,uuid'],
        'customer_id' => ['integer', 'exists:%system%.customers,id'],
    ];
    protected $update = [
        'uuid' => ['required', 'string', 'unique:%system%.websites,uuid,%id%'],
        'customer_id' => ['integer', 'exists:%system%.customers,id'],
    ];
}
