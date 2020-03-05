<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeachersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable(true);
            $table->string('name');
            $table->string('password');
            $table->string('phone')->unique();
            $table->string('photo')->nullable(true);
            $table->text('social_links');
            $table->string('device')->nullable(true);
            $table->boolean('active')->default(true);
            $table->boolean('pro')->default(false);
            $table->string("firebase")->nullable(true);
            $table->rememberToken();
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
        Schema::dropIfExists('teachers');
    }
}
