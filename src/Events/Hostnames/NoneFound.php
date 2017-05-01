<?php

namespace Hyn\Tenancy\Events\Hostnames;

use Illuminate\Http\Request;

class NoneFound
{
    /**
     * @var Request
     */
    public $request;

    /**
     * NoneFound constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
