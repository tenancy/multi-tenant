<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/hyn/multi-tenant
 *
 */

namespace Hyn\Tenancy\Tests\Traits;

trait InteractsWithBuilds
{
    protected $buildWebserver = 'none';
    protected $buildPhpVersion;
    protected $buildDb;
    protected $buildLaravelVersion;

    public function identifyBuild()
    {
        $name = env('CI_JOB_NAME');

        if ($name && preg_match(
            '/^(?<webserver>[a-z]+)\-(?<php_version>[0-9\.]+)\-L\-(?<laravel_version>[^\-]+)\-(?<db>[a-z]+)$/',
            $name,
            $m
        )) {
            $this->buildWebserver = $m['webserver'];
            $this->buildPhpVersion = $m['php_version'];
            $this->buildLaravelVersion = $m['laravel_version'];
            $this->buildDb = $m['db'];
        }
    }
}
