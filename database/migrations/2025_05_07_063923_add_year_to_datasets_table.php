<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->year('year')
                  ->nullable()
                  ->after('published_date')
                  ->comment('Tahun dataset');
        });
    }

    public function down()
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->dropColumn('year');
        });
    }
};