<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('report_id_user')->nullable()->comment('Optional for report user');
            $table->string('type_report')->comment('user / bug in lowercase');
            $table->text('message');
            $table->integer('status')->comment('Status : - 10 : Create, - 0 : Close, - 1 : Progress');
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
        Schema::dropIfExists('reports');
    }
}
