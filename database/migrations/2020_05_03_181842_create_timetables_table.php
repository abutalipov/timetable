<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimetablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->time('time_start');
            $table->time('time_end');
            $table->date('event_date');
            $table->bigInteger('user_id')->nullable()->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->bigInteger('group_id')->nullable()->unsigned();
            $table->foreign('group_id')->on('groups')->references('id')->onDelete('restrict')->onUpdate('restrict');
            $table->bigInteger('location_id')->nullable()->unsigned();
            $table->foreign('location_id')->on('locations')->references('id')->onDelete('restrict')->onUpdate('restrict');
            $table->bigInteger('occupation_id')->nullable()->unsigned();
            $table->foreign('occupation_id')->on('occupations')->references('id')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::dropIfExists('timetables');
    }
}
