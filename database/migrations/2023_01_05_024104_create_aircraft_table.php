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
        Schema::create('aircraft', function (Blueprint $table) {
            $table->id();

            $table->string('hex');
            $table->string('flight')
                  ->nullable();
            $table->decimal('latitude', 8, 6)
                  ->nullable();
            $table->decimal('longitude', 9, 6)
                  ->nullable();
            $table->decimal('knots', 5)
                  ->nullable();
            $table->unsignedInteger('altitude')
                  ->nullable();
            $table->string('registration')
                  ->nullable();
            $table->string('type')
                  ->nullable();

            $table->timestamps();
        });
    }
};
