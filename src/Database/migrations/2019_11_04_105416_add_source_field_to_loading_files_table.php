<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSourceFieldToLoadingFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loading_files', function (Blueprint $table) {
            $table->string('source', 5)->default('mail')->after('table')->comment('source where the file was fetched from');
        });
    }
}
