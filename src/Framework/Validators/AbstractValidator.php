<?php

namespace Hyn\Framework\Validators;

use App;
use Hyn\Framework\Models\AbstractModel;
use Input;
use Request;
use Validator;

abstract class AbstractValidator
{
    /**
     * @param AbstractModel $model
     *
     * @return bool
     */
    public function create(AbstractModel $model)
    {
        if (is_null($this->rules)) {
            return false;
        }
        // replicate the model if it exists
        if ($model->exists) {
            $model = $model->replicate(['id']);
        }

        $values = $this->parseRequestValues($model);

        $validator = $this->make($values, $this->rules, $model);

        if ($validator->fails()) {
            return $validator;
        }

        $model->fill($values);

        return $model;
    }

    /**
     * @param AbstractModel $model
     *
     * @return bool
     */
    public function updating(AbstractModel $model)
    {
        if (is_null($this->rules)) {
            return false;
        }

        // if not yet existing, forward to create method
        if (! $model->exists) {
            return $this->create($model);
        }

        // get the rules for only those attributes that have been changed
        $rules = array_only($this->rules, array_keys(Input::all()));

        // no rules available
        if (empty($rules)) {
            return false;
        }

        $values = $this->parseRequestValues($model);

        $validator = $this->make($values, $rules, $model);

        if ($validator->fails()) {
            return $validator;
        }

        $model->fill($values);

        return $model;
    }

    /**
     * @param AbstractModel $model
     *
     * @return AbstractModel|\Illuminate\Validation\Validator
     */
    public function deleting(AbstractModel $model)
    {
        $values = $this->parseRequestValues($model);

        $values = array_merge($values, ['id' => $model->id]);

        $validator = $this->make($values, [
            'id'      => ["exists:{$model->getTable()},id", 'required', 'numeric', 'min:1'],
            'confirm' => ['required', 'boolean', 'accepted'],
        ], $model);

        if ($validator->fails()) {
            return $validator;
        }

        $model->delete();

        return $model;
    }

    /**
     * Loads a validator object.
     *
     * @param $values
     * @param $rules
     * @param $model
     * @return \Illuminate\Validation\Validator
     */
    protected function make($values, $rules, $model)
    {
        foreach ($rules as $attribute => &$ruleset) {
            foreach ($ruleset as &$rule) {
                if ($model->exists && preg_match('/^unique:([^,]+),([^,]+)$/', $rule)) {
                    $rule = "{$rule},{$model->id}";
                }
            }
        }

        // multi connection verifier
        $verifier = App::make('validation.presence');
        $verifier->setConnection($model->getConnectionName());

        $validator = Validator::make($values, $rules);
        $validator->setPresenceVerifier($verifier);

        return $validator;
    }

    /**
     * Parses request values, without the token.
     *
     * @param AbstractModel $model
     * @return array
     */
    protected function parseRequestValues(AbstractModel $model)
    {
        $values = array_merge($model->getAttributes(), Input::all());

        return array_except($values, ['_token']);
    }

    /**
     * Parses requests to the controller for interactions with models.
     *
     * @param AbstractModel $model
     *
     * @param null          $redirect
     * @return $this|bool|AbstractModel|null
     */
    public function catchFormRequest(AbstractModel $model, $redirect = null)
    {
        // use abstract validator
        if (Request::method() != 'GET') {
            switch (Request::method()) {

                case 'POST':
                case 'UPDATE':
                    $model = $model->exists ? $this->updating($model) : $this->create($model);
                    $action = 'save';
                    break;
                case 'DELETE':
                    $model = $this->deleting($model);
                    $action = 'delete';
                    break;
                case 'HEAD':
                    $action = 'touch';
                    break;
                default:
                    return false;
            }

            if ($model instanceof \Illuminate\Validation\Validator) {
                return redirect()->back()->withErrors($model->errors())->withInput();
            }

            $model->{$action}();

            return $redirect ? $redirect : true;
        } else {
            return false;
        }
    }
}
