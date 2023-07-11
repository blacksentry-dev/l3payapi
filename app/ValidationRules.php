<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class ValidationRules
{
    public static function rules()
    {
        return [
            'email' => 'required|string|email|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string',
        ];
    }
}