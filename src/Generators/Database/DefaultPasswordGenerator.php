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

namespace Hyn\Tenancy\Generators\Database;

use Hyn\Tenancy\Contracts\Database\PasswordGenerator;
use Hyn\Tenancy\Contracts\Website;
use Illuminate\Contracts\Foundation\Application;

class DefaultPasswordGenerator implements PasswordGenerator
{
    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param Website $website
     * @return string
     */
    public function generate(Website $website) : string
    {
        return md5(sprintf(
            '%s.%d',
            $this->app['config']->get('app.key'),
            $website->id
        ));
    }
}
