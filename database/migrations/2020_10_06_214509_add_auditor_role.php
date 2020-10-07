<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddAuditorRole extends Migration
{
    /**
     * Permissions to be used elsewhere.
     *
     * @var array<string>
     */
    public $allPermissions = [
        'read-spaces',
        'read-users',
        'read-visits',
        'access-nova'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Create Permissions
        foreach ($this->allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $auditorRole = Role::firstOrCreate(['name' => 'auditor']);
        $auditorRole->syncPermissions($this->allPermissions);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        $roles = ['auditor'];
        foreach ($roles as $role) {
            $dbRole = Role::where('name', $role)->first();
            if (null === $dbRole) {
                continue;
            }
            $dbRole->delete();
        }

        foreach ($this->allPermissions as $permission) {
            $dbPerm = Permission::where('name', $permission)->first();
            if (null === $dbPerm) {
                continue;
            }
            $dbPerm->delete();
        }
    }
}
