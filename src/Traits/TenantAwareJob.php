<?php

namespace Hyn\Tenancy\Traits;

use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Illuminate\Queue\SerializesModels;

trait TenantAwareJob
{
    /**
     * @var int The hostname ID of the previously active tenant.
     */
    protected $tenant_id;

    use SerializesModels {
        __sleep as serializedSleep;
        __wakeup as serializedWakeup;
    }

    public function __sleep()
    {
        /** @var Environment $environment */
        $environment = app(Environment::class);

        if ($environment->hostname()) {
            $this->tenant_id = $environment->hostname()->id;
        }

        $attributes = $this->serializedSleep();

        return $attributes;
    }

    public function __wakeup()
    {
        if (isset($this->tenant_id)) {

            /** @var Environment $environment */
            $environment = app(Environment::class);

            $environment->hostname(Hostname::find($this->tenant_id));
        }

        $this->serializedWakeup();
    }
}
