<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserSpaces extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $requestingUser = $this->user();
        $targetUser = $this->route('user');
        if (! $requestingUser instanceof User) {
            // Deny to unauthenticated (which shouldn't get this far anyhow)
            return false;
        }

        if ($requestingUser->isSuperAdmin()) {
            return true;
        }

        // Service accounts with permission, or anyone updating their own spaces
        // @phpstan-ignore-next-line
        return $requestingUser->can('manage-users') || $requestingUser->id === $targetUser->id;
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
