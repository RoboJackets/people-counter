<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVisit extends FormRequest
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
     * @return array<string,array<string>>
     */
    public function rules()
    {
        return [
            'gtid' => [
                'starts_with:9',
                'digits:9',
            ],
            'in_door' => [
                'string',
            ],
            'out_door' => [
                'string',
            ],
            'in_time' => [
                'date',
            ],
            'out_time' => [
                'date',
            ],
        ];
    }
}
