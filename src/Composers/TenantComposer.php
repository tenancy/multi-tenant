<?php namespace HynMe\MultiTenant\Composers;

use Illuminate\Contracts\View\View;
use App;

class TenantComposer {


    /**
     * @param View $view
     */
    public function compose(View $view)
    {
        $view->with('_tenant', App::make('tenant.view'));
    }
}