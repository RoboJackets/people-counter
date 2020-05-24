<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUser extends FormRequest
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
            'username' => 'required|unique:App\User',
            'email' => 'required|unique:App\User|email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'gtid' => 'required|starts_with:9|digits:9|unique:App\User',
        ];
    }
}
