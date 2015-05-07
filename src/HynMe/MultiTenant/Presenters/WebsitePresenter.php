<?php namespace HynMe\MultiTenant\Presenters;

use Laracasts\Presenter\Presenter;

class WebsitePresenter extends Presenter
{
    /**
     * Shows summary of hostnames
     * @return string
     */
    public function hostnamesSummary()
    {
        $hostnames = $this->hostnames->lists('hostname');
        return implode(", ", array_splice($hostnames, 0, 5));
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
        return 'website';
    }

    /**
     * @return mixed
     */
    public function name()
    {
        return $this->identifier;
    }
}