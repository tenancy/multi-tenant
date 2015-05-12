<?php namespace HynMe\MultiTenant\Observers;

use App;
use HynMe\MultiTenant\Helpers\TenantDirectoryHelper;

class WebsiteObserver
{
    /**
     * @param $model
     */
    public function updating($model)
    {
        if($model->isDirty('identifier'))
        {
            /*
             * Move tenant directories once the identifier changes
             */
            return TenantDirectoryHelper::moveBasePath($model);
        }
    }
}