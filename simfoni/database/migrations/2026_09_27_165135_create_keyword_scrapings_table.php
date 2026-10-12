<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keyword tambahan dari menu pengaturan dashboard Otomatisasi.
        Schema::create('keyword_scraping', function (Blueprint $table) {
            $table->id();
            $table->string('keyword', 100)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyword_scraping');
    }
};
