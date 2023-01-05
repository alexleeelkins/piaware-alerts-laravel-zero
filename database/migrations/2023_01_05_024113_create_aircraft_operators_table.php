<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aircraft_operators', function (Blueprint $table) {
            $table->id();

            $table->string('icao')
                  ->unique();
            $table->string('iata')
                  ->nullable();
            $table->string('callsign')
                  ->nullable();
            $table->string('name')
                  ->nullable();
            $table->string('country')
                  ->nullable();
            $table->string('location')
                  ->nullable();
            $table->string('phone')
                  ->nullable();
            $table->string('shortname')
                  ->nullable();
            $table->string('url')
                  ->nullable();
            $table->string('wiki_url')
                  ->nullable();

            $table->timestamps();
        });
    }
};
