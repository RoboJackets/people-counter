<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisit extends FormRequest
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
            'gtid' => 'required|starts_with:9|digits:9',
            'in_door' => 'string|required',
            'out_door' => 'string|required_with:out_time',
            'in_time' => 'date|required',
            'out_time' => 'date|required_with:out_door',
        ];
    }
}
