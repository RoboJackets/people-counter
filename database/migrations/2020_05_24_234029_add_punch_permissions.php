<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddPunchPermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        Permission::firstOrCreate(['name' => 'record-punches']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dbPerm = Permission::where('name', 'record-punches')->first();

        if (null === $dbPerm) {
            return;
        }

        $dbPerm->delete();
    }
}
