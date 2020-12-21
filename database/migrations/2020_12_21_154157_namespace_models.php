<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

class NamespaceModels extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('model_has_roles')
            ->update(['model_type' => User::class]);
        DB::table('model_has_permissions')
            ->update(['model_type' => User::class]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('model_has_roles')
            ->update(['model_type' => 'App\User']);
        DB::table('model_has_permissions')
            ->update(['model_type' => 'App\User']);
    }
}
