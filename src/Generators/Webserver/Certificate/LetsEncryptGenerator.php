<?php

namespace Hyn\Tenancy\Generators\Webserver\Certificate;

use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Challenge\SolverInterface;
use AcmePhp\Core\Exception\Server\MalformedServerException;
use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Hyn\Tenancy\Contracts\Generator\GeneratesConfiguration;
use Hyn\Tenancy\Contracts\Generator\SavesToPath;
use Hyn\Tenancy\Exceptions\CertificateRequestFailure;
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
     * @var SolverInterface
     */
    protected $solver;

    public function __construct(AcmeClient $acme, SolverInterface $solver)
    {
        $this->acme = $acme;
        $this->solver = $solver;
    }

    /**
     * @param Website $website
     * @return string
     * @throws CertificateRequestFailure
     */
    public function generate(Website $website): string
    {
        try {
            $this->acme->registerAccount();
        } catch (MalformedServerException $e) {
            // ..
        }

        /** @var Hostname $commonName */
        $commonName = $website->hostnames->first();

        if (!$commonName) {
            throw new CertificateRequestFailure("No commonName available for website {$website->uuid}");
        }

        $challenges = $this->acme->requestAuthorization($commonName->fqdn);

        $challenge = collect($challenges)->first(function ($challenge) {
            return $this->solver->supports($challenge);
        });

        $this->solver->solve($challenge);

        $check = $this->acme->challengeAuthorization($challenge);

        if (!isset($check['status']) || $check['status'] !== 'valid') {
            throw new CertificateRequestFailure();
        }

        $name = new DistinguishedName(
            $commonName->fqdn,
            null, null, null, null, null,
            $website->customer ? $website->customer->email : config('mail.from.address'),
            $website->hostnames->reject(function ($hostname) use ($commonName) {
                return $hostname->fqdn === $commonName->fqdn;
            })->pluck('fqdn')
        );

        $csr = new CertificateRequest(
            $name,
            $keyPair = (new KeyPairGenerator())->generateKeyPair()
        );

        $response = $this->acme->requestCertificate($commonName->fqdn, $csr);

        $certificate = $response->getCertificate();

dd($certificate);

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
