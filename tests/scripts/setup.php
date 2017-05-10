<?php

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
    '/^(?<stage>[^\-]+)\-(?<php_version>[0-9\.]+)\-L\-(?<laravel_version>[0-9\.]+)\-(?<db>[a-z\+)$/',
    getenv('CI_JOB_NAME'),
    $m
)) {
    echo <<<EOM
Found advanced CI configuration from CI_JOB_NAME environment variable:
    - Stage {$m['stage']}
    - PHP {$m['php_version']}
    - Laravel {$m['laravel_version']}
    - Db driver: {$m['db']}
EOM;

    passthru("php composer update laravel/laravel:{$m['laravel_version']} --prefer-dist -n");

    foreach ([
                 "$base_path/vendor/laravel/laravel/config/tenancy.php",
                 "$base_path/vendor/laravel/laravel/config/webserver.php",
             ] as $config) {
        if (file_exists($config)) {
            @unlink($config);
        }
    }
}

