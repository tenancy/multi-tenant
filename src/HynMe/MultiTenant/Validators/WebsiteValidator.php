<?php namespace HynMe\MultiTenant\Validators;

use HynMe\Framework\Validators\AbstractValidator;

class WebsiteValidator extends AbstractValidator
{
    protected $rules = [
        'identifier' => ['required', 'unique:websites,identifier','alpha_dash'],
        'tenant_id' => ['required', 'exists:tenants,id'],
    ];
}