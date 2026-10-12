<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('web_scrappings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_file');
            $table->string('path_file');
            $table->boolean('status')->default(0); // 0 = belum, 1 = sudah didownload
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_scrappings');
    }
};
