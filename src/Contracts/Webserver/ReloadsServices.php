<?php

namespace Hyn\Tenancy\Contracts\Webserver;

interface ReloadsServices
{
    public function reload() : bool;
}
