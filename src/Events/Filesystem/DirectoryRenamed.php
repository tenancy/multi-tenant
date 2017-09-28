<?php

namespace Hyn\Tenancy\Events\Filesystem;

use Hyn\Tenancy\Abstracts\FilesystemEvent;

class DirectoryRenamed extends FilesystemEvent
{

    /**
     * @var string
     */
    public $old;

    /**
     * @param string $uuid
     * @return $this
     */
    public function setOld(string $uuid)
    {
        $this->old = $uuid;

        return $this;
    }
}