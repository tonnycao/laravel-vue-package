<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSourceFieldToSystemJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_jobs', function (Blueprint $table) {
            $table->string('source', 5)->default('mail')->after('download')->comment('source where the file was fetched from');
            $table->string('pattern', 50)->nullable()->after('source')->comment('file name pattern to match');
            $table->string('date_pattern', 30)->nullable()->after('pattern')->comment('file name pattern to match date pattern');
        });
    }
}
