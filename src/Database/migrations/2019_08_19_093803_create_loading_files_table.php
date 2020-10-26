<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoadingFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loading_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job');
            $table->string('name')->comment('filename');
            $table->string('hash', 50)->comment('file md5 hash');
            $table->string('tag', 20)->default('');
            $table->string('table')->default('');
            $table->tinyInteger('status')->default(0)->comment('status 0:downloaded, 1:loading, 2,loaded');
            $table->mediumText('info')->comment('file information');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loading_files');
    }
}
