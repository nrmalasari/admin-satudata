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
        Schema::table('organizations', function (Blueprint $table) {
            // Tambahkan kolom baru
            $table->integer('dataset_count')->default(0)->after('description');
            $table->timestamp('last_updated')->nullable()->after('dataset_count');
            
            // Atau modifikasi kolom yang sudah ada
            // $table->string('name', 255)->change(); // Contoh mengubah length
            
            // Tambahkan index jika perlu
            $table->index('last_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Hapus kolom yang ditambahkan
            $table->dropColumn(['dataset_count', 'last_updated']);
            
            // Hapus index jika ada
            $table->dropIndex(['last_updated']);
            
            // Kembalikan perubahan kolom jika ada
            // $table->string('name', 191)->change();
        });
    }
};