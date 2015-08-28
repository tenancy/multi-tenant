<?php

namespace Laraflock\MultiTenant\Validators;

use HynMe\Framework\Validators\AbstractValidator;

class HostnameValidator extends AbstractValidator
{
    protected $rules = [
        'hostname'     => ['required', 'hostname'],
        'website_id'   => ['required', 'exists:websites,id'],
        'tenant_id'    => ['required', 'exists:tenants,id'],
        'sub_of'       => ['exists:hostnames,id'],
        'prefer_https' => ['boolean'],
        'redirect_to'  => ['exists:hostnames,id'],
    ];
}
