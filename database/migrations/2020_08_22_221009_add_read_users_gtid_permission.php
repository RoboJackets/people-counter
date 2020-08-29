<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddReadUsersGtidPermission extends Migration
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

        Permission::firstOrCreate(['name' => 'read-users-gtid']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $dbPerm = Permission::where('name', 'read-users-gtid')->first();

        if (null === $dbPerm) {
            return;
        }

        $dbPerm->delete();
    }
}
