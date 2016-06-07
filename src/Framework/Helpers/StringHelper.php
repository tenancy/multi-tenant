<?php

namespace Hyn\Framework\Helpers;

use Auth;

class StringHelper
{
    /**
     * Replace a column definition string with the proper model data
     * Can also handle relations (if defined properly).
     *
     * @param $string
     * @param null  $model
     * @param array $forced
     *
     * @return mixed
     */
    public static function replaceSemiColon($string, $model = null, $forced = [])
    {
        return preg_replace_callback('/:([a-zA-Z0-9._]+)/', function ($matches) use ($model, $forced) {
            $found = $matches[1];

            // if this concerns a visitor
            if (Auth::check() && preg_match('/^visitor_/', $found)) {
                $column = str_replace('visitor_', null, $found);

                return Auth::user()->{$column};
            }

            // find in the overrule array
            if (! is_null(array_get($forced, $found))) {
                return array_get($forced, $found);
            }

            // Check if we need a property of a specific model
            $property = $matches[1];
            $return = $model;

            if (str_contains($matches[1], '.') && substr($matches[1], -1) != '.') {
                $property = explode('.', $matches[1]);
            }

            // Assume it's a property of the current model
            if (! is_array($property)) {
                $return = $model->{$property};
            }

            if (is_array($property)) {
                $return = $model->{$property[0]}[$property[1]];
            }

            return $return;
        }, $string);
    }
}
