<?php

namespace Hyn\Webserver\Commands;

use Hyn\Framework\Commands\AbstractRootCommand;
use Hyn\Webserver\Generators\Webserver\Ssl;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SslCertificateCommand extends AbstractRootCommand implements SelfHandling, ShouldBeQueued
{

    /**
     * @var Certificate
     */
    protected $certificate;

    /**
     * @var string
     */
    protected $action;

    /**
     * Create a new command instance.
     *
     * @param        $certificate_id
     * @param string $action
     */
    public function __construct($certificate_id, $action = 'update')
    {
        parent::__construct();

        $this->certificate = app('Hyn\Webserver\Contracts\SslRepositoryContract')->findById($certificate_id);
        $this->action = $action;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if (! in_array($this->action, ['create', 'update', 'delete'])) {
            return;
        }

        $action = sprintf('on%s', ucfirst($this->action));

        (new Ssl($this->certificate))->{$action}();
    }
}
