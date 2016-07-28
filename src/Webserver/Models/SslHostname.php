<?php

namespace Hyn\Webserver\Models;

use Hyn\Tenancy\Abstracts\Models\SystemModel;
use Laracasts\Presenter\PresentableTrait;

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
