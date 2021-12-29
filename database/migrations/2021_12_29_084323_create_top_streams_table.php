<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopStreamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('top_streams', function (Blueprint $table) {
            $table->id();
            $table->string('twitch_id');
            $table->foreignId('broadcaster_id')->constrained('users');
            $table->foreignId('game_id')->constrained('games');
            $table->string('title');
            $table->integer('viewer_count');
            $table->string('thumbnail')->nullable();
            $table->boolean('is_mature')->default(false);
            $table->dateTime('started_at');
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
        Schema::dropIfExists('top_streams');
    }
}
