<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateInitialRolesPermissions extends Migration
{
    /**
     * Permissions to be used elsewhere
     *
     * @var array
     */
    public $allPermissions = [
        'manage-users',
        'manage-visits',
        'create-visits',
        'create-visits-own',
        'read-visits',
        'read-visits-own',
        'update-visits',
        'update-visits-own',
        'delete-visits',
        'delete-visits-own',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        global $allPermissions;

        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Create Permissions
        foreach ($allPermissions as $vp) {
            Permission::firstOrCreate(['name' => $vp]);
        }

        $superadmin = Role::firstOrCreate(['name' => 'super-admin']);

        $member = Role::firstOrCreate(['name' => 'member']);
        $memberRoles = [
            'create-visits-own',
            'read-visits-own',
            'update-visits-own',
            'delete-visits-own',
        ];
        $member->syncPermissions($memberRoles);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        global $allPermissions;

        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        $roles = ['super-admin', 'member'];
        foreach ($roles as $role) {
            $dbRole = Role::where('name', $role)->first();
            if (null === $dbRole) {
                continue;
            }
            $dbRole->delete();
        }

        foreach ($allPermissions as $permission) {
            $dbPerm = Permission::where('name', $permission)->first();
            if (null === $dbPerm) {
                continue;
            }
            $dbPerm->delete();
        }
    }
}
