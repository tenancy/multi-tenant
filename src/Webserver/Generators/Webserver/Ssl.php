<?php

namespace Hyn\Webserver\Generators\Webserver;

use File;
use Hyn\Webserver\Abstracts\AbstractGenerator;
use Hyn\Webserver\Models\SslCertificate;

class Ssl extends AbstractGenerator
{
    /**
     * @var SslCertificate
     */
    protected $certificate;

    public function __construct(SslCertificate $certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->certificate->present()->name;
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return bool
     */
    public function onRename($from, $to)
    {
        // no action required
        return true;
    }

    /**
     * Publish path for specific filetype.
     *
     * @param string $postfix
     *
     * @return string
     */
    protected function publishPath($postfix = 'key')
    {
        return $this->certificate->publishPath($postfix);
    }

    /**
     * Pem.
     *
     * @return string
     */
    protected function pem()
    {
        return implode("\r\n", [$this->certificate->certificate, $this->certificate->authority_bundle]);
    }

    /**
     * @return bool
     */
    public function onCreate()
    {
        return
            (! File::isDirectory(dirname($this->certificate->pathKey)) && File::makeDirectory(dirname($this->certificate->pathKey)))
            && File::put($this->certificate->pathKey, $this->certificate->key)
            && File::put($this->certificate->pathCrt, $this->certificate->certificate)
            && File::put($this->certificate->pathCa, $this->certificate->authority_bundle)
            && File::put($this->certificate->pathPem, $this->pem());
    }

    /**
     * @return bool
     */
    public function onUpdate()
    {
        $this->onCreate();
    }

    /**
     * @return bool
     */
    public function onDelete()
    {
        return
            File::delete($this->publishPath('key'))
            && File::delete($this->publishPath('pem'));
    }
}
