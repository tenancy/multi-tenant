<?php

namespace Laraflock\MultiTenant\Presenters;

use Hyn\Framework\Presenters\AbstractModelPresenter;

class HostnamePresenter extends AbstractModelPresenter
{
    /**
     * @return string
     */
    public function icon()
    {
        return 'management-interface::icon.hostname';
    }

    /**
     * @return mixed
     */
    public function name()
    {
        return $this->hostname;
    }
}
