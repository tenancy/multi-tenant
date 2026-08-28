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
use Hyn\Tenancy\Environment;
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

    /**
     * The loop switches tenant on every website and the last one outlives the
     * command, which matters whenever it is called from a request or a job.
     *
     * @test
     */
    public function running_across_tenants_leaves_no_tenant_active()
    {
        $this->setUpWebsites(true);
        $this->getReplicatedWebsite();

        $this->artisan('tenancy:run', ['run' => 'env']);

        $this->assertNull(
            app(Environment::class)->tenant(),
            'tenancy:run left a tenant active after it finished.'
        );
    }

    /**
     * @test
     */
    public function a_failing_command_leaves_no_tenant_active_either()
    {
        $this->setUpWebsites(true);
        $this->getReplicatedWebsite();

        try {
            $this->artisan('tenancy:run', ['run' => 'commandThatDoesNotExist']);
        } catch (\Throwable $e) {
            // The point is what is left behind, not the exception itself.
        }

        $this->assertNull(
            app(Environment::class)->tenant(),
            'tenancy:run left a tenant active after failing.'
        );
    }
}
