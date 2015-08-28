<?php

namespace Laraflock\MultiTenant\Presenters;

use HynMe\Framework\Presenters\AbstractModelPresenter;

class TenantPresenter extends AbstractModelPresenter
{
    /**
     * @return string
     */
    public function icon()
    {
        return 'management-interface::icon.tenant';
    }

    /**
     * @return mixed
     */
    public function name()
    {
        return $this->entity->name;
    }
}
