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

if (getenv('CIRCLE_WORKING_DIRECTORY')) {
    putenv('CI_PROJECT_DIR=' . getenv('CIRCLE_WORKING_DIRECTORY'));
}

chdir(getenv('CI_PROJECT_DIR'));

$base_path = __DIR__ . '/../../';

/**
 * Install correct version of laravel.
 * Install db driver dependencies.
 */
if (preg_match(
    '/^php\-(?<php_version>[0-9\.]+)\-(?<webserver>[a-z]+)$/',
    getenv('CIRCLE_JOB'),
    $m
)) {
    putenv("BUILD_WEBSERVER={$m['webserver']}");
    putenv("BUILD_PHP_VERSION={$m['php_version']}");
    $connection = getenv('DB_CONNECTION');

    echo <<<EOM
    
    
Found advanced CI configuration from CIRCLE_JOB environment variable:
    - Webserver: {$m['webserver']}
    - PHP: {$m['php_version']}
    - Db driver: {$connection}


EOM;

    foreach ([
            // Marks installation finished
            "$base_path/vendor/laravel/laravel/tenancy.json",
            "$base_path/vendor/laravel/laravel/config/tenancy.php",
            "$base_path/vendor/laravel/laravel/config/webserver.php",
     ] as $file) {
        if (file_exists($file)) {
            echo "Unlinked $file\n";
            @unlink($file);
        }
    }
}
