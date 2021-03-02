<?php

namespace App\Nova\Actions;

use App\Models\Visit;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

class ExportVisits extends DownloadExcel implements WithMapping
{
    /**
     * @var string $name
     */
    public $name = 'Export to CSV';

    /**
     * @param Visit $visit
     *
     * @return array
     */
    public function map($visit): array
    {
        return [
            'First Name' => $visit->user->first_name,
            'Last Name' => $visit->user->last_name,
            'Email' => $visit->user->email,
            'In Time' => $visit->in_time,
            'In Door' => $visit->in_door,
            'Out Time' => $visit->out_time,
            'Out Door' => $visit->out_door
        ];
    }
}
