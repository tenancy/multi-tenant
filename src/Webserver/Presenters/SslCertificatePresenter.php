<?php

namespace Hyn\Webserver\Presenters;

use Hyn\Framework\Presenters\AbstractModelPresenter;

class SslCertificatePresenter extends AbstractModelPresenter
{
    /**
     * SSL Certificate does not really have a name.
     *
     * @return array
     */
    public function urlArguments()
    {
        return [
            'id' => $this->id,
        ];
    }

    /**
     * Shows summary of hostnames.
     *
     * @return string
     */
    public function hostnamesSummary()
    {
        $hostnames = $this->hostnames->lists('hostname')->all();

        return implode(', ', array_splice($hostnames, 0, 5));
    }

    /**
     * @return int
     */
    public function additionalHostnames()
    {
        return count($this->hostnames) - 5;
    }

    /**
     * @return string
     */
    public function icon()
    {
        return 'fa fa-lock';
    }

    /**
     * @return mixed
     */
    public function name()
    {
        return sprintf('%s %s', $this->X509->issuer(), $this->X509->type());
    }

    public function expiry()
    {
        return $this->invalidates_at->isPast() ? trans('management-interface::ssl.is_expired') : $this->invalidates_at->diffForHumans(null, true);
    }
}
