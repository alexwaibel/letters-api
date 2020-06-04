<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zips', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('zip')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('city')->nullable();
            $table->string('state_id')->nullable();
            $table->string('state_name')->nullable();
            $table->string('zcta')->nullable();
            $table->string('parent_zcta')->nullable();
            $table->string('population')->nullable();
            $table->string('density')->nullable();
            $table->string('county_fips')->nullable();
            $table->string('county_name')->nullable();
            $table->string('county_weights')->nullable();
            $table->string('county_names_all')->nullable();
            $table->string('county_fips_all')->nullable();
            $table->string('imprecise')->nullable();
            $table->string('military')->nullable();
            $table->string('timezone')->nullable();
            $table->string('age_median')->nullable();
            $table->string('male')->nullable();
            $table->string('female')->nullable();
            $table->string('married')->nullable();
            $table->string('family_size')->nullable();
            $table->string('income_household_median')->nullable();
            $table->string('income_household_six_figure')->nullable();
            $table->string('home_ownership')->nullable();
            $table->string('home_value')->nullable();
            $table->string('rent_median')->nullable();
            $table->string('education_college_or_above')->nullable();
            $table->string('labor_force_participation')->nullable();
            $table->string('unemployment_rate')->nullable();
            $table->string('race_white')->nullable();
            $table->string('race_black')->nullable();
            $table->string('race_asian')->nullable();
            $table->string('race_native')->nullable();
            $table->string('race_pacific')->nullable();
            $table->string('race_other')->nullable();
            $table->string('race_multiple')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zips');
    }
}
