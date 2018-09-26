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

class HostnameValidator extends Validator
{
    protected $create = [
        'fqdn' => ['required', 'string', 'unique:%system%.%hostnames%,fqdn'],
        'redirect_to' => ['nullable', 'string', 'url'],
        'force_https' => ['boolean'],
        'under_maintenance_since' => ['nullable', 'date'],
        'website_id' => ['nullable', 'integer', 'exists:%system%.%websites%,id'],
    ];

    protected $update = [
        'id' => ['required', 'integer'],
        'fqdn' => ['required', 'string', 'unique:%system%.%hostnames%,fqdn,%id%'],
        'redirect_to' => ['nullable', 'string', 'url'],
        'force_https' => ['boolean'],
        'under_maintenance_since' => ['nullable', 'date'],
        'website_id' => ['nullable', 'integer', 'exists:%system%.%websites%,id'],
    ];
}
