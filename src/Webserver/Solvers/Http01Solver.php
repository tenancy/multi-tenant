<?php

namespace Hyn\Webserver\Solvers;

use Hyn\Webserver\Abstracts\AbstractSolver;
use Hyn\Webserver\Commands\WebserverCommand;

class Http01Solver extends AbstractSolver
{
    protected function handle()
    {
        // write to docroot waiting for that command.
        if ($this->request->hostname->website) {
            app('Illuminate\Contracts\Bus\Dispatcher')
                ->dispatchNow(new WebserverCommand($this->request->hostname->website_id, 'update'));
        }
    }
}