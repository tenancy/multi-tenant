<?php

namespace Hyn\Webserver\Abstracts;

use Hyn\LetsEncrypt\Contracts\ChallengeSolverContract;
use Hyn\LetsEncrypt\Resources\Challenge;
use Hyn\Webserver\Models\LetsEncrypt\Request;

abstract class AbstractSolver implements ChallengeSolverContract
{

    /**
     * @var Challenge
     */
    protected $challenge;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Solves a certain challenge.
     *
     * Return false if not possible.
     *
     * @param Challenge $challenge
     * @param array     $payload
     *
     * @return bool
     */
    final public function solve(Challenge $challenge, $payload = [])
    {
        $this->request = new Request([
            'solver'      => __CLASS__,
            'expires_at'  => $challenge->getExpires(),
            'hostname_id' => $challenge->getCertificate()->getIdentifier(),
        ]);

        $this->request->save();

        // forward the request to the extended class
        $this->handle();
    }

    abstract protected function handle();

    final public function __destruct()
    {
        if ($this->request->isDirty()) {
            $this->request->save();
        }
    }
}
