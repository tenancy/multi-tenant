<?php

namespace Hyn\MultiTenant\Composers;

use App;
use Illuminate\Contracts\View\View;

class TenantComposer
{
    /**
     * @param View $view
     */
    public function compose(View $view)
    {
        $view->with('_tenant', App::make('tenant.view'));
    }
}
