<?php

namespace Hyn\Tenancy\Abstracts;

use Hyn\Tenancy\Models\Website;
use Illuminate\Contracts\Filesystem\Filesystem;

abstract class FilesystemEvent extends AbstractEvent
{
    /**
     * @var Filesystem
     */
    public $filesystem;
    /**
     * @var Website
     */
    public $website;

    public function __construct(Website $website, Filesystem $filesystem)
    {
        $this->website = $website;
        $this->filesystem = $filesystem;
    }
}