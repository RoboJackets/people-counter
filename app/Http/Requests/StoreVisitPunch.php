<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreVisitPunch extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Request $request)
    {
        $user = $this->user();
        if (! $user instanceof User) {
            // Deny to unauthenticated (which shouldn't get this far anyhow)
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Service accounts with permission, or anyone recording their own punch
        return $user->can('record-punches') || $user->gtid === $request->input('gtid');
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
            'door' => 'required|string',
            'space_id' => 'integer|exists:spaces,id',
        ];
    }
}
