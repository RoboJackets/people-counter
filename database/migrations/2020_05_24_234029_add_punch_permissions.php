<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

class AddPunchPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        Permission::firstOrCreate(['name' => 'record-punches']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $dbPerm = Permission::where('name', 'record-punches')->first();
        if (null !== $dbPerm) {
            $dbPerm->delete();
        }
    }
}
