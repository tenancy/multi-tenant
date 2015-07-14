<?php namespace HynMe\MultiTenant\Presenters;

use HynMe\Framework\Presenters\AbstractModelPresenter;

class TenantPresenter extends AbstractModelPresenter
{
    /**
     * @return string
     */
    public function icon()
    {
        return 'tenant';
    }

    /**
     * @return mixed
     */
    public function name()
    {
        return $this->entity->name;
    }
}