<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_dataset_tables', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Nama tabel kustom');
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade')->comment('Organisasi pemilik tabel');
            $table->foreignId('sector_id')->constrained('sectors')->onDelete('cascade')->comment('Sektor terkait');
            $table->string('table_type')->default('manual')->comment('Tipe tabel: manual, excel, dll');
            $table->boolean('editable')->default(true)->comment('Apakah tabel bisa diedit dari frontend');
            $table->boolean('is_public')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_dataset_tables');
    }
};