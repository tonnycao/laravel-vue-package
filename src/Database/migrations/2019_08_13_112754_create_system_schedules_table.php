<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 191)->comment('schedule name');
            $table->string('region', 5)->nullable()->comment('region');
            $table->integer('created_by')->comment('Created by');
            $table->integer('updated_by')->nullable()->comment('Updated by');
            $table->integer('deleted_by')->nullable()->comment('Deleted by');
            $table->string('frequency_type', 10)->comment('periodic, quarterly, weekly, daily, weekdays, weekends');
            $table->tinyInteger('week')->nullable()->comment('Which week of quarter or period?');
            $table->tinyInteger('day')->nullable()->comment('Which day of period, quarter, week?');
            $table->string('time', '5')->comment('Approximate time in the selected timezone: example: 18:00');
            $table->string('timezone', '191')->comment('Timezone');
            $table->string('type', 20)->comment('what type');
            $table->string('subtype', 20)->comment('what subtype');
            $table->tinyInteger('download')->default(0)->comment('whether to download 0:no, 1: yes');
            $table->tinyInteger('is_active')->default(0)->comment('status 0:inactive, 1: active');
            $table->timestamps();

            $table->unique([
                'frequency_type',
                'region',
                'type',
                'subtype',
                'is_active',
            ], 'uniq_schedule');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_schedules');
    }
}
