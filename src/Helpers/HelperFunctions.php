<?php

use Laraflock\MultiTenant\Tenant\Directory;

if (! function_exists('tenant_path')) {
    function tenant_path($path = '')
    {
        /** @var Directory $directory */
        $directory = app('Laraflock\MultiTenant\Contracts\DirectoryContract');

        if(! $directory)
        {
            return null;
        }

        return sprintf("%s%s", $directory->base(), $path);
    }
}
