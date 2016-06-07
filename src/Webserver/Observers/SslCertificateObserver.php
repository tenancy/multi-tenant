<?php

namespace Hyn\Webserver\Observers;

use Hyn\Webserver\Commands\SslCertificateCommand;
use Hyn\Webserver\Models\SslHostname;
use Illuminate\Foundation\Bus\DispatchesJobs;

class SslCertificateObserver
{
    use DispatchesJobs;

    /**
     * @param \Hyn\Webserver\Models\SslCertificate $model
     */
    public function creating($model)
    {
        foreach (['certificate', 'authority_bundle', 'key'] as $attribute) {
            if ($model->{$attribute}) {
                $model->{$attribute} = trim($model->{$attribute});
            }
        }

        if ($model->x509) {
            $model->validates_at = $model->x509->getValidityFrom();
            $model->invalidates_at = $model->x509->getValidityTo();
            $model->wildcard = $model->x509->isWildcard();
        }
    }

    /**
     * @param \Hyn\Webserver\Models\SslCertificate $model
     */
    public function created($model)
    {
        if ($model->x509) {
            foreach ($model->x509->getHostnames() as $hostname) {
                $sslHostname = new SslHostname();
                $sslHostname->ssl_certificate_id = $model->id;
                $sslHostname->hostname = $hostname;
                $sslHostname->save();
            }
        }
        $this->dispatch(
            new SslCertificateCommand($model->id, 'create')
        );
    }

    public function updated($model)
    {
        $this->dispatch(
            new SslCertificateCommand($model->id, 'update')
        );
    }

    public function deleting($model)
    {
        $this->dispatch(
            new SslCertificateCommand($model->id, 'delete')
        );
    }
}
