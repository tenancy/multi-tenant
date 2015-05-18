<?php namespace HynMe\MultiTenant\Helpers;

use File;
use HynMe\MultiTenant\Models\Website;

class TenantDirectoryHelper
{
    /**
     * Moves tenant directory from old base to new base
     *
     * @param Website $website
     * @return bool
     */
    public static function moveBasePath(Website $website)
    {
        if($website->directory->old_base())
        {
            return File::move($website->directory->old_base(), $website->directory->base());
        }
    }

    /**
     * Creates tenant directory and sub directory if they do not exist
     * @param Website $website
     * @return bool
     */
    public static function createPaths(Website $website)
    {
        if(!File::exists($website->directory->base()))
            return $website->directory->create();
    }
}