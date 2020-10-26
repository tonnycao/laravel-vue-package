<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('region', 5)->nullable()->comment('region');
            $table->string('type', 20)->comment('type of request');
            $table->string('subtype', 20)->nullable()->comment('subtype of request');
            $table->integer('status')->comment('job status');
            $table->integer('user', null, true)->nullable()->comment('user initiated the request');
            $table->string('name', 191)->comment('name of the job');
            $table->text('summary')->nullable();
            $table->text('trace')->nullable();
            $table->smallInteger('download')->default(0);
            $table->dateTime('finished_at')->nullable();
            $table->smallInteger('system_config_id')->nullable()->comment('system config id');
            $table->timestamp('scheduled_at')->nullable()->comment('system job scheduled at');
            $table->timestamp('auto_cancelled_at')->nullable()->comment('system auto cancel time');
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
        Schema::dropIfExists('system_jobs');
    }
}
