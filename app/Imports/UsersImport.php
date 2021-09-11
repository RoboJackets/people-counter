<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Space;
use App\Models\User;
use App\Traits\CreateOrUpdateUserFromBuzzAPI;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class UsersImport implements WithProgressBar, WithValidation, WithHeadingRow, OnEachRow, SkipsOnFailure, SkipsOnError
{
    use Importable;
    use CreateOrUpdateUserFromBuzzAPI;
    use SkipsFailures;
    use SkipsErrors;

    /**
     * The space to attach.
     *
     * @var string
     */
    private $space;

    /**
     * UsersImport constructor.
     *
     * @param  string  $space
     */
    public function __construct(string $space)
    {
        $this->space = $space;
    }

    /**
     * Converts a row to a user.
     *
     * @param  Row  $row
     * @return \App\Models\User|null
     *
     * @throws \Exception
     */
    public function onRow(Row $row)
    {
        $row = $row->toArray();
        $identifier = $row['username'] ?? $row['gtid'] ?? $row['email'] ?? null;
        if (null === $identifier) {
            return null;
        } else {
            Log::info('Importing '.$identifier);
            $identifier = is_int($identifier) ? $identifier : trim(strtolower($identifier));
        }
        try {
            $user = $this->createOrUpdateUserFromBuzzAPI($identifier, false);
        } catch (\Throwable $e) {
            Log::error('Exception when importing '.$identifier, [$e->getMessage()]);

            return null;
        }

        if (null === $user && null !== $row['email']) {
            // Probably a non-primary email, search again with stripped username
            $identifier = strtok($row['email'], '@');
            if (false === $identifier) {
                Log::notice('Attempted to retry import for '.$row['email'].' but username extract failed');
            } else {
                Log::info('Importing (retry) '.$identifier);
                try {
                    $user = $this->createOrUpdateUserFromBuzzAPI($identifier, false);
                } catch (\Throwable $e) {
                    Log::error('Exception when importing '.$identifier, [$e->getMessage()]);

                    return null;
                }
            }
        }

        if ($user instanceof User) {
            // Attach space to user as their default
            $space = Space::where('name', $this->space)->first();
            $user->spaces()->syncWithoutDetaching($space);

            return $user;
        }

        return null;
    }

    /**
     * Define rules used to validate the import.
     *
     * @return array<string,string>
     */
    public function rules(): array
    {
        return [
            'username' => 'string|not_regex:/@/',
            'gtid' => 'digits:9',
            'email' => 'email',
        ];
    }
}
