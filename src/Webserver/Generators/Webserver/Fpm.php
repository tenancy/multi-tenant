<?php

namespace Hyn\Webserver\Generators\Webserver;

use Config;
use Hyn\Webserver\Generators\AbstractFileGenerator;

class Fpm extends AbstractFileGenerator
{
    /**
     * Generates the view that is written.
     *
     * @return \Illuminate\View\View
     */
    public function generate()
    {
        return view('webserver::webserver.fpm.configuration', [
            'website'   => $this->website,
            'base_path' => base_path(),
            'user'      => $this->website->identifier,
            'group'     => Config::get('webserver.group'),
            'config'    => Config::get('webserver.fpm'),
        ]);
    }

    /**
     * Provides the complete path to publish the generated content to.
     *
     * @return string
     */
    protected function publishPath()
    {
        return sprintf('%s%s.conf', Config::get('webserver.fpm.path'), $this->name());
    }
}
