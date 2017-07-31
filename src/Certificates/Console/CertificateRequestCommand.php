<?php

namespace Hyn\Tenancy\Certificates\Console;

use AcmePhp\Core\AcmeClient;
use Illuminate\Console\Command;

class CertificateRequestCommand extends Command
{
    protected $signature = 'tenancy:certificate {tenant}';

    protected $description = 'Requests new Lets Encrypt certificate for tenant';

    public function handle(AcmeClient $acme)
    {

    }
}
