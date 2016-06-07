<?php

namespace Hyn\Webserver\Generators\Database;

use Hyn\Webserver\Abstracts\AbstractGenerator;
use Hyn\MultiTenant\Models\Website;

class Database extends AbstractGenerator
{
    /**
     * @var Website
     */
    protected $website;

    /**
     * @param Website $website
     */
    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    /**
     * @return string
     */
    public function name()
    {
        // .. not used?
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return bool
     */
    public function onRename($from, $to)
    {
        // TODO: Implement onRename() method.
    }

    /**
     * @return bool
     */
    public function onCreate()
    {
        return $this->website->database->create();
    }

    /**
     * @return bool
     */
    public function onUpdate()
    {
        // TODO: Implement onUpdate() method.
    }

    /**
     * @return bool
     */
    public function onDelete()
    {
        return $this->website->database->delete();
    }
}
