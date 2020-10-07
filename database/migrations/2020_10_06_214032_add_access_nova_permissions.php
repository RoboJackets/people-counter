<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddAccessNovaPermissions extends Migration
{
    /**
     * Permissions to be used elsewhere.
     *
     * @var array<string>
     */
    public $allPermissions = [
        'access-nova',
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

        foreach ($this->allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->allPermissions as $permission) {
            $dbPerm = Permission::where('name', $permission)->first();

            if (null === $dbPerm) {
                return;
            }

            $dbPerm->delete();
        }
    }
}
