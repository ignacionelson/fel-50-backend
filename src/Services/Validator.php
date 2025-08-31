<?php

namespace App\Services;

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class Validator
{
    public static function validate($data, $rules)
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            try {
                $rule->assert($data[$field] ?? null);
            } catch (NestedValidationException $exception) {
                $errors[$field] = $exception->getMessages();
            }
        }

        return empty($errors) ? null : $errors;
    }

    public static function userRegistration()
    {
        return [
            'first_name' => v::notEmpty()->length(2, 100)->alpha(),
            'last_name' => v::notEmpty()->length(2, 100)->alpha(),
            'email' => v::notEmpty()->email(),
            'password' => v::notEmpty()->length(6, null),
            'phone_number' => v::optional(v::phone())
        ];
    }

    public static function userLogin()
    {
        return [
            'email' => v::notEmpty()->email(),
            'password' => v::notEmpty()
        ];
    }
}