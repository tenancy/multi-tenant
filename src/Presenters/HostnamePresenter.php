<?php namespace HynMe\MultiTenant\Presenters;

use HynMe\Framework\Presenters\AbstractModelPresenter;

class HostnamePresenter extends AbstractModelPresenter
{


    /**
     * @return string
     */
    public function icon()
    {
        return 'hostname';
    }

    /**
     * @return mixed
     */
    public function name()
    {
        return $this->hostname;
    }
}