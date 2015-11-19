<?php

use Hyn\MultiTenant\Tenant\Directory;

if (! function_exists('tenant_path')) {
    function tenant_path($path = '')
    {
        /** @var Directory $directory */
        $directory = app('Hyn\MultiTenant\Contracts\DirectoryContract');

        if (! $directory) {
            return;
        }

        return sprintf('%s%s', $directory->base(), $path);
    }
}
