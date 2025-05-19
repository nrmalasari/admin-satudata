<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_dataset_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_dataset_table_id')->constrained('custom_dataset_tables')->onDelete('cascade')->comment('Tabel kustom terkait');
            $table->json('data')->comment('Data baris dalam format JSON');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_dataset_rows');
    }
};