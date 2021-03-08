<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Space;
use Illuminate\Database\Seeder;

class SCCSpaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $parentSpaces = [
            'SCC - Main' => ['max' => 84],
            'RoboJackets' => ['max' => 11],
        ];

        $sccChildSpaces = [
            'GT Motorsports' => ['max' => 12],
            'GT Off-Road' => ['max' => 6],
            'HyTech Racing' => ['max' => 6],
            'EcoCAR' => ['max' => 8],
            'Solar Racing' => ['max' => 17],
            'Wreck Racing' => ['max' => 8],
        ];

        foreach ($parentSpaces as $key => $value) {
            $space = new Space();
            $space->name = $key;
            $space->max_occupancy = $value['max'];
            $space->save();
        }

        $sccSpace = Space::where('name', '=', 'SCC - Main')->first();
        foreach ($sccChildSpaces as $key => $value) {
            $space = new Space();
            $space->name = $key;
            $space->max_occupancy = $value['max'];
            $space->parent_id = $sccSpace->id;
            $space->save();
        }
    }
}
