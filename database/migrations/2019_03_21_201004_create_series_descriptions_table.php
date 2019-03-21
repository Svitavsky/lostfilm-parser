<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeriesDescriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('series_descriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('series_id');
            $table->string('title');
            $table->string('language_code')->default('ru');
            $table->timestamps();

            $table->index('series_id');
            $table->index('language_code');
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
        Schema::table('series_descriptions', function (Blueprint $table) {
            $table->dropForeign(['series_id']);
            $table->dropIndex(['series_id']);
            $table->dropIndex(['language_code']);
        });
        Schema::dropIfExists('series_descriptions');
    }
}
