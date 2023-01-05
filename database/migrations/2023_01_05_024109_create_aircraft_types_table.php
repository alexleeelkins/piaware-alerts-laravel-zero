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
        Schema::create('aircraft_types', function (Blueprint $table) {
            $table->id();

            $table->string('type')
                  ->unique();
            $table->string('manufacturer')
                  ->nullable();
            $table->string('description')
                  ->nullable();
            $table->unsignedTinyInteger('engine_count')
                  ->nullable();
            $table->string('engine_type')
                  ->nullable();

            $table->timestamps();
        });
    }
};
