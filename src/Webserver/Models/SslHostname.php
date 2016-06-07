<?php

namespace Hyn\Webserver\Models;

use Laracasts\Presenter\PresentableTrait;
use Hyn\MultiTenant\Abstracts\Models\SystemModel;

class SslHostname extends SystemModel
{
    use PresentableTrait;

    protected $presenter = 'Hyn\Webserver\Presenters\SslHostnamePresenter';

    /**
     * @return SslCertificate
     */
    public function certificate()
    {
        return $this->belongsTo(SslCertificate::class);
    }
}
