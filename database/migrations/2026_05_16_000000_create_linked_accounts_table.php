<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinkedAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('linked_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_user_id');
            $table->unsignedBigInteger('linked_user_id');
            $table->string('switch_token', 64)->unique();
            $table->timestamps();

            $table->unique(['owner_user_id', 'linked_user_id']);

            $table->foreign('owner_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('linked_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('linked_accounts');
    }
}
