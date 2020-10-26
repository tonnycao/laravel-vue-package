<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBoxFolderFieldToSystemSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_schedules', function (Blueprint $table) {
            $table->string('box_folder', 255)->nullable(true)->after('date_pattern')->comment('files from which box folder');
        });
    }
}
