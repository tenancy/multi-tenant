<?php

namespace Hyn\Framework\Traits;

trait ValidatableTrait
{
    /**
     * Complete class name for model validation class.
     *
     * @var string
     */
    protected $validator;

    /**
     * @var
     */
    protected $validatorInstance;

    public function validator()
    {
        /*        if ( ! $this->validator or ! class_exists($this->validator))
        {
            throw new \Exception('Please set the $validator property to your validator path.');
        }

        if ( ! $this->validatorInstance)
        {
            $this->validatorInstance = new $this->validator($this);
        }

        return $this->validatorInstance;*/
    }
}
