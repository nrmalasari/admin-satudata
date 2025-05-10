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
        Schema::table('sectors', function (Blueprint $table) {
            // Pertama, cek dan ubah tipe kolom icon jika ada
            if (Schema::hasColumn('sectors', 'icon')) {
                $table->binary('icon')->nullable()->change();
            } else {
                $table->binary('icon')->nullable();
            }
            
            // Kedua, tambahkan kolom icon_mime hanya jika belum ada
            if (!Schema::hasColumn('sectors', 'icon_mime')) {
                $table->string('icon_mime', 255)->nullable()->after('icon');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sectors', function (Blueprint $table) {
            // Kembalikan tipe kolom icon ke string
            if (Schema::hasColumn('sectors', 'icon')) {
                $table->string('icon')->nullable()->change();
            }
            
            // Hapus kolom icon_mime jika ada
            if (Schema::hasColumn('sectors', 'icon_mime')) {
                $table->dropColumn('icon_mime');
            }
        });
    }
};