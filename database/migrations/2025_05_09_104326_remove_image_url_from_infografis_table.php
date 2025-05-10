<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('infografis', function (Blueprint $table) {
            // Hapus kolom image_url
            if (Schema::hasColumn('infografis', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('infografis', function (Blueprint $table) {
            // Tambahkan kembali kolom image_url
            $table->string('image_url')->nullable()->after('image_path');
        });
    }
};