<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateUserSpaces extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Request $request)
    {
        $user = $request->user();
        if (! $user instanceof User) {
            // Deny to unauthenticated (which shouldn't get this far anyhow)
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Service accounts with permission, or anyone updating their own spaces
        return $user->can('manage-users') || $user->id == $request->input('id');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,array<string>>
     */
    public function rules()
    {
        return [
            'spaces' => [
                'required',
                'array',
            ],
            'spaces.*' => [
                'exists:spaces,id',
            ],
        ];
    }
}
