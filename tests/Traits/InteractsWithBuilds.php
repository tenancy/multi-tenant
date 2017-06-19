<?php

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
