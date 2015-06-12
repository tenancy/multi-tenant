<?php namespace HynMe\MultiTenant\Observers;

use App;
use HynMe\MultiTenant\Helpers\TenantDirectoryHelper;
use Illuminate\Foundation\Bus\DispatchesCommands;

class WebsiteObserver
{
    use DispatchesCommands;

    /**
     * @param $model
     * @return boolean
     */
    public function updating($model)
    {
        if($model->isDirty('identifier'))
        {
            /*
             * Move tenant directories once the identifier changes
             */
            return $model->directory->move();
        }


    }

    public function creating($model)
    {
    }

    /**
     * @param $model
     */
    public function created($model)
    {
        $model->directory->create();
    }

    public function deleted($model)
    {
        $model->directory->delete();
    }
}