<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengeQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('teacher_id', false, true);
            $table->integer('challenge_lesson_id', false, true);
            $table->integer('challenge_solution_id', false, true);
            $table->text('content');
            $table->string('material');
            $table->unsignedTinyInteger('level');
            $table->unsignedInteger('point');
            $table->unsignedTinyInteger('attempt');
            $table->unsignedTinyInteger('status')->default(2);
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
        Schema::dropIfExists('challenge_questions');
    }
}
