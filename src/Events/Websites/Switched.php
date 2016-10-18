<?php

namespace Hyn\Tenancy\Events\Websites;

use Hyn\Tenancy\Abstracts\WebsiteEvent;
use Hyn\Tenancy\Models\Website;

class Switched extends WebsiteEvent
{
    /**
     * @var Website
     */
    public $old;

    /**
     * @param Website $website
     * @return $this
     */
    public function setOld(Website $website)
    {
        $this->old = $website;

        return $this;
    }
}
