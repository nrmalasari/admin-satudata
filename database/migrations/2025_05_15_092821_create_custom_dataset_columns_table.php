<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_dataset_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_dataset_table_id')->constrained('custom_dataset_tables')->onDelete('cascade')->comment('Tabel kustom terkait');
            $table->string('name')->comment('Nama kolom di database');
            $table->string('header')->comment('Label kolom yang ditampilkan');
            $table->string('type')->default('string')->comment('Tipe data: string, integer, float, date, dll');
            $table->boolean('visible')->default(true)->comment('Apakah kolom ditampilkan');
            $table->integer('order_index')->default(0)->comment('Urutan kolom');
            $table->string('filter_type')->default('text')->comment('Tipe filter: text, number, select, dll');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_dataset_columns');
    }
};