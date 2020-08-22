<?php

namespace App\Imports;

use App\Space;
use App\Traits\CreateOrUpdateUserFromBuzzAPI;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;

class UsersImport implements WithProgressBar, WithValidation, WithHeadingRow, SkipsOnFailure, OnEachRow
{
    use Importable, CreateOrUpdateUserFromBuzzAPI;

    /**
     * @var string $space
     */
    var $space;

    /**
     * UsersImport constructor.
     *
     * @param string $team
     */
    public function __construct(string $space) {
        $this->space = $space;
    }

    /**
     * @param Row $row
     *
     * @return \App\User|null
     *
     * @throws \Exception
     */
    public function onRow(Row $row)
    {
        $row = $row->toArray();
        $identifier = $row['username'] ?? $row['gtid'] ?? $row['email'] ?? null;
        if ($identifier === null) {
            return null;
        } else {
            $identifier = strtolower($identifier);
        }
        $user = $this->createOrUpdateUserFromBuzzAPI($identifier, false);
        if ($user instanceof \App\User) {
            // Attach space to user as their default
            $space = Space::where('name', $this->space)->first();
            $user->spaces()->syncWithoutDetaching($space);
            return $user;
        } else {
            return null;
        }
    }

    /**
     * Define rules used to validate the import
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'username' => 'string|not_regex:/@/',
            'gtid' => 'digits:9',
            'email' => 'email'
        ];
    }

    /**
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures)
    {
//        var_dump($failures);
    }
}
