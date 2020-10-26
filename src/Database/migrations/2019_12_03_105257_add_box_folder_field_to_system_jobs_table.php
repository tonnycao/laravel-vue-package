<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBoxFolderFieldToSystemJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_jobs', function (Blueprint $table) {
            $table->string('box_folder', 255)->nullable(true)->after('date_pattern')->comment('files from which box folder');
            $table->string('box_file', 255)->nullable(true)->after('box_folder')->comment('specific box file');
        });
    }
}
