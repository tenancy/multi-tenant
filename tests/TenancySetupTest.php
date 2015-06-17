<?php namespace HynMe\MultiTenant\Tests;

use App;
use TestCase;

class TenancySetupTest extends TestCase
{

    public function testSetupHasNotRun()
    {
        $this->tenant = App::make('tenant.hostname');
        $this->assertNull($this->tenant);
    }
}