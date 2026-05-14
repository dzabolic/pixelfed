<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHighlightsTable extends Migration
{
    public function up()
    {
        // Tabela principal dos destaques
        Schema::create('highlights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->string('cover_path')->nullable(); // Para a capa customizada
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Tabela de ligação (muitos para muitos) entre Destaques e Stories
        Schema::create('highlight_story', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('highlight_id');
            $table->unsignedBigInteger('story_id');

            $table->foreign('highlight_id')->references('id')->on('highlights')->onDelete('cascade');
            $table->foreign('story_id')->references('id')->on('stories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('highlight_story');
        Schema::dropIfExists('highlights');
    }
}
