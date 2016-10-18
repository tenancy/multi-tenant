<?php

namespace Hyn\Tenancy\Generators\Database;

use Hyn\Tenancy\Contracts\Database\PasswordGenerator;
use Hyn\Tenancy\Models\Website;
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