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

namespace Hyn\Tenancy\Tests\Traits;

use Illuminate\Database\Eloquent\Collection;

trait InteractsWithMigrations
{
    /**
     * @param string $command
     * @param callable|null $callback
     * @param callable|null $hook
     */
    protected function migrateAndTest(string $command, callable $callback = null, callable $hook = null)
    {
        if ($command !== 'migrate') {
            $command = "migrate:$command";
        }

        $code = $this->artisan("tenancy:$command", [
            '--realpath' => __DIR__ . '/../migrations',
            '-n' => 1
        ]);

        $this->assertEquals(0, $code, "tenancy:$command didn't work out");

        if ($hook) {
            $hook();
        }

        if ($callback) {
            $this->websites->query()->chunk(10, function (Collection $websites) use ($callback) {
                $websites->each($callback);
            });
        }
    }
}
