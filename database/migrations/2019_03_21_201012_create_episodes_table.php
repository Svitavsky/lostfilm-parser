<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEpisodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('series_id');
            $table->string('link')->nullable();
            $table->string('image_url')->nullable();
            $table->float('rank')->nullable();
            $table->unsignedSmallInteger('season_number')->nullable();
            $table->unsignedSmallInteger('episode_number')->nullable();
            $table->timestamps();

            $table->index('series_id');
            $table->foreign('series_id')
                ->references('id')
                ->on('series')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->dropForeign(['series_id']);
            $table->dropIndex(['series_id']);
        });
        Schema::dropIfExists('episodes');
    }
}
