<?php

namespace Hyn\MultiTenant\Presenters;

use Hyn\Framework\Presenters\AbstractModelPresenter;

class WebsitePresenter extends AbstractModelPresenter
{
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
        return 'management-interface::icon.website';
    }

    /**
     * @return mixed
     */
    public function name()
    {
        return $this->identifier;
    }
}
