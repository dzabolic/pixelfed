<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoverUrlToHighlightsTable extends Migration
{
    public function up()
    {
        Schema::table('highlights', function (Blueprint $table) {
            $table->string('cover_url')->nullable()->after('cover_path');
        });
    }

    public function down()
    {
        Schema::table('highlights', function (Blueprint $table) {
            $table->dropColumn('cover_url');
        });
    }
}
