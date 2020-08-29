<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddSpacePermissions extends Migration
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

        Permission::firstOrCreate(['name' => 'manage-spaces']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $dbPerm = Permission::where('name', 'manage-spaces')->first();

        if (null === $dbPerm) {
            return;
        }

        $dbPerm->delete();
    }
}
