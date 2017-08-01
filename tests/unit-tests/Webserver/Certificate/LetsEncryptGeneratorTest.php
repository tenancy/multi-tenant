<?php

namespace Hyn\Tenancy\Tests\Webserver\Certificate;

use Hyn\Tenancy\Generators\Webserver\Certificate\LetsEncryptGenerator;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;

class LetsEncryptGeneratorTest extends Test
{
    /**
     * @var LetsEncryptGenerator
     */
    protected $generator;

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames();
        $this->setUpWebsites(true, true);

        $app['config']->set('webserver.lets-encrypt.directory-url', 'http://boulder:4000');
        $app['config']->set('webserver.lets-encrypt.agreement-url', 'http://boulder:4000/terms/v1');
        $app['config']->set('webserver.lets-encrypt.key-pair.private', __DIR__ . '/../../../../lets-encrypt-private.pem');
        $app['config']->set('webserver.lets-encrypt.key-pair.public', __DIR__ . '/../../../../lets-encrypt-public.pem');

        $this->generator = $app->make(LetsEncryptGenerator::class);
    }

    /**
     * @test
     */
    public function can_create_a_request()
    {
        $this->generator->generate($this->website);
    }
}
