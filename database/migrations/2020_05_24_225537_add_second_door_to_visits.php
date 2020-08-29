<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSecondDoorToVisits extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('visits', static function (Blueprint $table): void {
            $table->renameColumn('door', 'in_door');
            $table->string('out_door')->after('door')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', static function (Blueprint $table): void {
            $table->dropColumn('out_door');
            $table->renameColumn('in_door', 'door');
        });
    }
}
