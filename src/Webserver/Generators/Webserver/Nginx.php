<?php

namespace Hyn\Webserver\Generators\Webserver;

use Config;
use Hyn\Webserver\Generators\AbstractFileGenerator;

class Nginx extends AbstractFileGenerator
{
    /**
     * Generates the view that is written.
     *
     * @return \Illuminate\View\View
     */
    public function generate()
    {
        return view('webserver::webserver.nginx.configuration', [
            'website'     => $this->website,
            'public_path' => public_path(),
            'log_path'    => Config::get('webserver.log.path')."/nginx-{$this->website->id}-{$this->website->identifier}",
            'config'      => Config::get('webserver.nginx'),
            'fpm_port'    => Config::get('webserver.fpm.port'),
        ]);
    }

    /**
     * Provides the complete path to publish the generated content to.
     *
     * @return string
     */
    protected function publishPath()
    {
        return sprintf('%s%s.conf', Config::get('webserver.nginx.path'), $this->name());
    }
}
