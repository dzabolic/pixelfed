<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservedUsernamesTable extends Migration
{
    public function up()
    {
        Schema::create('reserved_usernames', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            // Motivo: 'reserved' (reservado pra venda), 'sold' (vendido), 'blocked' (banido)
            $table->enum('reason', ['reserved', 'sold', 'blocked'])->default('reserved');
            $table->string('notes')->nullable(); // anotações suas (ex: "vendido pra fulano")
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reserved_usernames');
    }
}
