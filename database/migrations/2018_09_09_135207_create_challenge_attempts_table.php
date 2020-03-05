<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengeAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('challenger_id', false, true);
            $table->integer('challenge_question_id', false, true);
            $table->unsignedTinyInteger('attempt');
            $table->unsignedTinyInteger('status')->default(0);
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('challenge_attempts');
    }
}
