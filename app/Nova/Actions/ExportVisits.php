<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

class ExportVisits extends DownloadExcel implements WithMapping
{
    /**
     * Name of action.
     *
     * @var string
     */
    public $name = 'Export to CSV';

    /**
     * Prepare values of each Visit to be exported.
     *
     * @param \App\Models\Visit $row
     *
     * @return array<string, \Carbon\Carbon|string|null>
     */
    public function map($row): array
    {
        return [
            'First Name' => $row->user->first_name,
            'Last Name' => $row->user->last_name,
            'Email' => $row->user->email,
            'In Time' => $row->in_time,
            'In Door' => $row->in_door,
            'Out Time' => $row->out_time,
            'Out Door' => $row->out_door,
        ];
    }
}
