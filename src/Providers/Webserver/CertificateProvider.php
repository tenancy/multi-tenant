<?php

namespace Hyn\Tenancy\Providers\Webserver;

use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Challenge\SolverInterface;
use AcmePhp\Core\Http\Base64SafeEncoder;
use AcmePhp\Core\Http\SecureHttpClient;
use AcmePhp\Core\Http\ServerErrorHandler;
use AcmePhp\Ssl\KeyPair;
use AcmePhp\Ssl\Parser\KeyParser;
use AcmePhp\Ssl\PrivateKey;
use AcmePhp\Ssl\PublicKey;
use AcmePhp\Ssl\Signer\DataSigner;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class CertificateProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AcmeClient::class, function ($app) {
            return new AcmeClient(
                new SecureHttpClient(
                    $this->keyPair(),
                    new Client(),
                    new Base64SafeEncoder(),
                    new KeyParser(),
                    new DataSigner(),
                    new ServerErrorHandler()
                ),
                $this->directoryUrl()
            );
        });

        $this->app->singleton(SolverInterface::class, function ($app) {
            return $app->make(
                $app['config']->get('webserver.lets-encrypt.solver')
            );
        });
    }

    /**
     * @return KeyPair
     */
    protected function keyPair(): KeyPair
    {
        $publicKeyPath = config('webserver.lets-encrypt.key-pair.public');
        $privateKeyPath = config('webserver.lets-encrypt.key-pair.private');

        $public = new PublicKey(
            $publicKeyPath && file_exists($publicKeyPath) ?
                file_get_contents($publicKeyPath) :
                app('tenant.disk')->get('lets-encrypt-public.pem')
        );

        $private = new PrivateKey(
            $privateKeyPath && file_exists($privateKeyPath) ?
                file_get_contents($privateKeyPath) :
                app('tenant.disk')->get('lets-encrypt-private.pem')
        );

        return new KeyPair(
            $public,
            $private
        );
    }

    /**
     * @return string
     */
    protected function directoryUrl(): string
    {
        return $this->app->environment() === 'production' ?
            'https://acme-v01.api.letsencrypt.org/directory' :
            'https://acme-staging.api.letsencrypt.org/directory';
    }
}
