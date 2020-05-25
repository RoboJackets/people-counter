<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSecondDoorToVisits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('visits', static function (Blueprint $table): void {
            $table->renameColumn('door', 'in_door');
            $table->string('out_door')->after('door')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('visits', static function (Blueprint $table): void {
            $table->dropColumn('out_door');
            $table->renameColumn('in_door', 'door');
        });
    }
}
