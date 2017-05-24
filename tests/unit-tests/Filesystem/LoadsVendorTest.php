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

namespace Hyn\Tenancy\Tests\Filesystem;

use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Website\Directory;
use Illuminate\Contracts\Foundation\Application;

class LoadsVendorTest extends Test
{
    /**
     * @var Directory
     */
    protected $directory;

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);

        $this->directory = $app->make(Directory::class);
        $this->directory->setWebsite($this->website);
    }

    /**
     * @test
     */
    public function reads_additional_vendor()
    {
        // Directory should now exists, let's write the config folder.
        $this->assertTrue($this->directory->makeDirectory('vendor'));

        // Write a testing config.
        $this->assertTrue($this->directory->put('vendor' . DIRECTORY_SEPARATOR . 'autoload.php', <<<EOM
<?php

namespace Test\Vendor;

class Foo {}
EOM
));

        $this->assertTrue($this->directory->exists('vendor/autoload.php'));

        $this->activateTenant('local');

        $this->assertTrue(class_exists(\Test\Vendor\Foo::class));
    }
}
