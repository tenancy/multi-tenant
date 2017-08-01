<?php

namespace Hyn\Tenancy\Generators\Webserver\Certificates;

use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Exception\Server\MalformedServerException;
use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Hyn\Tenancy\Certificates\Solvers\TenancyHttpSolver;
use Hyn\Tenancy\Contracts\Generator\GeneratesConfiguration;
use Hyn\Tenancy\Contracts\Generator\SavesToPath;
use Hyn\Tenancy\Models\Website;

class LetsEncryptGenerator implements GeneratesConfiguration, SavesToPath
{
    /**
     * @var AcmeClient
     */
    protected $acme;

    /**
     * @var AuthorizationChallenge
     */
    protected $challenge;
    /**
     * @var TenancyHttpSolver
     */
    private $solver;

    public function __construct(AcmeClient $acme, TenancyHttpSolver $solver)
    {
        $this->acme = $acme;
        $this->solver = $solver;
    }

    /**
     * @param Website $website
     * @return string
     */
    public function generate(Website $website): string
    {
        try {
            $this->acme->registerAccount();
        } catch (MalformedServerException $e) {
            // ..
        }


    }

    /**
     * @param Website $website
     * @return string
     */
    public function targetPath(Website $website): string
    {
        return $this->solver->getWellKnownPath($this->challenge);
    }

    /**
     * @param AuthorizationChallenge $challenge
     * @return $this
     */
    public function setChallenge(AuthorizationChallenge $challenge)
    {
        $this->challenge = $challenge;
        return $this;
    }
}