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

namespace Hyn\Tenancy\Tests\Commands;

use App\Console\Kernel;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;

class RunCommandTest extends Test
{
    protected function beforeSetUp(Application $app)
    {
        /** @var Kernel $kernel */
        $kernel = $app->make(Kernel::class);

        $kernel->command('foo', function () {
        });
        $kernel->command('commandThatDoesNotExist', function () {
            throw new \Exception;
        });
        $kernel->command('with:args {foo} {--bar}', function () {
        });
    }

    /**
     * @test
     */
    public function can_proxy_artisan_commands()
    {
        $this->setUpWebsites(true);

        $code = $this->artisan('tenancy:run', [
            'run' => 'foo'
        ]);

        $this->assertEquals(0, $code);
    }

    /**
     * @test
     */
    public function proxies_exceptions()
    {
        $this->expectException(\Exception::class);

        $this->setUpWebsites(true);

        $this->artisan('tenancy:run', [
            'run' => 'commandThatDoesNotExist'
        ]);
    }

    /**
     * @test
     */
    public function takes_options_and_arguments()
    {
        $this->setUpWebsites(true);

        $code = $this->artisan('tenancy:run', [
            'run' => 'with:args',
            '--argument' => [
                'foo=hello'
            ],
            '--option' => [
                'bar=you'
            ]
        ]);
        $this->assertEquals(0, $code);
    }
}
