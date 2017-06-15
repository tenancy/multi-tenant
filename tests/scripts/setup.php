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

chdir(getenv('CI_PROJECT_DIR'));

$base_path = __DIR__ . '/../../';

if (getenv('TRAVIS_BUILD_DIR')) {
    putenv('CI_PROJECT_DIR=' . getenv('TRAVIS_BUILD_DIR'));
}

/**
 * Install correct version of laravel.
 * Install db driver dependencies.
 */
if (preg_match(
    '/^(?<webserver>[a-z]+)\-(?<php_version>[0-9\.]+)\-L\-(?<laravel_version>[^\-]+)\-(?<db>[a-z]+)$/',
    getenv('CI_JOB_NAME'),
    $m
)) {
    putenv("BUILD_WEBSERVER={$m['webserver']}");
    putenv("BUILD_LARAVEL_VERSION={$m['laravel_version']}");
    putenv("BUILD_PHP_VERSION={$m['php_version']}");

    if (!strstr($m['laravel_version'], '.')) {
        $m['laravel_version'] = "dev-" . $m['laravel_version'];
    } else {
        $m['laravel_version'] = $m['laravel_version'] . ".*";
    }

    echo <<<EOM
    
    
Found advanced CI configuration from CI_JOB_NAME environment variable:
    - Webserver {$m['webserver']}
    - PHP {$m['php_version']}
    - Laravel {$m['laravel_version']}
    - Db driver: {$m['db']}


EOM;

    $composerCommand = "php composer require laravel/laravel:{$m['laravel_version']}";

    if ($m['laravel_version'] == '5.3.*') {
        $composerCommand .= " phpunit/phpunit:5.*";
    }

    passthru("$composerCommand --prefer-dist -n");

    foreach ([
                 "$base_path/vendor/laravel/laravel/config/tenancy.php",
                 "$base_path/vendor/laravel/laravel/config/webserver.php",
             ] as $config) {
        if (file_exists($config)) {
            @unlink($config);
        }
    }
}
