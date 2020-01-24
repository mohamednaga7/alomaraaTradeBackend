<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('brand_id');
            $table->string('brand');
            $table->string('slug')->unique();
            $table->string('model');
            $table->string('modelArabic');
            $table->integer('year');
            $table->integer('capacity');
            $table->integer('cylinders');
            $table->integer('horsePower');
            $table->integer('maxSpeed');
            $table->integer('numberOfDoors');
            $table->integer('numberOfSeats');
            $table->string('bodyTypeEnglish');
            $table->string('bodyTypeArabic');
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
        Schema::dropIfExists('cars');
    }
}
