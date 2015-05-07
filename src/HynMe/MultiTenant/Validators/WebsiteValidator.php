<?php namespace HynMe\MultiTenant\Validators;

use HynMe\Framework\Models\AbstractModel;
use HynMe\Framework\Validators\AbstractValidator;
use Request;

class WebsiteValidator extends AbstractValidator
{
    protected $rules = [
        'identifier' => ['required', 'unique:websites,identifier'],
        'tenant_id' => ['required', 'exists:tenants,id'],
    ];
}