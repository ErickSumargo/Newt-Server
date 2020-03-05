<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrivateTeachingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('private_teachings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('private_teacher_id', false, true);
            $table->integer('private_lesson_id', false, true);
            $table->text('lesson');
            $table->unsignedTinyInteger('arrival');
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
        Schema::dropIfExists('private_teachings');
    }
}
