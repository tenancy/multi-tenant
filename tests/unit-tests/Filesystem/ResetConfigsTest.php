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

namespace Hyn\Tenancy\Tests\Filesystem;

use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Events\Websites\Identified;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Website\Directory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Queue;

class ConfigJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $website_id;

    public $appName;

    public function __construct($website_id = null)
    {
        $this->website_id = $website_id;
    }

    public function handle()
    {
        $website = Website::find($this->website_id);
        if ($website)
        {
            app(Environment::class)->tenant($website);
        }
        $this->appName = config('app.name');
    }
}

class ResetConfigsTest extends Test
{
    /**
     * @var Directory
     */
    protected $directory;

    /**
     * @var \Hyn\Tenancy\Contracts\Website
     */
    protected $secondWebsite;

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);

        $this->directory = $app->make(Directory::class);
        $this->directory->setWebsite($this->website);

        $this->secondWebsite = new Website;
        $this->websites->create($this->secondWebsite);

        $secondDirectory = $this->app->make(Directory::class);
        $secondDirectory->setWebsite($this->secondWebsite);
        $secondDirectory->makeDirectory('config');
        $secondDirectory->put('config/app.php', <<<EOM
<?php
return ['name' => 'My name on second tenant'];
EOM
        );
    }
    /**
     * @test
     */
    public function config_is_reset_when_tenant_switched_identified()
    {
        config()->set('app.name', 'Laravel');

        $job = new ConfigJob($this->website->id);
        $secondJob = new ConfigJob($this->secondWebsite->id);

        $secondJob->handle();
        $job->handle();

        $this->assertEquals('Laravel', $job->appName);
        $this->assertEquals('My name on second tenant', $secondJob->appName);
    }
}
