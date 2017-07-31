<?php

namespace Hyn\Tenancy\Certificates\Console;

use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Exception\Server\MalformedServerException;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Illuminate\Console\Command;

class CertificateRequestCommand extends Command
{
    protected $signature = 'tenancy:certificate {website}';

    protected $description = 'Requests new Lets Encrypt certificate for tenant';

    public function handle(AcmeClient $acme, WebsiteRepository $websites)
    {
        $website = $this->argument('website');

        $website = $websites->findByUuid($website);

        if (!$website) {
            throw new \InvalidArgumentException("Invalid website");
        }

        try {
            $data = $acme->registerAccount();
        } catch (MalformedServerException $e) {
        }


    }
}
