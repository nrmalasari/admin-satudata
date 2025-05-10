<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('logo_path', 2048)->nullable();
            $table->text('description')->nullable();
            $table->integer('dataset_count')->default(0); // Add count field
            $table->timestamp('last_updated')->nullable(); // Add last updated timestamp
            
            $table->foreignId('sector_id')
                ->constrained('sectors')
                ->onDelete('cascade');
                
            $table->timestamps();
            
            $table->index('sector_id');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};