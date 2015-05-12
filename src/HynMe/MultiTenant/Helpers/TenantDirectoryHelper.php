<?php namespace HynMe\MultiTenant\Helpers;

use File;
use HynMe\MultiTenant\Models\Website;

class TenantDirectoryHelper
{
    public static function moveBasePath(Website $website)
    {
        if($website->directory()->old_base())
        {
            return File::move($website->directory()->old_base(), $website->directory()->base());
        }
    }
}