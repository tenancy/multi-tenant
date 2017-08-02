<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Certificates\Solvers;

use AcmePhp\Core\Challenge\SolverInterface;
use AcmePhp\Core\Protocol\AuthorizationChallenge;

class TenancyHttpSolver implements SolverInterface
{

    /**
     * Determines whether or not the solver supports a given Challenge.
     *
     * @param AuthorizationChallenge $authorizationChallenge
     *
     * @return bool The solver supports the given challenge's type
     */
    public function supports(AuthorizationChallenge $authorizationChallenge)
    {
        return 'http-01' === $authorizationChallenge->getType();
    }

    /**
     * Solve the given authorization challenge.
     *
     * @param AuthorizationChallenge $authorizationChallenge
     */
    public function solve(AuthorizationChallenge $authorizationChallenge)
    {
        file_put_contents(
            $this->getWellKnownPath($authorizationChallenge),
            $authorizationChallenge->getPayload()
        );
    }

    /**
     * Cleanup the environments after a successful challenge.
     *
     * @param AuthorizationChallenge $authorizationChallenge
     */
    public function cleanup(AuthorizationChallenge $authorizationChallenge)
    {
        if (file_exists($path = $this->getWellKnownPath($authorizationChallenge))) {
            unlink($path);
        }
    }

    /**
     * @param AuthorizationChallenge $authorizationChallenge
     * @return string
     */
    public function getWellKnownPath(AuthorizationChallenge $authorizationChallenge): string
    {
        return public_path('.well-known/acme-challenge/%s', $authorizationChallenge->getToken());
    }
}
