<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('datasets', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->foreignId('custom_dataset_table_id')
                ->nullable()
                ->after('id')
                ->constrained('custom_dataset_tables')
                ->onDelete('set null');
            $table->json('tags')->nullable()->after('is_public');
        });
    }
};
