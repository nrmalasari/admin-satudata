<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk memperbarui tabel custom_dataset_columns
     */
    public function up(): void
    {
        Schema::table('custom_dataset_columns', function (Blueprint $table) {
            // Hapus foreign key constraint yang sudah ada (jika ada)
            $table->dropForeign(['custom_dataset_table_id']);
            
            // Tambahkan kembali foreign key constraint
            $table->foreign('custom_dataset_table_id')
                  ->references('id')
                  ->on('custom_dataset_tables')
                  ->onDelete('cascade');
            
            // Pastikan kolom tidak nullable
            $table->string('name')->nullable(false)->change();
            $table->string('header')->nullable(false)->change();
        });
    }

    /**
     * Rollback perubahan
     */
    public function down(): void
    {
        Schema::table('custom_dataset_columns', function (Blueprint $table) {
            // Hapus foreign key constraint
            $table->dropForeign(['custom_dataset_table_id']);
            
            // Kembalikan ke nullable jika diperlukan (opsional)
            // $table->string('name')->nullable()->change();
            // $table->string('header')->nullable()->change();
        });
    }
};