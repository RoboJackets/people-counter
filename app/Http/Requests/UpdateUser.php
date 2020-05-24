<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Handled in Policy
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules()
    {
        return [
            'username' => 'unique:App\User|nullable',
            'email' => 'unique:App\User|email|nullable',
            'first_name' => 'string|nullable',
            'last_name' => 'string|nullable',
            'gtid' => 'starts_with:9|digits:9|unique:App\User|nullable',
        ];
    }
}
