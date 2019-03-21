<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEpisodeDescriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('episode_descriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('episode_id');
            $table->date('release_date');
            $table->string('title');
            $table->string('language_code')->default('ru');
            $table->timestamps();

            $table->index('episode_id');
            $table->index('language_code');
            $table->foreign('episode_id')
                ->references('id')
                ->on('episodes')
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
        Schema::table('episode_descriptions', function (Blueprint $table) {
            $table->dropForeign(['episode_id']);
            $table->dropIndex(['episode_id']);
            $table->dropIndex(['language_code']);
        });
        Schema::dropIfExists('episode_descriptions');
    }
}
