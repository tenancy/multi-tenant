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
use Illuminate\Support\Str;

class WebsiteValidator extends Validator
{
    protected $websites;
    protected $create;
    protected $update;

    public function __construct()
    {
        $this->websites = str_replace('\\', '', Str::snake(Str::plural(class_basename(config('tenancy.models.website')))));
        $this->create = [
            'uuid' => ['required', 'string', "unique:%system%.{$this->websites},uuid"],
        ];
        $this->update = [
            'uuid' => ['required', 'string', "unique:%system%.{$this->websites},uuid,%id%"],
        ];
    }
}
