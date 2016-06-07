<?php

namespace Hyn\Framework\Validation;

use Illuminate\Validation\Validator;

class ExtendedValidation extends Validator
{
    /**
     * Validates hostnames.
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     *
     * @return int
     */
    public function validateHostname($attribute, $value, $parameters)
    {
        return preg_match('/(?=^.{4,253}$)(^((?!-)[a-zA-Z0-9-]{0,62}[a-zA-Z0-9]\.)+[a-zA-Z]{2,63}$)/i', $value);
    }
}
