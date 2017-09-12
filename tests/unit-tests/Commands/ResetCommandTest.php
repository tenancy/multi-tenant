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

namespace Hyn\Tenancy\Tests\Commands;

use Hyn\Tenancy\Database\Console\ResetCommand;
use Hyn\Tenancy\Tests\Test;

class ResetCommandTest extends Test
{
    /**
     * @test
     */
    public function is_ioc_bound()
    {
        $this->assertInstanceOf(
            ResetCommand::class,
            $this->app->make(ResetCommand::class)
        );
    }
}
