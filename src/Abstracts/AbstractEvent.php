<?php

namespace Hyn\Tenancy\Abstracts;

abstract class AbstractEvent
{
    public $reason;

    public function setReason(string $reason)
    {
        $this->reason = $reason;

        return $this;
    }
}
