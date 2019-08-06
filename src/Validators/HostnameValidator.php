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

namespace Hyn\Tenancy\Validators;

use Hyn\Tenancy\Abstracts\Validator;

class HostnameValidator extends Validator
{
    protected $create = [
        'fqdn' => ['required', 'string', 'unique:%system%.%hostnames%,fqdn', 'regex:/^(?:(?:\*|(?!-)(?:xn--)?[a-zA-Z0-9][a-zA-Z0-9-_]{0,61}[a-zA-Z0-9]{0,1})\.)(?:(?!-)(?:xn--)?[a-zA-Z0-9][a-zA-Z0-9-_]{0,61}[a-zA-Z0-9]{0,1}\.)*(?!-)(?:xn--)?(?:[a-zA-Z0-9\-]{1,50}|[a-zA-Z0-9-]{1,30}\.[a-zA-Z]{2,})$/i'],
        'redirect_to' => ['nullable', 'string', 'url'],
        'force_https' => ['boolean'],
        'under_maintenance_since' => ['nullable', 'date'],
        'website_id' => ['nullable', 'integer', 'exists:%system%.%websites%,id'],
    ];

    protected $update = [
        'id' => ['required', 'integer'],
        'fqdn' => ['required', 'string', 'unique:%system%.%hostnames%,fqdn,%id%', 'regex:/^(?:(?:\*|(?!-)(?:xn--)?[a-zA-Z0-9][a-zA-Z0-9-_]{0,61}[a-zA-Z0-9]{0,1})\.)(?:(?!-)(?:xn--)?[a-zA-Z0-9][a-zA-Z0-9-_]{0,61}[a-zA-Z0-9]{0,1}\.)*(?!-)(?:xn--)?(?:[a-zA-Z0-9\-]{1,50}|[a-zA-Z0-9-]{1,30}\.[a-zA-Z]{2,})$/i'],
        'redirect_to' => ['nullable', 'string', 'url'],
        'force_https' => ['boolean'],
        'under_maintenance_since' => ['nullable', 'date'],
        'website_id' => ['nullable', 'integer', 'exists:%system%.%websites%,id'],
    ];
}
