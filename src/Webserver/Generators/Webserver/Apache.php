<?php

namespace Hyn\Webserver\Generators\Webserver;

use Config;
use Hyn\Webserver\Generators\AbstractFileGenerator;

class Apache extends AbstractFileGenerator
{
    /**
     * Generates the view that is written.
     *
     * @return \Illuminate\View\View
     */
    public function generate()
    {
        return view('webserver::webserver.apache.configuration', [
            'website'     => $this->website,
            'public_path' => public_path(),
            'log_path'    => Config::get('webserver.log.path')."/apache-{$this->website->id}-{$this->website->identifier}",
            'base_path'   => base_path(),
            'config'      => Config::get('webserver.apache'),
        ]);
    }

    /**
     * Provides the complete path to publish the generated content to.
     *
     * @return string
     */
    protected function publishPath()
    {
        return sprintf('%s%s.conf', Config::get('webserver.apache.path'), $this->name());
    }
}
