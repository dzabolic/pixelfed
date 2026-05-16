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
            // O "dono" do cluster (quem iniciou a sessão neste dispositivo)
            $table->unsignedBigInteger('owner_user_id');
            // A conta vinculada
            $table->unsignedBigInteger('linked_user_id');
            // Token seguro para troca sem senha
            $table->string('switch_token', 64)->unique();
            $table->timestamps();
 
            $table->foreign('owner_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('linked_user_id')->references('id')->on('users')->onDelete('cascade');
 
            // Cada par owner+linked é único
            $table->unique(['owner_user_id', 'linked_user_id']);
        });
    }
 
    public function down()
    {
        Schema::dropIfExists('linked_accounts');
    }
}
 
