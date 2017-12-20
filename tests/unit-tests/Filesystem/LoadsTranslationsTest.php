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

namespace Hyn\Tenancy\Tests\Filesystem;

use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Website\Directory;
use Illuminate\Contracts\Foundation\Application;

class LoadsTranslationsTest extends Test
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
    public function reads_additional_translations()
    {
        // Directory should now exists, let's write the config folder.
        $this->assertTrue($this->directory->makeDirectory('lang'));

        // Write a testing translation file.
        $this->assertTrue($this->directory->put('lang' . DIRECTORY_SEPARATOR . 'ch' . DIRECTORY_SEPARATOR . 'test.php', <<<EOM
<?php

return [
    'foo' => 'bar'
];
EOM
));

        $this->assertTrue($this->directory->exists('lang/ch/test.php'));

        $this->activateTenant('local');

        $this->assertEquals('bar', trans('test.foo', [], 'ch'));
    }

    /**
     * @test
     */
    public function overrides_global_translations()
    {
        $original = include base_path('resources/lang/en/passwords.php');

        $this->assertEquals($original['password'], trans('passwords.password', [], 'en'));

        // Directory should now exists, let's write the config folder.
        $this->assertTrue($this->directory->makeDirectory('lang'));

        // Write a testing translation file.
        $this->assertTrue($this->directory->put('lang' . DIRECTORY_SEPARATOR . 'en' . DIRECTORY_SEPARATOR . 'passwords.php', <<<EOM
<?php

return [
    'password' => 'bar',
];
EOM
        ));

        $this->assertTrue($this->directory->exists('lang/en/passwords.php'));

        $this->activateTenant('local');

        $this->assertEquals('bar', trans('passwords.password', [], 'en'));
    }
}
