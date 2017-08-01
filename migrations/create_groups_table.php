<?php

use Dubroquin\Bouncer\Database\Models;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBouncerTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Models::table('groups'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->integer('role_id')->unsigned();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles');
        });
    }
}