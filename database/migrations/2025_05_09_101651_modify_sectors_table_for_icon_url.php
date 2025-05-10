<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sectors', function (Blueprint $table) {
            // Ubah kolom icon dari binary ke string
            $table->string('icon')->nullable()->change();
            // Hapus kolom icon_mime jika ada
            if (Schema::hasColumn('sectors', 'icon_mime')) {
                $table->dropColumn('icon_mime');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sectors', function (Blueprint $table) {
            // Kembalikan kolom icon ke binary
            $table->binary('icon')->nullable()->change();
            // Tambahkan kembali kolom icon_mime
            $table->string('icon_mime')->nullable()->after('icon');
        });
    }
};